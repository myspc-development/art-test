<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: Content list.
 */
?>
<div id="content" class="ap-card" role="region" aria-labelledby="content-title" data-widget="content" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="content-title" class="ap-card__title"><?php esc_html_e('Your Content','artpulse'); ?></h2>
    <div id="ap-user-content"></div>
</div>
