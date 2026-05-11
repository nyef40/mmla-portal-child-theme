-- Migration: sync_local_data_to_server
-- Created: Mon May 26 05:19:20 PDT 2025

-- Add your SQL statements here
-- Example:
-- ALTER TABLE lqbk_referral_submissions ADD COLUMN new_field VARCHAR(255);

-- Insert the record that exists on local but not on server
-- This will sync submission_id = 17 from local to server

MERGE INTO lqbk_referral_submissions t
USING (
    SELECT
        17 AS submission_id,
        'Dr. Tiger' AS provider_name,
        'Tiger Clinic' AS provider_practice,
        'nyef40@yahoo.com' AS provider_email,
        '5555556666' AS provider_phone,
        AES_ENCRYPT('Nick Tiger', 'mmla_2025') AS patient_name,
        AES_ENCRYPT('tiger@gmail.com', 'mmla_2025') AS patient_email,
        '11111199999999999999' AS patient_phone,
        AES_ENCRYPT('tiger', 'mmla_2025') AS insurance,
        'Other' AS reason,
        'blast' AS notes,
        NULL AS user_id,
        TO_DATE('2025-05-23 01:16:33', 'YYYY-MM-DD HH24:MI:SS') AS created_at,
        NULL AS updated_at,
        '3a4411e7-1af0-4e30-8535-d41fdaaa6dae' AS validation_token,
        1 AS is_validated
    FROM dual
) s
ON (t.submission_id = s.submission_id)
WHEN MATCHED THEN
    UPDATE SET
        t.provider_name = s.provider_name,
        t.provider_practice = s.provider_practice,
        t.provider_email = s.provider_email,
        t.provider_phone = s.provider_phone,
        t.patient_name = s.patient_name,
        t.patient_email = s.patient_email,
        t.patient_phone = s.patient_phone,
        t.insurance = s.insurance,
        t.reason = s.reason,
        t.notes = s.notes,
        t.user_id = s.user_id,
        t.created_at = s.created_at,
        t.updated_at = s.updated_at,
        t.validation_token = s.validation_token,
        t.is_validated = s.is_validated
WHEN NOT MATCHED THEN
    INSERT (
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
        s.submission_id,
        s.provider_name,
        s.provider_practice,
        s.provider_email,
        s.provider_phone,
        s.patient_name,
        s.patient_email,
        s.patient_phone,
        s.insurance,
        s.reason,
        s.notes,
        s.user_id,
        s.created_at,
        s.updated_at,
        s.validation_token,
        s.is_validated
    );