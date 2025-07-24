<?php
$roles = array_intersect(['member','artist','organization'], wp_get_current_user()->roles);
if (count($roles) <= 1) {
    return; // no tabs needed
}
$labels = [
    'member' => __('Member Tools', 'artpulse'),
    'artist' => __('Artist Tools', 'artpulse'),
    'organization' => __('Org Tools', 'artpulse'),
];
wp_enqueue_script('ap-role-tabs', plugin_dir_url(__FILE__) . '../../assets/js/dashboard-role-tabs.js', [], null, true);
?>
<div class="ap-role-tabs">
  <?php foreach ($roles as $r): ?>
    <button type="button" class="ap-role-tab" data-role="<?= esc_attr($r) ?>">
      <?= esc_html($labels[$r] ?? $r) ?>
    </button>
  <?php endforeach; ?>
</div>
