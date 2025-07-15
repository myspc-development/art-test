<?php
extract(ap_template_context($args ?? [], [
    'id' => 'guide',
    'title' => '',
    'content' => '',
    'visible' => true,
]));
?>
<section id="<?php echo esc_attr($id); ?>" class="ap-dashboard-section dashboard-card" data-widget="<?php echo esc_attr($id); ?>" <?php echo $visible ? '' : 'style="display:none"'; ?>>
    <h2><?php echo esc_html($title); ?></h2>
    <div class="ap-guide-content">
        <?php echo $content; ?>
    </div>
</section>
