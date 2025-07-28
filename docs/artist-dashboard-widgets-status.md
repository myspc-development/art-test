# Artist Dashboard Widgets – Status Check

The following widgets were reviewed on 2025-07-23. They comprise all widgets registered for the `artist` role in `DashboardController` and `widget-manifest.json`. No organization widgets appear in the artist dashboard layout.

| Widget | Implemented | Key Files |
|--------|-------------|----------|
| Activity Feed | ✅ | `widgets/ActivityFeedWidget.php` |
| Artwork Manager | ✅ | `assets/js/widgets/ArtistArtworkManagerWidget.jsx` |
| Audience Insights | ✅ | `assets/js/widgets/ArtistAudienceInsightsWidget.jsx` |
| Earnings Summary | ✅ | `assets/js/widgets/ArtistEarningsWidget.jsx` |
| Post & Engage | ✅ | `assets/js/widgets/ArtistFeedPublisherWidget.jsx` |
| Inbox Preview | ✅ | `assets/js/widgets/ArtistInboxPreviewWidget.jsx` |
| Revenue Summary | ✅ | `assets/js/widgets/ArtistRevenueSummaryWidget.jsx`, REST: `src/Monetization/SalesOverview.php` |
| Artist Spotlight | ✅ | `assets/js/widgets/ArtistSpotlightWidget.jsx`, REST: `src/Rest/SpotlightRestController.php` |
| Cat Fact | ✅ | `templates/widgets/cat-fact.php` |
| Collab Requests | ✅ | `assets/js/widgets/ArtistCollaborationWidget.jsx` |
| Dashboard Feedback | ✅ | `templates/widgets/dashboard-feedback.php` |
| Messages | ✅ | `templates/widgets/messages.php` |
| My Events | ✅ | `templates/widgets/my-events.php` |
| Notifications | ✅ | `templates/widgets/notifications.php` |
| Onboarding Checklist | ✅ | `assets/js/widgets/OnboardingTrackerWidget.jsx` |

All listed widgets are currently implemented with either PHP templates or React components and corresponding REST endpoints where required.
