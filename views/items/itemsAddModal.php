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
                        <label for="name" class="form-label">Item Name & Description</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter item dame & description" required>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="0" placeholder="Enter item stock or quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="sold_by" class="form-label">UNIT</label>
                        <input type="text" class="form-control" id="sold_by" name="sold_by" placeholder="Enter unit" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" placeholder="Enter category" required>
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
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
