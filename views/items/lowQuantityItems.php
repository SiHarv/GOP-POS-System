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

<div class="low-quantity-panel">
   <div class="panel-header">
      <span class="iconify text-danger me-2" data-icon="mdi:alert-circle" data-width="24"></span>
      <h5 class="mb-0">Low stock products</h5>
      <?php if ($purchaseNeededCount > 0): ?>
         <span class="badge bg-danger ms-2"><?php echo $purchaseNeededCount; ?></span>
      <?php endif; ?>
   </div>

   <div class="panel-body">
      <?php if (empty($lowStockItems)): ?>
         <div class="no-items-message">
            <span class="iconify mb-2" data-icon="mdi:check-circle" data-width="32" style="color: #28a745;"></span>
            <p>All items are well-stocked!</p>
         </div>
      <?php else: ?>
         <div class="low-quantity-list">
            <?php foreach ($lowStockItems as $item): ?>
               <div class="low-quantity-item <?php echo ($item['stock'] <= $purchaseThreshold) ? 'purchase-needed' : ''; ?>">
                  <div class="item-details">
                     <h6><?php echo $item['name']; ?></h6>
                     <div class="item-meta">
                        <small><?php echo $item['category']; ?> · <?php echo $item['sold_by']; ?></small>
                     </div>
                     <div class="stock-status mt-2">
                        <div class="progress" style="height: 4px;">
                           <div class="progress-bar <?php echo ($item['stock'] <= 5) ? 'bg-danger' : 'bg-warning'; ?>"
                              role="progressbar"
                              style="width: <?php echo min(($item['stock'] / $lowStockThreshold) * 100, 100); ?>%"
                              aria-valuenow="<?php echo $item['stock']; ?>"
                              aria-valuemin="0"
                              aria-valuemax="<?php echo $lowStockThreshold; ?>">
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="item-actions">
                     <span class="stock-count <?php echo ($item['stock'] <= 5) ? 'critical' : 'warning'; ?>">
                        <?php echo $item['stock']; ?>
                     </span>
                     <?php if ($item['stock'] <= $purchaseThreshold): ?>
                        <span class="purchase-indicator mt-2">
                           <span class="iconify" data-icon="mdi:shopping" data-width="16"></span>
                           Purchase needed
                        </span>
                     <?php endif; ?>
                  </div>
               </div>
            <?php endforeach; ?>
         </div>
      <?php endif; ?>
   </div>
</div>