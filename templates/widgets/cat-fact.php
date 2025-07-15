<?php
// Fetch a random cat fact from the catfact.ninja API.
$fact = '';
$response = wp_remote_get('https://catfact.ninja/fact');
if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (isset($data['fact'])) {
        $fact = $data['fact'];
    }
}
?>
<div class="ap-widget notice notice-info p-4 rounded">
  <div class="ap-widget-header">😺 <?php esc_html_e('Cat Fact', 'artpulse'); ?></div>
  <div class="ap-widget-body">
    <?php if ($fact): ?>
      <p><?php echo esc_html($fact); ?></p>
    <?php else: ?>
      <p><?php esc_html_e('Could not load cat fact.', 'artpulse'); ?></p>
    <?php endif; ?>
  </div>
</div>
