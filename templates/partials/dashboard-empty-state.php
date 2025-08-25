<?php
/**
 * Reusable dashboard empty state component.
 *
 * Optional variables when including:
 * - $icon   (HTML string for an icon)
 * - $title  (string)
 * - $body   (string)
 * - $action (HTML string for a button/link)
 */
$icon   = $icon ?? '';
$title  = $title ?? __('Nothing here', 'artpulse');
$body   = $body ?? __('Nothing to display.', 'artpulse');
$action = $action ?? '';
?>
<div class="ap-empty-state" role="status" aria-live="polite">
  <?php if ($icon) : ?>
    <div class="ap-empty-state__icon"><?= $icon; ?></div>
  <?php endif; ?>
  <?php if ($title) : ?>
    <h3 class="ap-empty-state__title"><?= esc_html($title); ?></h3>
  <?php endif; ?>
  <?php if ($body) : ?>
    <p class="ap-empty-state__body"><?= esc_html($body); ?></p>
  <?php endif; ?>
  <?php if ($action) : ?>
    <div class="ap-empty-state__action"><?= $action; ?></div>
  <?php endif; ?>
</div>
