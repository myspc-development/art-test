<?php
if (!defined('ABSPATH')) { exit; }
wp_enqueue_script('ap-update-diagnostics');
wp_localize_script('ap-update-diagnostics', 'AP_UpdateData', [
    'endpoint' => rest_url('artpulse/v1/update/diagnostics'),
    'nonce'    => wp_create_nonce('wp_rest'),
]);
?>
<div class="wrap">
  <h1>Update Diagnostics</h1>
  <div id="ap-update-output">Loading…</div>
</div>
