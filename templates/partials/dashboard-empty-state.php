<?php
/**
 * Reusable dashboard empty state component.
 *
 * Optional variables when including:
 * - $icon   (HTML string for an icon)
 * - $title  (string)
 * - $body   (string)
 * - $action (HTML string for a button/link)
 *
 * @package ArtPulse
 *
 * phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
 */

$icon   = $icon ?? '';
$title  = $title ?? esc_html__( 'Nothing here', 'artpulse' );
$body   = $body ?? esc_html__( 'Nothing to display.', 'artpulse' );
$action = $action ?? '';
?>
<div class="ap-empty-state" role="status" aria-live="polite">
	<?php if ( $icon ) : ?>
	<div class="ap-empty-state__icon"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon HTML is intentionally rendered. ?></div>
	<?php endif; ?>
	<?php if ( $title ) : ?>
	<h3 class="ap-empty-state__title"><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>
	<?php if ( $body ) : ?>
	<p class="ap-empty-state__body"><?php echo esc_html( $body ); ?></p>
	<?php endif; ?>
	<?php if ( $action ) : ?>
	<div class="ap-empty-state__action"><?php echo $action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Action HTML is intentionally rendered. ?></div>
	<?php endif; ?>
</div>
