
-- First, let's see what the current check constraints allow
SELECT conname, pg_get_constraintdef(oid) 
FROM pg_constraint 
WHERE conrelid = 'oregon_tires_appointments'::regclass 
AND contype = 'c';

SELECT conname, pg_get_constraintdef(oid) 
FROM pg_constraint 
WHERE conrelid = 'oregon_tires_contact_messages'::regclass 
AND contype = 'c';

-- Update the check constraints to allow the new status values
ALTER TABLE oregon_tires_appointments 
DROP CONSTRAINT IF EXISTS oregon_tires_appointments_status_check;

ALTER TABLE oregon_tires_appointments 
ADD CONSTRAINT oregon_tires_appointments_status_check 
CHECK (status IN ('new', 'priority', 'completed'));

ALTER TABLE oregon_tires_contact_messages 
DROP CONSTRAINT IF EXISTS oregon_tires_contact_messages_status_check;

ALTER TABLE oregon_tires_contact_messages 
ADD CONSTRAINT oregon_tires_contact_messages_status_check 
CHECK (status IN ('new', 'priority', 'completed'));

-- Update existing records to use lowercase status values if needed
UPDATE oregon_tires_appointments 
SET status = LOWER(status) 
WHERE status NOT IN ('new', 'priority', 'completed');

UPDATE oregon_tires_contact_messages 
SET status = LOWER(status) 
WHERE status NOT IN ('new', 'priority', 'completed');
