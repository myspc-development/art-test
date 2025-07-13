<?php
$visible = $visible ?? true;
/** Dashboard widget: My Follows */
?>
<section id="my-follows" class="ap-dashboard-section dashboard-card" data-widget="my_follows" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php esc_html_e('My Follows','artpulse'); ?></h2>
    <div class="ap-my-follows">
        <div class="ap-directory-results"></div>
    </div>
</section>
