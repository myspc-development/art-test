<?php
$user_id = get_current_user_id();
$messages = get_posts([
    'post_type'      => 'message',
    'meta_key'       => 'recipient_id',
    'meta_value'     => $user_id,
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>
<div id="artist-inbox-preview" class="ap-card" role="region" aria-labelledby="artist-inbox-preview-title" data-widget="artist-inbox-preview">
  <h2 id="artist-inbox-preview-title" class="ap-card__title"><?php esc_html_e('Artist Inbox','artpulse'); ?></h2>
  <?php if ($messages): ?>
    <ul>
      <?php foreach ($messages as $m): ?>
        <li><a href="<?php echo esc_url(get_permalink($m)); ?>"><?php echo esc_html(get_the_title($m)); ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p><?php esc_html_e('No new messages.','artpulse'); ?></p>
  <?php endif; ?>
</div>
