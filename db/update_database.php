<?php
// Script to add finalized columns to charges table
require_once __DIR__ . '/../connection/DBConnection.php';

try {
    $db = new DBConnection();
    $conn = $db->getConnection();
    
    echo "Starting database update...\n";
    
    // Check if columns already exist
    $checkResult = $conn->query("SHOW COLUMNS FROM charges LIKE 'finalized'");
    if ($checkResult->num_rows > 0) {
        echo "Finalized columns already exist. Skipping update.\n";
    } else {
        // Add finalized column
        $sql1 = "ALTER TABLE charges 
                ADD COLUMN finalized TINYINT(1) NOT NULL DEFAULT 0 AFTER charge_date,
                ADD COLUMN finalized_date TIMESTAMP NULL DEFAULT NULL AFTER finalized";
        
        if ($conn->query($sql1) === TRUE) {
            echo "Successfully added finalized columns.\n";
            
            // Update existing records to be finalized (optional)
            $sql2 = "UPDATE charges SET finalized = 1, finalized_date = charge_date WHERE finalized = 0";
            if ($conn->query($sql2) === TRUE) {
                echo "Updated existing records to finalized status.\n";
            }
            
            // Create index
            $sql3 = "CREATE INDEX idx_charges_finalized ON charges(finalized)";
            if ($conn->query($sql3) === TRUE) {
                echo "Created index for finalized column.\n";
            }
            
        } else {
            echo "Error adding columns: " . $conn->error . "\n";
        }
    }
    
    echo "Database update completed.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
