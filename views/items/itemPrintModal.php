<!-- Print Items Modal -->
<div class="modal fade" id="printItemsModal" tabindex="-1" aria-labelledby="printItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printItemsModalLabel">Print Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="categorySelect" class="form-label">Select Category:</label>
                        <select class="form-select" id="categorySelect">
                            <option value="">Loading categories...</option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" id="loadItemsBtn">
                            <span class="iconify" data-icon="solar:refresh-outline" data-width="20" data-height="20"></span>
                            Load Items
                        </button>
                    </div>
                </div>
                
                <!-- Loading spinner -->
                <div id="loadingSpinner" class="text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading items...</p>
                </div>

<style>
/* Modal table styling */
#printItemsTable {
    table-layout: fixed;
}

#printItemsTable th:nth-child(1),
#printItemsTable td:nth-child(1) {
    width: 8%;
    text-align: center;
} /* Item ID - centered and reduced width */

#printItemsTable th:nth-child(2),
#printItemsTable td:nth-child(2) {
    text-align: center;
} /* Item Name - centered */

#printItemsTable th:nth-child(3),
#printItemsTable td:nth-child(3) {
    text-align: center;
} /* Category - centered */

#printItemsTable th:nth-child(4),
#printItemsTable td:nth-child(4) {
    text-align: center;
} /* Sold By - centered */
</style>
                
                <!-- Items table container -->
                <div id="itemsTableContainer" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="printItemsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th class="fw-bold text-center" style="width: 8%;">Item ID</th>
                                    <th class="fw-bold text-center">Item Name</th>
                                    <th class="fw-bold text-center">Category</th>
                                    <th class="fw-bold text-center">Sold By</th>
                                    <th class="fw-bold text-end">Cost</th>
                                    <th class="fw-bold text-end">Price</th>
                                    <th class="fw-bold text-end">Stock</th>
                                </tr>
                            </thead>
                            <tbody id="printItemsTableBody">
                                <!-- Items will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <p class="text-muted">Total Items: <span id="totalItemsCount">0</span></p>
                        </div>
                    </div>
                </div>
                
                <!-- No items message -->
                <div id="noItemsMessage" class="text-center" style="display: none;">
                    <p class="text-muted">No items found for the selected category.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="printBtn" style="display: none;">
                    <span class="iconify" data-icon="solar:printer-outline" data-width="20" data-height="20"></span>
                    Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print-specific styles -->
<style>
#printArea {
    display: none;
}

@media print {
    * {
        visibility: hidden;
    }
    
    #printArea,
    #printArea * {
        visibility: visible;
    }
    
    #printArea {
        display: block !important;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 20px;
        font-family: Arial, sans-serif;
    }
    
    .print-header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #000;
        padding-bottom: 15px;
    }
    
    .print-header h2 {
        margin: 0 0 10px 0;
        font-size: 24px;
        font-weight: bold;
    }
    
    .print-header h3 {
        margin: 0 0 10px 0;
        font-size: 18px;
        color: #333;
    }
    
    .print-header p {
        margin: 0;
        font-size: 12px;
        color: #666;
    }
    
    .print-table {
        width: calc(100% - 2in);
        margin: 0 1in;
        border-collapse: collapse;
        font-size: 11px;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    
    .print-table th,
    .print-table td {
        border: 1px solid #666;
        padding: 6px;
        text-align: left;
    }
    
    .print-table th {
        background-color: transparent !important;
        color: #000000 !important;
        font-weight: 600 !important;
        text-align: center !important;
        font-size: 12px !important;
        padding: 4px 3px !important;
        border: 1px solid #666 !important;
    }
    
    
    /* Right align price and stock columns in print */
    .print-table th:nth-child(5),
    .print-table th:nth-child(6),
    .print-table th:nth-child(7) {
        text-align: right !important;
        font-weight: 600 !important;
        background-color: transparent !important;
        color: #000000 !important;
    }
    
    .print-table td:nth-child(5),
    .print-table td:nth-child(6),
    .print-table td:nth-child(7) {
        text-align: right !important;
    }
    
    /* Additional classes for preserved Bootstrap styling in print */
    .print-header-right {
        text-align: right !important;
        font-weight: bold !important;
    }
    
    .print-cell-right {
        text-align: right !important;
    }
    
    /* Ensure all table headers are bold */
    .print-table thead th {
        font-weight: 600 !important;
        background-color: transparent !important;
        color: #000000 !important;
        border: 1px solid #666 !important;
        font-size: 12px !important;
        padding: 4px 3px !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Ensure table data styling */
    .print-table tbody td {
        border: 1px solid #666 !important;
        padding: 3px 2px !important;
    }
    
    
    
    /* Alternative header styling if background colors fail */
    @media print {
        .print-table th {
            background: transparent !important;
            color: #000000 !important;
        }
        
        /* Force black text */
        .print-table thead th {
            background: none !important;
            color: #000000 !important;
            font-weight: 600 !important;
        }
    }
    
    .print-table tbody tr:nth-child(even) {
        background-color: #f9f9f9 !important;
    }
    
    .print-footer {
        margin-top: 30px;
        text-align: center;
        font-size: 10px;
        color: #666;
        border-top: 1px solid #ccc;
        padding-top: 15px;
    }
    
    .print-footer p {
        margin: 5px 0;
    }
    
    /* Hide modal and other page elements when printing */
    .modal,
    .modal-backdrop,
    nav,
    .sidebar,
    .main-content .container-fluid > .row > .col-lg-1,
    .main-content .container-fluid > .row > .col-lg-2 {
        display: none !important;
    }
}
</style>

<!-- Hidden print area -->
<div id="printArea" style="display: none;">
    <!-- Content will be dynamically generated here for printing -->
</div>
