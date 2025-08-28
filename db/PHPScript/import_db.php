<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class DatabaseImporter {
    private $host = 'localhost';
    private $username = 'root';
    private $password = ''; // Updated password
    private $database = 'gop_marketing_db';
    private $conn;

    public function __construct() {
        // Create connection
        $this->conn = new mysqli($this->host, $this->username, $this->password);
        
        if ($this->conn->connect_error) {
            die("❌ Connection failed: " . $this->conn->connect_error);
        }
        
        $this->printSuccess("✅ Connected to MySQL successfully");
    }

    private function printHeader($message) {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "  " . strtoupper($message) . "\n";
        echo str_repeat("=", 60) . "\n";
    }

    private function printSubHeader($message) {
        echo "\n" . str_repeat("-", 40) . "\n";
        echo "  " . $message . "\n";
        echo str_repeat("-", 40) . "\n";
    }

    private function printSuccess($message) {
        echo "✅ " . $message . "\n";
    }

    private function printError($message) {
        echo "❌ " . $message . "\n";
    }

    private function printInfo($message) {
        echo "ℹ️  " . $message . "\n";
    }

    private function printWarning($message) {
        echo "⚠️  " . $message . "\n";
    }

    public function dropAllTables() {
        try {
            $this->printSubHeader("DROPPING EXISTING TABLES");
            
            // Select the database first
            $this->conn->select_db($this->database);
            
            // Disable foreign key checks
            $this->conn->query("SET FOREIGN_KEY_CHECKS = 0");
            $this->printInfo("Foreign key checks disabled");
            
            // Get all table names
            $result = $this->conn->query("SHOW TABLES");
            $tables = [];
            
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            
            if (empty($tables)) {
                $this->printWarning("No tables found to drop");
                return;
            }

            $this->printInfo("Found " . count($tables) . " table(s) to drop");
            
            // Drop each table
            foreach ($tables as $table) {
                if ($this->conn->query("DROP TABLE IF EXISTS `$table`")) {
                    $this->printSuccess("Table '$table' dropped");
                } else {
                    $this->printError("Failed to drop table '$table': " . $this->conn->error);
                }
            }
            
            // Re-enable foreign key checks
            $this->conn->query("SET FOREIGN_KEY_CHECKS = 1");
            $this->printInfo("Foreign key checks re-enabled");
            
            $this->printSuccess("All tables dropped successfully");
            
        } catch (Exception $e) {
            $this->printError("Error dropping tables: " . $e->getMessage());
        }
    }

    public function importSQL() {
        try {
            $this->printHeader("DATABASE IMPORT PROCESS");
            
            // Create database if not exists
            $sql = "CREATE DATABASE IF NOT EXISTS " . $this->database;
            if ($this->conn->query($sql)) {
                $this->printSuccess("Database '{$this->database}' ready");
            }

            // Select the database
            $this->conn->select_db($this->database);

            // Drop all existing tables first
            $this->dropAllTables();

            $this->printSubHeader("IMPORTING SQL FILE");

            // Read SQL file
            $sqlFile = __DIR__ . '/../gop_marketing_db.sql';
            $this->printInfo("Reading SQL file: " . basename($sqlFile));
            
            $sql = file_get_contents($sqlFile);

            if ($sql === false) {
                throw new Exception("Error reading SQL file");
            }

            $this->printSuccess("SQL file loaded successfully");

            // Remove comments and clean up SQL
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            
            // Split SQL by semicolon and filter empty queries
            $queries = array_filter(array_map('trim', explode(';', $sql)), function($query) {
                return !empty($query) && !preg_match('/^\s*$/', $query);
            });

            $totalQueries = count($queries);
            $this->printInfo("Found {$totalQueries} SQL queries to execute");

            $this->printSubHeader("EXECUTING QUERIES");

            $successCount = 0;
            $errorCount = 0;

            // Execute each query
            foreach ($queries as $index => $query) {
                if (!empty(trim($query))) {
                    $queryPreview = substr(trim(preg_replace('/\s+/', ' ', $query)), 0, 50);
                    
                    if ($this->conn->query($query)) {
                        $successCount++;
                        echo "✅ Query " . ($index + 1) . "/{$totalQueries}: {$queryPreview}...\n";
                    } else {
                        $errorCount++;
                        echo "❌ Query " . ($index + 1) . "/{$totalQueries}: {$queryPreview}...\n";
                        echo "   Error: " . $this->conn->error . "\n";
                    }
                }
            }

            $this->printSubHeader("IMPORT SUMMARY");
            $this->printSuccess("Total queries executed: {$totalQueries}");
            $this->printSuccess("Successful: {$successCount}");
            
            if ($errorCount > 0) {
                $this->printError("Failed: {$errorCount}");
            } else {
                $this->printSuccess("Failed: {$errorCount}");
            }

            if ($errorCount == 0) {
                $this->printHeader("🎉 DATABASE IMPORT COMPLETED SUCCESSFULLY! 🎉");
            } else {
                $this->printHeader("⚠️  DATABASE IMPORT COMPLETED WITH ERRORS ⚠️");
            }

        } catch (Exception $e) {
            $this->printError("Import failed: " . $e->getMessage());
        }
    }

    public function __destruct() {
        $this->conn->close();
    }
}

// Run the importer
echo "\n" . str_repeat("*", 60) . "\n";
echo "     🚀 GOP MARKETING DATABASE IMPORTER 🚀\n";
echo str_repeat("*", 60) . "\n";

$importer = new DatabaseImporter();
$importer->importSQL();

echo "\n" . str_repeat("*", 60) . "\n";
echo "     ✨ IMPORT PROCESS FINISHED ✨\n";
echo str_repeat("*", 60) . "\n\n";