<?php
/**
 * One-time normalization: re-encrypt every row in {prefix}referral_submissions so
 * that patient_name and patient_email are stored with the CURRENT encryption key.
 *
 * History of the column:
 *   - Some legacy rows were inserted via MySQL's AES_ENCRYPT(value, 'mmla_2025')
 *     (see db-migrations/20250526_051920_sync_local_data_to_server.sql).
 *   - Other rows were inserted as plaintext bytes by submit_referral_callback()
 *     prior to the May 2026 encryption fix.
 *
 * After this script runs, every row uses the same key returned by get_encryption_key(),
 * and try_decrypt_field() can be tightened to that single key.
 *
 * Run with WP-CLI inside the WordPress container:
 *
 *   docker exec -it mmla-portal-wordpress-1 \
 *     wp eval-file wp-content/themes/blocksy-child/migrations/20260512_normalize_referral_encryption.php \
 *     --allow-root
 *
 * Dry run (no UPDATE) — set this constant before running:
 *
 *   docker exec -it mmla-portal-wordpress-1 \
 *     wp eval-file ... --allow-root --skip-plugins --skip-themes
 *
 * The script is idempotent: re-running it after success will detect that rows
 * already decrypt with the current key and skip them.
 */

if (!defined('ABSPATH')) {
    fwrite(STDERR, "This script must be run via WP-CLI (wp eval-file). Aborting.\n");
    exit(1);
}

if (!function_exists('mysql_aes_key') || !function_exists('mysql_aes_encrypt') || !function_exists('get_encryption_key')) {
    fwrite(STDERR, "Required helpers missing. Make sure functions.php is loaded.\n");
    exit(1);
}

$DRY_RUN = defined('PORTAL_MIGRATION_DRY_RUN') && PORTAL_MIGRATION_DRY_RUN;

global $wpdb;
$table = $wpdb->prefix . 'referral_submissions';
$current_key = get_encryption_key();
$current_derived = mysql_aes_key($current_key);

$candidate_keys = array_values(array_filter(array_unique([
    'mmla_2025',                 // legacy migration key
    $current_key,                // current key
    'portal-referral-key-16',    // hard-coded fallback
])));

echo "=== Referral PHI re-encryption ===\n";
echo "Table:           {$table}\n";
echo "Current key:     " . str_repeat('*', max(0, strlen($current_key) - 4)) . substr($current_key, -4) . " (length " . strlen($current_key) . ")\n";
echo "Candidate keys:  " . count($candidate_keys) . "\n";
echo "Mode:            " . ($DRY_RUN ? "DRY RUN (no UPDATE)" : "LIVE") . "\n\n";

$rows = $wpdb->get_results("SELECT submission_id, patient_name, patient_email FROM {$table}");
if (!$rows) {
    echo "No rows found in {$table}. Nothing to do.\n";
    return;
}

echo "Found " . count($rows) . " row(s).\n\n";

$stats = ['updated' => 0, 'already_current' => 0, 'failed' => 0, 'empty' => 0];

/**
 * Recover plaintext from a stored value that may be:
 *   - already plaintext (printable UTF-8 bytes),
 *   - AES-encrypted with one of the candidate keys.
 * Returns [plaintext, source] where source is 'plaintext' or the key used.
 * Returns null if no candidate decrypts cleanly and the value is not plaintext.
 */
function recover_plaintext($value, $candidate_keys) {
    if ($value === null || $value === '') return ['', 'empty'];

    // Already plaintext?
    if (mb_check_encoding($value, 'UTF-8') && !preg_match('/[\x00-\x08\x0E-\x1F\x7F]/', $value)) {
        return [$value, 'plaintext'];
    }

    // Try each candidate key.
    foreach ($candidate_keys as $key) {
        $attempt = @openssl_decrypt(
            $value,
            'aes-128-ecb',
            mysql_aes_key($key),
            OPENSSL_RAW_DATA
        );
        if ($attempt !== false
            && $attempt !== ''
            && mb_check_encoding($attempt, 'UTF-8')
            && !preg_match('/[\x00-\x08\x0E-\x1F\x7F]/', $attempt)
        ) {
            return [$attempt, "decrypted with key '" . $key . "'"];
        }
    }
    return null;
}

foreach ($rows as $row) {
    $id = (int) $row->submission_id;
    $updates = [];
    $log = [];

    foreach (['patient_name', 'patient_email'] as $col) {
        $orig = $row->$col;
        if ($orig === null || $orig === '') {
            $log[] = "$col=<empty>";
            $stats['empty']++;
            continue;
        }

        $recovered = recover_plaintext($orig, $candidate_keys);
        if ($recovered === null) {
            $log[] = "$col=FAILED (could not decrypt; not plaintext either)";
            $stats['failed']++;
            continue;
        }
        [$plain, $source] = $recovered;
        if ($plain === '') {
            $log[] = "$col=<empty>";
            continue;
        }

        // Re-encrypt with the CURRENT key.
        $new_ciphertext = openssl_encrypt(
            $plain,
            'aes-128-ecb',
            $current_derived,
            OPENSSL_RAW_DATA
        );

        // If the row is already encrypted with the current key, $new_ciphertext === $orig.
        if ($new_ciphertext === $orig) {
            $log[] = "$col=already_current ({$source})";
            $stats['already_current']++;
            continue;
        }

        $updates[$col] = $new_ciphertext;
        $log[] = "$col=" . $source . " → re-encrypted (was '" . substr($plain, 0, 24) . "')";
    }

    echo "Row #{$id}: " . implode(' | ', $log) . "\n";

    if (!empty($updates) && !$DRY_RUN) {
        $set_clauses = [];
        $values = [];
        foreach ($updates as $col => $val) {
            $set_clauses[] = "{$col} = %s";
            $values[] = $val;
        }
        $values[] = $id;
        $sql = "UPDATE {$table} SET " . implode(', ', $set_clauses) . " WHERE submission_id = %d";
        $result = $wpdb->query($wpdb->prepare($sql, $values));
        if ($result === false) {
            echo "  ERROR updating row #{$id}: " . $wpdb->last_error . "\n";
            $stats['failed']++;
        } else {
            $stats['updated']++;
        }
    } elseif (!empty($updates) && $DRY_RUN) {
        $stats['updated']++; // would-be update
    }
}

echo "\n=== Summary ===\n";
echo "Re-encrypted:    {$stats['updated']}" . ($DRY_RUN ? " (dry-run, no DB changes)" : "") . "\n";
echo "Already current: {$stats['already_current']}\n";
echo "Empty fields:    {$stats['empty']}\n";
echo "Failed:          {$stats['failed']}\n";

if ($stats['failed'] > 0) {
    echo "\nWARNING: " . $stats['failed'] . " field(s) could not be normalized. ";
    echo "Add the missing key to the candidate list and re-run, or inspect those rows manually.\n";
    exit(2);
}

echo "\nDone. After verifying /portal-referrals/ still shows correct names, you can\n";
echo "tighten try_decrypt_field() in functions.php to use only get_encryption_key().\n";
