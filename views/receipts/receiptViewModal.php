<!-- Receipt Details Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">Receipt Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="printable-area">
                <div class="receipt-header text-center mt-3">
                    <div class="text-start mt-5"
                        style="position:absolute;
                        top: 0px;
                        margin-left: 10px;
                        ">
                        <img src="../../icon/icon.png" alt="gop-icon" style="
                        height: 100px; 
                        width: 80px;
                        ">
                    </div>
                    <h5>GOP MARKETING</h5>
                    <div>Wangag, Damulaan</div>
                    <div>Albuera, Leyte</div>
                    <div class="mt-3"><b>Delivery Receipt</b></div>
                    <!-- <div>Tel: 0987654321</div> -->
                    <div class="text-end me-5"
                        style="position: absolute; right: 20px; top: 100px;">
                        <strong>Receipt #:</strong> <span id="receipt-id"></span>
                    </div>
                    <hr>
                </div>
                <div class="receipt-details mb-4">
                    <div class="d-flex justify-content-between">
                        <div><strong>Customer:</strong> <span id="receipt-customer"></span></div>
                        <div><strong>Date:</strong> <span id="receipt-date"></span></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div><strong>Address:</strong> <span id="receipt-address"></span></div>
                        <div><strong>Terms:</strong> <span id="receipt-terms"></span></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div><strong>P.O. Number:</strong> <span id="receipt-po-number">-</span></div>
                        <div><strong>Salesman:</strong> <span id="receipt-salesman"></span></div>
                    </div>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>QTY</th>
                            <th>UNIT</th>
                            <th style="text-align: center;">ITEM/DESCRIPTION</th>
                            <th>BASE PRICE</th>
                            <th>DISC.</th>
                            <th>NET PRICE</th>
                            <th style="text-align: center;">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody id="receipt-items">
                        <!-- Items will be inserted here dynamically -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end"><strong>Total Amount:</strong></td>
                            <td><strong>₱<span id="receipt-total"></span></strong></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="footer text-center mt-4">
                    <p>Thank you for your business!</p>
                    <small>This is a computer-generated receipt</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="print-receipt">Print Receipt</button>
            </div>
        </div>
    </div>
</div>

<style>
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
            height: 60px !important;
            width: 48px !important;
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
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 8px 4px;
            text-align: left;
            vertical-align: top;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .table td {
            font-size: 10px;
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
            font-size: 10px;
            color: #666;
        }

        .footer p {
            margin: 5px 0;
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