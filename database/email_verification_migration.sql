-- Run this once against an existing GradConnect SL database.
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS email_verification_token CHAR(64) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS email_verification_expires DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS email_verification_sent_at DATETIME DEFAULT NULL;

-- Existing accounts predate email verification and are treated as verified.
UPDATE users
SET email_verified = 1
WHERE email_verified = 0
  AND email_verification_token IS NULL;