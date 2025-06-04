<!-- Bootstrap Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm" method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="name" class="form-label">Item Name/ Description</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter item dame & description" required>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" placeholder="Enter item stock or quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="sold_by" class="form-label">Sold By</label>
                        <select class="form-select" id="sold_by" name="sold_by" required>
                            <option value="" selected disabled>--  --</option>
                            <option value="PCS">PIECE</option>
                            <option value="PCK">PACK</option>
                            <option value="BOX">BOX</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="" selected disabled>-- Select category --</option>
                            <option value="HARDWARE">HARDWARE</option>
                            <option value="ELECTRICAL">ELECTRICAL</option>
                            <option value="PLUMBING">PLUMBING</option>
                            <option value="PVC FITTING">PVC FITTING</option>
                            <option value="G.I FITTING">G.I FITTING</option>
                            <option value="PPR FITTING">PPR FITTING</option>
                            <option value="BLUE FITTING">BLUE FITTING</option>
                            <option value="P.E FITTING">P.E FITTING</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cost" class="form-label">Cost (₱)</label>
                        <input type="number" class="form-control" id="cost" name="cost" step="0.01" min="0" placeholder="Enter cost in peso" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" placeholder="Enter price in peso" required>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
