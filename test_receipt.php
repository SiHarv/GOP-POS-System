<?php
// Test file to verify receipt generation with base64 logo
require_once 'connection/DBConnection.php';

// Mock data for testing
$customer_name = "Test Customer";
$address = "123 Test Street, Test City";
$receipt_id = "DR-" . date('Ymd') . "-001";
$date = date('M d, Y');
$terms = "Cash";
$po_number = "-";
$salesman = "Test Salesman";

// Mock items
$items = [
    [
        'quantity' => 2,
        'unit' => 'kg',
        'item_name' => 'Test Item 1',
        'base_price' => 100.00,
        'discount' => 5,
        'net_price' => 95.00,
        'amount' => 190.00
    ],
    [
        'quantity' => 1,
        'unit' => 'pcs',
        'item_name' => 'Test Item 2',
        'base_price' => 250.00,
        'discount' => 0,
        'net_price' => 250.00,
        'amount' => 250.00
    ]
];

$total_amount = array_sum(array_column($items, 'amount'));

// Get base64 logo
function getBase64Logo() {
    $logoPath = __DIR__ . '/icon/invoice-icon.png';
    if (file_exists($logoPath)) {
        $imageData = file_get_contents($logoPath);
        $mimeType = 'image/png';
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }
    return '';
}

$base64Logo = getBase64Logo();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-area,
            #printable-area * {
                visibility: visible;
            }
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white;
                padding: 20px;
                font-size: 12px;
                line-height: 1.4;
                border: 2px solid #000000;
            }
            .receipt-header img {
                max-width: 48px !important;
                max-height: 60px !important;
                object-fit: contain;
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <button onclick="window.print()" class="btn btn-primary mb-3">Print Receipt</button>
        
        <div id="printable-area" style="border: 1px solid #ddd; padding: 20px; max-width: 600px;">
            <div class="receipt-header text-center position-relative mb-4">
                <div class="text-start" style="position: absolute; left: 0; top: 0;">
                    <?php if ($base64Logo): ?>
                        <img src="<?php echo $base64Logo; ?>" alt="gop-icon" style="height: 100px; width: 80px;">
                    <?php else: ?>
                        <div style="width: 80px; height: 100px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px;">No Logo</div>
                    <?php endif; ?>
                </div>
                <div style="font-size: 12px;">GOP MARKETING</div>
                <div style="font-size: 12px;">Wangag, Damulaan</div>
                <div style="font-size: 12px;">Albuera, Leyte</div>
                <div class="mt-3" style="font-size: 12px;"><b>Delivery Receipt</b></div>
                <div class="text-end me-5" style="position: absolute; right: 20px; top: 100px; font-size: 12px;">
                    <strong>Receipt #:</strong> <?php echo $receipt_id; ?>
                </div>
                <hr>
            </div>
            
            <div class="receipt-details mb-4">
                <div class="d-flex justify-content-between">
                    <div style="font-size: 12px;"><strong>Customer:</strong> <?php echo $customer_name; ?></div>     
                    <div style="font-size: 12px;"><strong>Date:</strong> <?php echo $date; ?></div>
                </div>
                <div class="d-flex justify-content-between">
                    <div style="font-size: 12px;"><strong>Address:</strong> <?php echo $address; ?></div>       
                    <div style="font-size: 12px;"><strong>Terms:</strong> <?php echo $terms; ?></div>
                </div>
                <div class="d-flex justify-content-between">
                    <div style="font-size: 12px;"><strong>P.O. Number:</strong> <?php echo $po_number; ?></div>
                    <div style="font-size: 12px;"><strong>Salesman:</strong> <?php echo $salesman; ?></div>     
                </div>
            </div>
            
            <table class="table table-bordered" style="padding: none; margin:none;">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 12px; padding: 3px;">QTY</th>
                        <th class="text-center" style="font-size: 12px; padding: 3px;">UNIT</th>
                        <th class="text-center" style="text-align: center; width: 30%; font-size:12px; padding: 3px;">ITEM/DESCRIPTION</th>
                        <th class="text-center" style="font-size: 12px; padding: 3px;">BASE PRICE</th>
                        <th class="text-center" style="font-size: 12px; padding: 3px;">DISC.</th>
                        <th class="text-center" style="font-size: 12px; padding: 3px;">NET PRICE</th>
                        <th class="text-center" style="text-align: center; font-size:12px; padding: 3px;">AMOUNT</th>      
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="text-center" style="font-size:12px; padding: 3px;"><?php echo $item['quantity']; ?></td>
                        <td class="text-center" style="font-size:12px; padding: 3px;"><?php echo $item['unit']; ?></td>
                        <td style="font-size:12px; padding: 3px;"><?php echo $item['item_name']; ?></td>
                        <td class="text-end" style="font-size:12px; padding: 3px;">₱<?php echo number_format($item['base_price'], 2); ?></td>
                        <td class="text-center" style="font-size:12px; padding: 3px;"><?php echo $item['discount']; ?>%</td>
                        <td class="text-end" style="font-size:12px; padding: 3px;">₱<?php echo number_format($item['net_price'], 2); ?></td>
                        <td class="text-end" style="font-size:12px; padding: 3px;">₱<?php echo number_format($item['amount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-end" style="font-size:12px; padding: 3px;"><strong>Total Amount:</strong></td>
                        <td class="text-end" style="font-size:12px; padding: 3px;"><strong>₱<?php echo number_format($total_amount, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="footer text-center mt-4">
                <small>This is a computer-generated receipt</small>
            </div>
        </div>
    </div>
</body>
</html>
