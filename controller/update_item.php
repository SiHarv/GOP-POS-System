<?php
require_once 'backend_items.php';
header('Content-Type: application/json');

try {
    $controller = new ItemsController();
    $result = $controller->updateItem($_POST);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item updated successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}