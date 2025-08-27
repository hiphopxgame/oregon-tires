-- Drop the old oregon_tires_appointments table after successful data migration
-- All data has been consolidated into oretir_appointments

-- First, verify that all data has been migrated by checking record counts
DO $$
DECLARE
    oretir_count INTEGER;
    oregon_count INTEGER;
BEGIN
    SELECT COUNT(*) INTO oretir_count FROM oretir_appointments;
    SELECT COUNT(*) INTO oregon_count FROM oregon_tires_appointments;
    
    -- Log the counts for verification
    RAISE NOTICE 'Records in oretir_appointments: %', oretir_count;
    RAISE NOTICE 'Records in oregon_tires_appointments: %', oregon_count;
    
    -- Ensure we have more or equal records in the consolidated table
    IF oretir_count < oregon_count THEN
        RAISE EXCEPTION 'Data migration incomplete. oretir_appointments has fewer records than oregon_tires_appointments';
    END IF;
END $$;

-- Drop the old table and its dependencies
DROP TABLE IF EXISTS oregon_tires_appointments CASCADE;