<?php
// Simple test to verify base64 image conversion is working
require_once __DIR__ . '/../views/receipts/receiptViewModal.php';

// Test the base64 conversion
$logoPath = __DIR__ . '/../icon/invoice-icon.png';
if (file_exists($logoPath)) {
    echo "✅ Logo file found at: $logoPath\n";
    $base64 = getImageAsBase64($logoPath);
    if (!empty($base64) && strpos($base64, 'data:image/') === 0) {
        echo "✅ Base64 conversion successful\n";
        echo "📊 Base64 length: " . strlen($base64) . " characters\n";
        echo "🔗 Preview (first 100 chars): " . substr($base64, 0, 100) . "...\n";
    } else {
        echo "❌ Base64 conversion failed\n";
    }
} else {
    echo "❌ Logo file not found at: $logoPath\n";
}
?>
