# Stock Subtraction on Print Receipt Feature

## Overview
This implementation moves the stock subtraction functionality from the "Process Charge" action to the "Print Receipt" action. This ensures that inventory is only updated when the receipt is actually printed, providing better control over stock management.

## How It Works

### 1. Process Charge (Modified)
- When user clicks "Process Charge", a receipt is created in the database
- **NO stock subtraction occurs at this point**
- Receipt is marked as `finalized = 0` (not finalized)
- User is given option to "View Receipt" or "Create Another"

### 2. Print Receipt (New Functionality)
- When user clicks "Print Receipt" in the receipt modal:
  1. System shows confirmation dialog warning that this action cannot be undone
  2. If confirmed, sends AJAX request to finalize the receipt
  3. Backend checks stock availability for all items
  4. If sufficient stock exists, subtracts quantities from inventory
  5. Marks receipt as `finalized = 1` and sets `finalized_date`
  6. Triggers browser print functionality
  7. Updates are made within a database transaction (rollback on any error)

## Files Created/Modified

### New Files Created:
1. **`js/receipt_print.js`** - Handles print receipt functionality with stock subtraction
2. **`db/add_finalized_columns.sql`** - SQL script to add finalized tracking columns
3. **`db/update_database.php`** - PHP script to safely update the database

### Files Modified:
1. **`controller/backend_charge.php`** - Removed stock subtraction from processCharge method
2. **`controller/backend_receipts.php`** - Added finalizeReceipt method and handler
3. **`js/charge.js`** - Updated success callback to offer receipt viewing
4. **`js/receipts.js`** - Added receipt ID tracking for print functionality
5. **`views/receipts/receiptViewModal.php`** - Included new JavaScript file
6. **`views/receipts/receipts.php`** - Added SweetAlert2 and auto-open receipt functionality

## Database Changes

Added two new columns to the `charges` table:
- `finalized` (TINYINT): 0 = not finalized, 1 = finalized/printed
- `finalized_date` (TIMESTAMP): When the receipt was finalized/printed

## Usage Flow

### Creating a Charge:
1. User selects customer and adds items to cart
2. User enters P.O. Number
3. User clicks "Process Charge"
4. Receipt is created (stock not yet subtracted)
5. User can choose to view the receipt or create another charge

### Printing and Finalizing:
1. User views receipt (either from charge page or receipts page)
2. User clicks "Print Receipt"
3. System confirms action with warning about irreversibility
4. If confirmed:
   - Stock is checked and subtracted
   - Receipt is marked as finalized
   - Print dialog opens
   - Changes are permanent

## Benefits

1. **Better Stock Control**: Stock only updates when receipt is actually printed
2. **Prevents Double Charging**: Once printed, receipt cannot subtract stock again (idempotent)
3. **Error Recovery**: If printing fails, stock hasn't been affected yet
4. **Audit Trail**: Clear tracking of when receipts were finalized
5. **User Flexibility**: Can create receipts and decide later when to print

## Error Handling

- Insufficient stock errors are caught and reported
- Database transactions ensure data consistency
- Idempotent operations prevent double-processing
- Clear user feedback through SweetAlert2 dialogs

## Technical Notes

- Uses MySQL transactions for data integrity
- JavaScript uses async/await pattern for better error handling
- Responsive design maintained across all screen sizes
- Print styles optimized for receipt formatting
