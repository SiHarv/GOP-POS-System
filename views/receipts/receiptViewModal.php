<?php
// Function to convert image to base64 for reliable PDF printing
function getImageAsBase64($imagePath) {
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
                        width: 80px;
                        ">
                    </div>
                    <div style="font-size: 12px;">GOP GARKETING</div>
                    <div style="font-size: 12px;">Wangag, Damulaan</div>
                    <div style="font-size: 12px;">Albuera, Leyte</div>
                    <div class="mt-3" style="font-size: 12px;"><b>Delivery Receipt</b></div>
                    <!-- <div>Tel: 0987654321</div> -->
                    <div class="text-end me-5"
                        style="position: absolute; right: 20px; top: 100px; font-size: 12px;">
                        <strong>Receipt #:</strong> <span id="receipt-id"></span>
                    </div>
                    <hr>
                </div>
                <div class="receipt-details mb-4">
                    <div class="d-flex justify-content-between">
                        <div style="font-size: 12px;"><strong>Customer:</strong> <span id="receipt-customer"></span></div>
                        <div style="font-size: 12px;"><strong>Date:</strong> <span id="receipt-date"></span></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div style="font-size: 12px;"><strong>Address:</strong> <span id="receipt-address"></span></div>
                        <div style="font-size: 12px;"><strong>Terms:</strong> <span id="receipt-terms"></span></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div style="font-size: 12px;"><strong>P.O. Number:</strong> <span id="receipt-po-number">-</span></div>
                        <div style="font-size: 12px;"><strong>Salesman:</strong> <span id="receipt-salesman"></span></div>
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
                    <small>This is a computer-generated receipt</small>
                </div>
            </div>
            <div class="modal-footer" style="position: sticky; bottom: 0; right: 0; background: #fff; border: none; z-index: 10; display: flex; gap: 10px; box-shadow: 0 -2px 8px rgba(0,0,0,0.05);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="print-receipt">Print Receipt</button>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-footer .btn {
        min-width: 120px;
    }

    /* Ensure logo displays properly in both screen and print */
    .receipt-header .text-start img {
        max-width: 100%;
        height: auto;
        object-fit: contain;
        display: block;
    }

    @media print {
        /* Hide everything except the printable area */
        body * {
            visibility: hidden;
        }

        #printable-area,
        #printable-area * {
            visibility: visible;
        }

        /* Set up print page */
        @page {
            margin: 0.5in;
            size: A4;
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

        /* Hide modal elements */
        .modal-footer,
        .modal-header {
            display: none !important;
        }

        /* Receipt header styling */
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }

        .receipt-header h5 {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }

        .receipt-header div {
            font-size: 11px;
            margin: 2px 0;
        }

        /* Logo positioning for print */
        .receipt-header .text-start {
            position: absolute;
            left: 0;
            top: 0;
            margin-left: 0;
        }

        .receipt-header .text-start img {
            height: 32px !important;
            width: 32px !important;
            /* Ensure base64 images print properly */
            max-width: 32px !important;
            max-height: 32px !important;
            object-fit: contain;
            display: block !important;
        }

        /* Receipt number positioning */
        .receipt-header .text-end {
            position: absolute;
            right: 0;
            top: 0;
            margin-right: 0;
            font-size: 11px;
        }

        /* Receipt details styling */
        .receipt-details {
            margin-bottom: 15px;
            font-size: 11px;
        }

        .receipt-details .d-flex {
            display: flex !important;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .receipt-details strong {
            font-weight: bold;
        }

        /* Table styling for print */
        .table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 8px;
            border: 2px solid #000000;
        }

        .table th,
        .table td {
            border: 2px solid #000000;
            /* padding: 8px 4px; */
            vertical-align: top;
        }

        .table th {
            background-color: #f8f9fa;
            /* font-weight: bold; */
            text-align: center;
            font-size: 8px;
        }

        .table td {
            font-size: 8px;
        }

        /* Adjust column widths */
        .table th:nth-child(1),
        .table td:nth-child(1) { width: 8%; text-align: center; }
        .table th:nth-child(2),
        .table td:nth-child(2) { width: 8%; text-align: center; }
        .table th:nth-child(3),
        .table td:nth-child(3) { width: 30%; }
        .table th:nth-child(4),
        .table td:nth-child(4) { width: 15%; text-align: right; }
        .table th:nth-child(5),
        .table td:nth-child(5) { width: 10%; text-align: center; }
        .table th:nth-child(6),
        .table td:nth-child(6) { width: 15%; text-align: right; }
        .table th:nth-child(7),
        .table td:nth-child(7) { width: 14%; text-align: right; }

        /* Footer styling */
        .table tfoot td {
            border-top: 2px solid #000;
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .table tfoot .text-end {
            text-align: right;
        }

        /* Footer message */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .footer p,
        .footer small {
            font-size: 12px !important;
            margin: 5px 0;
        }

        /* Receipt bottom container styling */
        .receipt-bottom-container {
            margin-top: 20px;
            font-size: 10px;
            border: none !important;
        }

        .receipt-bottom-container .d-flex {
            display: flex !important;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .receipt-bottom-container span {
            color: red !important;
            font-weight: bold !important;
        }

        /* Ensure proper spacing */
        hr {
            border: 1px solid #000;
            margin: 10px 0;
        }

        /* Remove any extra margins/padding */
        .container-fluid,
        .row,
        .col-lg-8,
        .modal-body {
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>

<!-- Include the receipt print functionality script -->
<script src="../../js/receipt_print.js"></script>