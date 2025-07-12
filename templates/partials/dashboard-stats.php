<?php
/**
 * Stats overview section.
 *
 * Expected variable: $stats (array of objects with ->name).
 */
?>
<div class="dashboard-card" id="ap-dashboard-stats">
<?php if (!empty($stats)) : ?>
  <ul class="ap-dashboard-stats-list">
  <?php foreach ($stats as $item) : ?>
    <li><?php echo esc_html($item->name); ?></li>
  <?php endforeach; ?>
  </ul>
<?php else : ?>
  <?php include __DIR__ . '/dashboard-empty-state.php'; ?>
<?php endif; ?>
</div>
