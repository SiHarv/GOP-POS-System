<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm" method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter customer name" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Enter customer's phone number" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter customer's address (delivery address)" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="terms" class="form-label">Terms (COD / monthly payment, etc.)</label>
                        <select class="form-select" id="terms" name="terms" required>
                            <option value="COD">COD</option>
                            <option value="Monthly payment">Monthly payment</option>
                            <!-- <option value="BOX">BOX</option> -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="salesman" class="form-label">Salesman:</label>
                        <input type="text" name="salesman" id="salesman" class="form-control" placeholder="Enter salesman name" required>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>