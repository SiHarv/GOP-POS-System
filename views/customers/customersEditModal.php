<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCustomerForm" method="post">
                    <input type="hidden" id="edit_customer_id" name="id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="edit_phone_number" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_terms" class="form-label">Terms (COD / monthly payment, etc.)</label>
                        <select class="form-select mt-2" id="edit_terms" name="terms" required>
                            <option value="COD">COD</option>
                            <option value="Monthly payment">Monthly payment</option>
                            <!-- <option value="BOX">BOX</option> -->
                        </select>
                    </div>
                    <!-- <div class="mb-3">
                        <label for="edit_terms" class="form-label">Terms (COD / monthly payment)</label>
                        <input type="text" class="form-control mt-2" id="edit_terms" name="terms" required>
                    </div> -->
                    <div class="mb-3">
                        <label for="edit_salesman" class="form-label">Salesman:</label>
                        <input type="text" name="salesman" id="edit_salesman" class="form-control" required>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>