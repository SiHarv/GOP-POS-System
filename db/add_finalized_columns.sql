-- SQL script to add finalized columns to charges table
-- Run this script to update your database for the new print receipt functionality

USE gop_marketing_db;

-- Add finalized column to track if receipt has been printed/finalized
ALTER TABLE charges 
ADD COLUMN finalized TINYINT(1) NOT NULL DEFAULT 0 AFTER charge_date,
ADD COLUMN finalized_date TIMESTAMP NULL DEFAULT NULL AFTER finalized;

-- Update existing records to be finalized (since they were already processed with the old system)
-- Comment out the next line if you want existing receipts to remain unfinalized
UPDATE charges SET finalized = 1, finalized_date = charge_date WHERE finalized = 0;

-- Create index for better query performance
CREATE INDEX idx_charges_finalized ON charges(finalized);

-- Optional: View to check the new columns
-- SELECT id, customer_id, total_price, po_number, charge_date, finalized, finalized_date FROM charges LIMIT 10;
