<?php
extract(ap_template_context($args ?? [], ['visible' => true]));
/**
 * Dashboard widget: My Events.
 */
?>
<div id="my-events" class="ap-card" role="region" aria-labelledby="my-events-title" data-widget="my-events" <?php echo $visible ? '' : 'hidden'; ?>>
    <h2 id="my-events-title" class="ap-card__title"><?php esc_html_e('My Events','artpulse'); ?></h2>
    <div id="ap-dashboard-stats" class="ap-dashboard-stats"></div>
    <div id="ap-next-event"></div>
    <div id="ap-my-events"></div>
    <canvas id="ap-trends-chart" height="150"></canvas>
    <canvas id="ap-user-engagement-chart" height="150"></canvas>
    <canvas id="ap-profile-metrics-chart" height="150"></canvas>
    <canvas id="ap-event-analytics-chart" height="150"></canvas>
    <button class="ap-widget-settings-btn ap-form-button nectar-button" data-widget-settings="my-events"><?php esc_html_e('Settings', 'artpulse'); ?></button>
</div>
