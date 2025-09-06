<?php
/**
 * Template for displaying single ArtPulse Event content
 * Fetches event details from post meta.

error_log('📦 content-artpulse_event.php loaded');

?>

<div class="container-wrap">
	<div class="container">
	<div class="row">
		<div class="col span_12">

		<?php if (has_post_thumbnail()) : ?>
			<div class="event-featured-image">
			<?php the_post_thumbnail('large', ['class' => 'img-responsive']); ?>
			</div>
		<?php endif; ?>

		<h1 class="event-title"><?php the_title(); ?></h1>

		<?php
			// Fetch post meta
			$date    = get_post_meta(get_the_ID(), '_ap_event_date', true);
			$venue   = get_post_meta(get_the_ID(), '_ap_event_venue', true);
			$address = get_post_meta(get_the_ID(), '_ap_event_address', true);
			$start   = get_post_meta(get_the_ID(), '_ap_event_start_time', true);
			$end     = get_post_meta(get_the_ID(), '_ap_event_end_time', true);
			$contact = get_post_meta(get_the_ID(), '_ap_event_contact', true);
			$rsvp    = get_post_meta(get_the_ID(), '_ap_event_rsvp', true);
		?>

		<ul class="event-meta styled-box">
			<?php if ($date): ?>
			<li><strong><?php esc_html_e('Date:', 'artpulse'); ?></strong> <?= esc_html($date); ?></li>
			<?php endif; ?>
			<?php if ($venue): ?>
			<li><strong><?php esc_html_e('Venue:', 'artpulse'); ?></strong> <?= esc_html($venue); ?></li>
			<?php endif; ?>
			<?php if ($address): ?>
			<li><strong><?php esc_html_e('Address:', 'artpulse'); ?></strong> <?= esc_html($address); ?></li>
			<?php endif; ?>
			<?php if ($start || $end): ?>
			<li><strong><?php esc_html_e('Time:', 'artpulse'); ?></strong>
				<?= esc_html($start); ?>
				<?= ($start && $end) ? ' – ' : ''; ?>
				<?= esc_html($end); ?>
			</li>
			<?php endif; ?>
			<?php if ($contact): ?>
			<li><strong><?php esc_html_e('Contact:', 'artpulse'); ?></strong> <?= esc_html($contact); ?></li>
			<?php endif; ?>
			<?php if ($rsvp): ?>
			<li><strong><?php esc_html_e('RSVP:', 'artpulse'); ?></strong> <a href="<?= esc_url($rsvp); ?>" target="_blank"><?php esc_html_e('Reserve Now', 'artpulse'); ?></a></li>
			<?php endif; ?>
		</ul>

		<div class="event-description">
			<?php the_content(); ?>
		</div>

		</div>
	</div>
	</div>
</div>
