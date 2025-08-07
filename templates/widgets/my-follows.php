<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!user_can(get_current_user_id(), 'read')) return;
extract(ap_template_context($args ?? [], ['visible' => true]));
/** Dashboard widget: My Follows */
?>
<div id="my-follows" class="ap-card" role="region" aria-labelledby="my-follows-title" data-widget="my_follows" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="my-follows-title" class="ap-card__title"><?php esc_html_e('My Follows','artpulse'); ?></h2>
    <div class="ap-my-follows">
        <div class="ap-directory-results"></div>
    </div>
</div>
