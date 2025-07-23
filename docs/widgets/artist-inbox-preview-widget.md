---
title: Artist Inbox Preview Widget
category: widgets
role: developer
last_updated: 2025-07-20
status: complete
---

# Artist Inbox Preview Widget

The **Artist Inbox Preview** widget shows a snapshot of unread follower messages so creators can quickly see new activity without opening the full inbox. Only users with the **artist** role receive this widget by default.

## Location
- JavaScript: [`assets/js/widgets/ArtistInboxPreviewWidget.jsx`](../assets/js/widgets/ArtistInboxPreviewWidget.jsx)
- PHP template: [`templates/widgets/artist-inbox-preview.php`](../templates/widgets/artist-inbox-preview.php)

## Data Displayed
- Up to the three most recent conversation threads
- Each entry lists the follower's display name, a snippet of the last message and the message date
- Footer link leads to the full `/inbox` page

## Data Retrieval
`ArtistInboxPreviewWidget.jsx` fetches data from the REST API:

1. `GET /artpulse/v1/conversations` lists conversation threads for the current artist
2. For each thread it requests `GET /artpulse/v1/messages?with={user_id}` to obtain the latest message

Results are stored in local state via `useState` so the component re-renders when the API calls complete. If the API fails, the widget displays `No new messages.`

## Configuration
The widget has no special settings. Dashboard layouts determine its visibility. `DashboardController` includes `artist_inbox_preview` in the default artist preset and it registers through `DashboardWidgetRegistry` in `includes/dashboard-widgets.php`:

```php
DashboardWidgetRegistry::register(
    'artist_inbox_preview',
    __('Artist Inbox Preview', 'artpulse'),
    'inbox',
    __('Recent unread messages from artists.', 'artpulse'),
    'ap_widget_artist_inbox_preview',
    [
        'roles'    => ['artist'],
        'category' => 'engagement',
    ]
);
```

## Preview vs Full Inbox
The preview only displays brief message excerpts. Artists click **View All Messages** to open the inbox page where they can read entire threads and reply. No compose form is present in the preview widget.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
