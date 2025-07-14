<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Content list.
 */
?>
<section id="content" class="ap-dashboard-section dashboard-card" data-widget="content" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('Your Content','artpulse'); ?></h2>
    <div id="ap-user-content"></div>
</section>
