-- Run this once against an existing GradConnect SL database.
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS password_reset_token CHAR(64) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS password_reset_expires DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS password_reset_sent_at DATETIME DEFAULT NULL;