<?php
// Function to convert image to base64 for reliable PDF printing
function getImageAsBase64($imagePath)
{
    if (file_exists($imagePath)) {
        $imageData = file_get_contents($imagePath);
        $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
        $base64 = base64_encode($imageData);
        return "data:image/{$imageType};base64,{$base64}";
    }
    return '';
}

// Get the logo as base64 for reliable printing
$logoPath = __DIR__ . '/../../icon/invoice-icon.png';
$logoBase64 = getImageAsBase64($logoPath);
?>

<!-- Receipt Details Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg " style="max-height: 90vh; overflow-y: auto;">
        <div class="modal-content">
            <div class="modal-header">
                <p class="modal-title" id="receiptModalLabel">Receipt Details</p>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="printable-area" style="padding-bottom: 90px;">
                <div class="receipt-header text-center mt-5 ">
                    <div class="text-start mt-5"
                        style="position:absolute;
                        top: -15px;
                        margin-left: 10px;
                        ">
                        <img src="<?php echo $logoBase64; ?>" alt="gop-icon" style="
                        height: 100px; 
                        width: 90px;
                        ">
                    </div>
                    <div style="font-size: 15px; font-weight: bold;">GOP GARKETING</div>
                    <div style="font-size: 14px;">Wangag, Damulaan</div>
                    <div style="font-size: 14px;">Albuera, Leyte</div>
                    <div class="mt-3" style="font-size: 14px;"><b>Delivery Receipt</b></div>
                    <!-- <div>Tel: 0987654321</div> -->
                    <div class="text-end me-5"
                        style="position: absolute; right: 20px; top: 100px; font-size: 14px;">
                        <strong>Receipt #:</strong> <span id="receipt-id"></span>
                    </div>
                    <hr>
                </div>
                <div class="receipt-details mb-4">
                    <div class="d-flex">
                        <div style="font-size: 14px; width: 50%;"><strong>Customer:</strong> <span id="receipt-customer"></span></div>
                        <div style="font-size: 14px; width: 30%;"><strong>Date:</strong> <span id="receipt-date"></span></div>
                    </div>
                    <div class="d-flex">
                        <div style="font-size: 14px; width: 50%;"><strong>Address:</strong> <span id="receipt-address"></span></div>
                        <div style="font-size: 14px; width: 30%;"><strong>Terms:</strong> <span id="receipt-terms"></span></div>
                    </div>
                    <div class="d-flex">
                        <div style="font-size: 14px; width: 50%;"><strong>P.O. Number:</strong> <span id="receipt-po-number">-</span></div>
                        <div style="font-size: 14px; width: 30%;"><strong>Salesman:</strong> <span id="receipt-salesman"></span></div>
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
                    <tbody id="receipt-items">
                        <!-- Items will be inserted here dynamically -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end" style="font-size:12px; padding: 3px;"><strong>Total Amount:</strong></td>
                            <td class="text-end" style="font-size:12px; padding: 3px;"><strong>₱<span id="receipt-total"></span></strong></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="footer text-center mt-4">
                    <small style="font-style: italic;">This is a computer-generated receipt</small>
                </div>
            </div>
            <div class="modal-footer" style="position: sticky; bottom: 0; right: 0; background: #fff; border: none; z-index: 10; display: flex; gap: 10px; box-shadow: 0 -2px 8px rgba(0,0,0,0.05);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="print-receipt">Print Receipt</button>
            </div>
        </div>
    </div>
</div>



<!-- Include the receipt print functionality script -->
<script src="../../js/receipt_print.js"></script>