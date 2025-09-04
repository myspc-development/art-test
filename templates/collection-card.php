<?php
/**
 * Collection card layout.
 *
 * Variable: $collection_id (int)
 */
if (!isset($collection_id)) {
    return;
}

$collection = get_post($collection_id);
if (!$collection || $collection->post_type !== 'ap_collection') {
    return;
}

$title     = get_the_title($collection);
$permalink = get_permalink($collection);
$image     = get_the_post_thumbnail($collection_id, 'medium', ['alt' => $title]);
$curator   = get_the_author_meta('display_name', $collection->post_author);
?>
<div class="ap-collection-card nectar-box ap-widget" id="collection-<?php echo esc_attr($collection_id); ?>">
    <a href="<?php echo esc_url($permalink); ?>" class="ap-collection-thumb">
        <?php if ($image) : ?>
            <?php echo $image; ?>
        <?php endif; ?>
        <h3 class="ap-collection-title"><?php echo esc_html($title); ?></h3>
    </a>
    <div class="ap-collection-card-content">
        <?php if ($curator) : ?>
            <div class="ap-collection-curator">
                <?php printf( esc_html__( 'Curated by %1$s', 'artpulse' ), esc_html( $curator ) ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
