ALTER TABLE applications
    ADD COLUMN IF NOT EXISTS interview_date DATE DEFAULT NULL AFTER status,
    ADD COLUMN IF NOT EXISTS interview_time TIME DEFAULT NULL AFTER interview_date;