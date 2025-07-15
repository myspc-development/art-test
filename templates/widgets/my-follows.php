<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/** Dashboard widget: My Follows */
?>
<div id="my-follows" class="ap-card" role="region" aria-labelledby="my-follows-title" data-widget="my_follows" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="my-follows-title" class="ap-card__title"><?php esc_html_e('My Follows','artpulse'); ?></h2>
    <div class="ap-my-follows">
        <div class="ap-directory-results"></div>
    </div>
</div>
