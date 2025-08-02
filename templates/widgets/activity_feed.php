<?php if (!empty($logs)) : ?>
<section data-widget="<?= esc_attr(ActivityFeedWidget::id()); ?>" class="ap-widget ap-<?= esc_attr(ActivityFeedWidget::id()); ?>">
    <ul class="ap-activity-feed">
        <?php foreach ($logs as $row) : ?>
            <li><?= esc_html($row->description); ?> <em><?= esc_html(date_i18n(get_option('date_format') . ' H:i', strtotime($row->logged_at))); ?></em></li>
        <?php endforeach; ?>
    </ul>
</section>
<?php endif; ?>
