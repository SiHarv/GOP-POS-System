<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'clear') {
        // Clear all charge session data
        unset($_SESSION['charge_customer_name']);
        unset($_SESSION['charge_customer_id']);
        unset($_SESSION['charge_po_number']);
        unset($_SESSION['charge_salesman']);
        
        echo json_encode(['status' => 'success', 'message' => 'Session data cleared']);
    } else {
        // Save charge data to session
        if (isset($_POST['customer_name'])) {
            $_SESSION['charge_customer_name'] = $_POST['customer_name'];
        }
        
        if (isset($_POST['customer_id'])) {
            $_SESSION['charge_customer_id'] = $_POST['customer_id'];
        }
        
        if (isset($_POST['po_number'])) {
            $_SESSION['charge_po_number'] = $_POST['po_number'];
        }
        
        if (isset($_POST['salesman'])) {
            $_SESSION['charge_salesman'] = $_POST['salesman'];
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Session data saved']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
