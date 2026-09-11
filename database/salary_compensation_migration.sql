-- Add structured salary fields without removing the legacy salary column.
ALTER TABLE jobs
    ADD COLUMN salary_type ENUM('negotiable', 'competitive', 'not_disclosed', 'fixed') NOT NULL DEFAULT 'negotiable' AFTER salary,
    ADD COLUMN salary_amount DECIMAL(12, 2) DEFAULT NULL AFTER salary_type,
    ADD COLUMN salary_period ENUM('monthly', 'annual') DEFAULT NULL AFTER salary_amount;

-- Preserve existing records by mapping their current salary text to the closest option.
UPDATE jobs
SET salary_type = CASE
        WHEN LOWER(TRIM(COALESCE(salary, ''))) LIKE '%negotiable%' THEN 'negotiable'
        WHEN LOWER(TRIM(COALESCE(salary, ''))) LIKE '%competitive%' THEN 'competitive'
        WHEN LOWER(TRIM(COALESCE(salary, ''))) LIKE '%not disclosed%' THEN 'not_disclosed'
        WHEN TRIM(COALESCE(salary, '')) REGEXP '[0-9]' THEN 'fixed'
        ELSE 'not_disclosed'
    END,
    salary_amount = CASE
        WHEN TRIM(COALESCE(salary, '')) REGEXP '[0-9]' THEN CAST(REPLACE(REGEXP_SUBSTR(salary, '[0-9][0-9,\\.]*'), ',', '') AS DECIMAL(12, 2))
        ELSE NULL
    END,
    salary_period = CASE
        WHEN LOWER(TRIM(COALESCE(salary, ''))) LIKE '%year%' OR LOWER(TRIM(COALESCE(salary, ''))) LIKE '%annual%' THEN 'annual'
        WHEN TRIM(COALESCE(salary, '')) REGEXP '[0-9]' THEN 'monthly'
        ELSE NULL
    END
WHERE salary_type = 'negotiable' AND (salary IS NOT NULL AND TRIM(salary) <> '');