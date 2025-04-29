<!-- Receipt Details Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">Receipt Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="printable-area">
                <div class="receipt-header text-center mb-4">
                    <h2>GOP Marketing</h2>
                    <div>123 Sitio Wangag, Brgy. Damula-an, Albuera, Leyte</div>
                    <div>Tel: 0987654321</div>
                    <hr>
                </div>
                <div class="receipt-details mb-4">
                    <div><strong>Receipt #:</strong> <span id="receipt-id"></span></div>
                    <div><strong>Date:</strong> <span id="receipt-date"></span></div>
                    <div><strong>Customer:</strong> <span id="receipt-customer"></span></div>
                    <div><strong>Address:</strong> <span id="receipt-address"></span></div>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Quantity</th>
                            <th>Item</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="receipt-items">
                        <!-- Items will be inserted here dynamically -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
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
        body * {
            visibility: hidden;
        }
        #printable-area, #printable-area * {
            visibility: visible;
        }
        #printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .modal-footer {
            display: none;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .receipt-details {
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #666;
        }
    }
</style>