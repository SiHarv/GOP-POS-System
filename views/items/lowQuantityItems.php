<?php
$lowStockThreshold = 15;
$purchaseThreshold = 10;
$lowStockItems = [];
$purchaseNeededCount = 0;

if (isset($items) && is_array($items)) {
   foreach ($items as $item) {
      if ($item['stock'] < $lowStockThreshold) {
         $lowStockItems[] = $item;
         if ($item['stock'] <= $purchaseThreshold) {
            $purchaseNeededCount++;
         }
      }
   }
}

usort($lowStockItems, function ($a, $b) {
   return $a['stock'] <=> $b['stock'];
});
?>

<div class="low-quantity-panel" style="height: 0;">
   <div class="card shadow-sm">
      <div class="card-header bg-dark text-dark py-2">
         <h6 class="mb-0 fw-bold text-white text-start">
            <span class="iconify me-1" data-icon="solar:danger-triangle-linear" data-width="16" style="margin-bottom: 2px;"></span>
            Low Stock Alert
         </h6>
      </div>
      <div class="card-body p-1" style="max-height: 500px; overflow-y: auto;">
         <?php if (isset($sidebarItems) && !empty($sidebarItems)): ?>
            <?php foreach ($sidebarItems as $item):
               $stockClass = '';
               $stockIcon = '';
               if ($item['stock'] <= 5) {
                  $stockClass = 'text-danger';
                  $stockIcon = 'solar:danger-triangle-bold';
               } elseif ($item['stock'] <= 15) {
                  $stockClass = 'text-warning';
                  $stockIcon = 'solar:danger-triangle-linear';
               }
            ?>
               <div class="low-stock-item mb-2 p-2 border-bottom">
                  <div class="d-flex align-items-center">
                     <div class="stock-indicator me-2">
                        <div class="<?php echo $stockClass; ?> fw-bold d-flex align-items-center" style="font-size: 0.85rem;">
                           <span class="iconify me-1" data-icon="<?php echo $stockIcon; ?>" data-width="12"></span>
                           <span><?php echo $item['stock']; ?></span>
                        </div>
                     </div>
                     <div class="flex-grow-1 item-details-container">
                        <div class="fw-bold text-primary" style="font-size: 0.8rem;">
                           <?php echo substr($item['name'], 0, 18); ?><?php echo strlen($item['name']) > 18 ? '...' : ''; ?>
                        </div>
                        <div class="text-muted" style="font-size: 0.7rem;">
                           <?php echo $item['category']; ?>
                        </div>
                        <div class="text-muted sold-by" style="font-size: 0.65rem;">
                           Unit: <?php echo strlen($item['sold_by']) > 12 ? substr($item['sold_by'], 0, 10) . '...' : $item['sold_by']; ?>
                        </div>
                     </div>
                  </div>
               </div>
            <?php endforeach; ?>
         <?php else: ?>
            <div class="text-center text-muted py-3">
               <span class="iconify" data-icon="solar:check-circle-linear" data-width="32"></span>
               <div class="mt-2">All items are well stocked</div>
            </div>
         <?php endif; ?>
      </div>
   </div>
</div>