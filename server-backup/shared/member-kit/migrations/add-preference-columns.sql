ALTER TABLE members
    ADD COLUMN marketing_emails TINYINT(1) NOT NULL DEFAULT 0 AFTER email_verified_at,
    ADD COLUMN digest_frequency ENUM('never','daily','weekly') NOT NULL DEFAULT 'never' AFTER marketing_emails;
