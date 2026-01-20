<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemModalLabel">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editItemForm" method="post">
                    <input type="hidden" id="edit_item_id" name="id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Item Name & Description</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_stock" class="form-label">Current Stock (editable)</label>
                        <input type="number" class="form-control" id="edit_stock" name="stock" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="edit_new_stock" class="form-label">Add New Stock</label>
                        <input type="number" class="form-control" id="edit_new_stock" name="new_stock" min="0" value="0">
                    </div>
                    <div class="mb-3">
                        <label for="edit_sold_by" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="edit_sold_by" name="sold_by" placeholder="Enter unit" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="edit_category" name="category" placeholder="Enter category" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_cost" class="form-label">Cost (₱)</label>
                        <input type="number" class="form-control" id="edit_cost" name="cost" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price (₱)</label>
                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>