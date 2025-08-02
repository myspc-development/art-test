<?php

use ArtPulse\Widgets\EventsWidget;

return [
    'activity_feed' => [
        'class' => \ActivityFeedWidget::class,
        'label' => 'Activity Feed',
        'description' => 'Shows recent user and org activity',
        'roles' => ['member', 'artist', 'organization'],
        'category' => 'user',
        'icon' => 'activity',
    ],
    'qa_checklist' => [
        'class' => \QAChecklistWidget::class,
        'label' => 'QA Checklist',
        'description' => 'Steps to verify basic dashboard features.',
        'roles' => ['member'],
        'icon' => 'yes',
    ],
    'sample_events' => [
        'class' => EventsWidget::class,
        'label' => 'Events Widget',
        'description' => 'Sample upcoming events list.',
        'roles' => ['member', 'artist', 'organization'],
        'icon' => 'calendar',
        'category' => 'community',
    ],
];
