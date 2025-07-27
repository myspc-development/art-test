<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
?>
<div id="collab-requests" class="ap-card" role="region" aria-labelledby="collab-requests-title" data-widget="collab-requests" <?php echo $visible ? '' : 'hidden'; ?>>
  <h2 id="collab-requests-title" class="ap-card__title"><?php esc_html_e('Collab Requests', 'artpulse'); ?></h2>
  <div class="ap-react-widget" data-widget-id="collab_requests"></div>
</div>
