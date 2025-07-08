<?php
/**
 * Dashboard widget: Content list.
 */
?>
<div class="dashboard-card" data-widget="content" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2 id="content"><?php esc_html_e('Your Content','artpulse'); ?></h2>
    <div id="ap-user-content"></div>
</div>
