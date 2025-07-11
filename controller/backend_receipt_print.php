<?php
require_once __DIR__ . '/../connection/DBConnection.php';

class ReceiptPrintController
{
    private $conn;

    public function __construct()
    {
        $db = new DBConnection();
        $this->conn = $db->getConnection();
    }

    public function getReceiptDetails($id)
    {
        $sql = "SELECT 
            c.id,
            c.charge_date,
            c.total_price,
            c.po_number,
            c.finalized,
            c.finalized_date,
            cust.name AS customer_name,
            cust.address AS customer_address,
            cust.terms AS customer_terms,
            cust.salesman AS customer_salesman,
            i.name AS item_name,
            i.sold_by AS item_unit,
            ci.quantity,
            ci.price AS unit_price,
            ci.discount_percentage,
            (ci.quantity * ci.price * (1 - ci.discount_percentage/100)) AS subtotal
        FROM charges c
        JOIN customers cust ON c.customer_id = cust.id
        JOIN charge_items ci ON c.id = ci.charge_id
        JOIN items i ON ci.item_id = i.id
        WHERE c.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $receipt = null;

        if ($row = $result->fetch_assoc()) {
            $receipt = [
                'id' => $row['id'],
                'date' => $row['charge_date'],
                'customer_name' => $row['customer_name'] ?? '',
                'customer_address' => $row['customer_address'] ?? '',
                'customer_terms' => $row['customer_terms'] ?? '',
                'customer_salesman' => $row['customer_salesman'] ?? '',
                'total_price' => floatval($row['total_price']),
                'po_number' => $row['po_number'] ?? '',
                'finalized' => $row['finalized'] ?? 0,
                'finalized_date' => $row['finalized_date'] ?? null,
                'items' => []
            ];

            do {
                $receipt['items'][] = [
                    'quantity' => intval($row['quantity']),
                    'name' => $row['item_name'] ?? '',
                    'unit' => $row['item_unit'] ?? 'PCS',
                    'unit_price' => floatval($row['unit_price']),
                    'discount_percentage' => floatval($row['discount_percentage']),
                    'subtotal' => floatval($row['subtotal'])
                ];
            } while ($row = $result->fetch_assoc());
        }

        return $receipt;
    }

    public function finalizeReceipt($receiptId)
    {
        try {
            $this->conn->begin_transaction();

            // Check if receipt is already finalized
            $checkSql = "SELECT finalized FROM charges WHERE id = ?";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bind_param("i", $receiptId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $receiptData = $checkResult->fetch_assoc();

            // If already finalized, return success (idempotent operation)
            if ($receiptData && $receiptData['finalized'] == 1) {
                return ['status' => 'success', 'message' => 'Receipt already finalized'];
            }

            // Get all items from the receipt
            $itemsSql = "SELECT ci.item_id, ci.quantity, i.name as item_name 
                        FROM charge_items ci 
                        JOIN items i ON ci.item_id = i.id 
                        WHERE ci.charge_id = ?";
            $itemsStmt = $this->conn->prepare($itemsSql);
            $itemsStmt->bind_param("i", $receiptId);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();

            // Update stock for each item
            $updateStockSql = "UPDATE items SET stock = stock - ? WHERE id = ? AND stock >= ?";
            $updateStmt = $this->conn->prepare($updateStockSql);

            while ($item = $itemsResult->fetch_assoc()) {
                // Check current stock
                $currentStock = $this->getItemStock($item['item_id']);
                if ($currentStock < $item['quantity']) {
                    throw new Exception("Insufficient stock for item: " . $item['item_name']);
                }

                // Update stock
                $updateStmt->bind_param(
                    "iii",
                    $item['quantity'],
                    $item['item_id'],
                    $item['quantity']
                );

                if (!$updateStmt->execute() || $updateStmt->affected_rows === 0) {
                    throw new Exception("Error updating stock for item: " . $item['item_name']);
                }
            }

            // Mark receipt as finalized
            $finalizeSql = "UPDATE charges SET finalized = 1, finalized_date = NOW() WHERE id = ?";
            $finalizeStmt = $this->conn->prepare($finalizeSql);
            $finalizeStmt->bind_param("i", $receiptId);
            
            if (!$finalizeStmt->execute()) {
                throw new Exception("Error finalizing receipt");
            }

            $this->conn->commit();
            return ['status' => 'success', 'message' => 'Receipt finalized and stock updated'];

        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function getItemStock($itemId)
    {
        $sql = "SELECT stock FROM items WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['stock'] : 0;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new ReceiptPrintController();

    if ($_POST['action'] === 'get_receipt_details' && isset($_POST['id'])) {
        $result = $controller->getReceiptDetails($_POST['id']);
        echo json_encode($result);
        exit;
    }
    
    if ($_POST['action'] === 'finalize_receipt' && isset($_POST['id'])) {
        $result = $controller->finalizeReceipt($_POST['id']);
        echo json_encode($result);
        exit;
    }
}
?>