$(document).ready(function() {
   // Show dropdown on input focus
   $('#customer').on('focus', function() {
       preventDefault();
       $('#customer-dropdown').addClass('show');
   });
   // Filter dropdown items as user types
   $('#customer').on('input', function() {
       var val = $(this).val().toLowerCase();
       $('#customer-dropdown li').each(function() {
           var name = $(this).find('.customer-option').data('name').toLowerCase();
           $(this).toggle(name.includes(val));
       });
       $('#customer-dropdown').addClass('show');
       // Clear hidden id if user types
       $('#customer_id').val('');
   });
   // Select customer from dropdown
   $(document).on('click', '.customer-option', function(e) {
       e.preventDefault();
       $('#customer').val($(this).data('name'));
       $('#customer_id').val($(this).data('id'));
       $('#customer-dropdown').removeClass('show');
   });
   // Hide dropdown when clicking outside
   $(document).on('click', function(e) {
       if (!$(e.target).closest('.dropdown').length) {
           $('#customer-dropdown').removeClass('show');
       }
   });
});