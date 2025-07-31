<?php
require_once __DIR__ . '/../../auth/check_auth.php';
require_once __DIR__ . '/../../controller/backend_items.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');
    if ($handle !== FALSE) {
        $header = fgetcsv($handle); // Read header row
        $itemsController = new ItemsController();
        $imported = 0;
        while (($data = fgetcsv($handle)) !== FALSE) {
            // Map CSV columns to your DB fields
            // Example: [name, category, sold_by, stock, cost, price]
            $item = array_combine($header, $data);
            // You may want to validate/sanitize $item here
            $itemsController->importItemFromCSV($item);
            $imported++;
        }
        fclose($handle);
        header("Location: items.php?imported=$imported");
        exit;
    }
}
header("Location: items.php?import_error=1");
exit;
?>