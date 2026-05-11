-- Migration: sync_local_data_to_server_v0_version
-- Created: Mon May 26 05:51:34 PDT 2025

-- Add your SQL statements here
-- Example:
-- ALTER TABLE lqbk_referral_submissions ADD COLUMN new_field VARCHAR(255);

INSERT INTO lqbk_referral_submissions (
    submission_id,
    provider_name,
    provider_practice,
    provider_email,
    provider_phone,
    patient_name,
    patient_email,
    patient_phone,
    insurance,
    reason,
    notes,
    user_id,
    created_at,
    updated_at,
    validation_token,
    is_validated
) VALUES (
    17,
    'Dr. Tiger',
    'Tiger Clinic',
    'nyef40@yahoo.com',
    '5555556666',
    AES_ENCRYPT('Nick Tiger', 'mmla_2025'),
    AES_ENCRYPT('tiger@gmail.com', 'mmla_2025'),
    '11111199999999999999',
    AES_ENCRYPT('tiger', 'mmla_2025'),
    'Other',
    'blast',
    NULL,
    '2025-05-23 01:16:33',
    NULL,
    '3a4411e7-1af0-4e30-8535-d41fdaaa6dae',
    1
) ON DUPLICATE KEY UPDATE
    provider_name = VALUES(provider_name),
    provider_practice = VALUES(provider_practice),
    provider_email = VALUES(provider_email),
    provider_phone = VALUES(provider_phone),
    patient_name = VALUES(patient_name),
    patient_email = VALUES(patient_email),
    patient_phone = VALUES(patient_phone),
    insurance = VALUES(insurance),
    reason = VALUES(reason),
    notes = VALUES(notes),
    user_id = VALUES(user_id),
    created_at = VALUES(created_at),
    updated_at = VALUES(updated_at),
    validation_token = VALUES(validation_token),
    is_validated = VALUES(is_validated);