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
        'cache' => true,
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
        'cache' => true,
        'lazy' => true,
    ],
    'audience_crm' => [
        'callback' => 'render_widget_audience_crm',
        'label' => 'Audience CRM',
        'description' => 'Manage organization contacts.',
        'roles' => ['organization'],
        'icon' => 'groups',
        'lazy' => true,
    ],
    'sponsored_event_config' => [
        'callback' => 'render_widget_sponsored_event_config',
        'label' => 'Sponsored Event Config',
        'description' => 'Configure sponsored event settings.',
        'roles' => ['organization'],
        'icon' => 'star',
        'lazy' => true,
    ],
    'embed_tool' => [
        'callback' => 'render_widget_embed_tool',
        'label' => 'Embed Tool',
        'description' => 'Generate embeddable event widgets.',
        'roles' => ['organization'],
        'icon' => 'share',
        'lazy' => true,
    ],
    'org_event_overview' => [
        'callback' => 'render_widget_org_event_overview',
        'label' => 'Event Overview',
        'description' => 'Overview of upcoming organization events.',
        'roles' => ['organization'],
        'icon' => 'calendar',
        'lazy' => true,
    ],
    'org_team_roster' => [
        'callback' => 'render_widget_org_team_roster',
        'label' => 'Team Roster',
        'description' => 'List and manage team members.',
        'roles' => ['organization'],
        'icon' => 'admin-users',
        'lazy' => true,
    ],
];
