# API Audit Report

Total REST endpoints: 208
Total AJAX actions: 51

## REST Routes

| Namespace | Methods | Path | File:Line | permission_callback | Nonce |
|-----------|---------|------|-----------|-------------------|-------|
| artpulse/v1 | GET | /follow/feed | follow-api.php:10 | yes | no |
| artpulse/v1 | POST | /payment/intent | payments.php:10 | yes | no |
| artpulse/v1 | POST | /payment/checkout | payments.php:23 | yes | no |
| artpulse/v1 | POST | /event/(?P<id>\\d+)/rsvp | includes/class-artpulse-rest-controller.php:14 | yes | no |
| artpulse/v1 | POST | /user/(?P<id>\\d+)/follow | includes/class-artpulse-rest-controller.php:23 | yes | no |
| artpulse/v1 | POST | /widgets/log | src/Analytics/EmbedAnalytics.php:43 | yes | yes |
| artpulse/v1 | ['GET | /webhooks/(?P<id>\d+) | src/Integration/WebhookManager.php:22 | yes | no |
| artpulse/v1 | ['PUT | /webhooks/(?P<id>\d+)/(?P<hid>\d+) | src/Integration/WebhookManager.php:31 | yes | no |
| 'artpulse/v1' | POST | /import | src/Rest/ImportRestController.php:32 | yes | no |
| artpulse/v1 | GET | /preview/dashboard | src/Rest/DashboardPreviewController.php:11 | yes | no |
| artpulse/v1 | ['GET | /org/(?P<id>\d+)/meta | src/Rest/OrgMetaController.php:17 | yes | no |
| 'artpulse/v1' | GET | /artist-events | src/Rest/ArtistEventsController.php:20 | yes | no |
| 'artpulse/v1' | WP_REST_Server::CREATABLE | /roles/toggle | src/Rest/RoleMatrixController.php:22 | yes | no |
| 'artpulse/v1' | WP_REST_Server::CREATABLE | /roles/batch | src/Rest/RoleMatrixController.php:39 | yes | no |
| 'artpulse/v1' | WP_REST_Server::READABLE | /roles/seed | src/Rest/RoleMatrixController.php:51 | yes | no |
| artpulse/v1 | GET | /payment-reports | src/Rest/PaymentReportsController.php:18 | yes | no |
| artpulse/v1 | WP_REST_Server::CREATABLE | /profile/(?P<id>\d+)/verify | src/Rest/ProfileVerificationController.php:19 | yes | no |
| artpulse/v1 | GET | /import-template/(?P<post_type>[^/]+) | src/Rest/ImportTemplateController.php:23 | yes | no |
| artpulse/v1 | GET | /dashboard/messages | src/Rest/DashboardMessagesController.php:11 | yes | no |
| artpulse/v1 | POST | /favorite | src/Rest/FavoriteRestController.php:23 | yes | no |
| artpulse/v1 | POST | /checkin | src/Rest/VisitRestController.php:23 | yes | no |
| artpulse/v1 | GET | /event/(?P<id>\d+)/visits | src/Rest/VisitRestController.php:38 | yes | no |
| artpulse/v1 | GET | /event/(?P<id>\d+)/visits/export | src/Rest/VisitRestController.php:47 | yes | no |
| 'artpulse/v1' | WP_REST_Server::READABLE | /update/diagnostics | src/Rest/UpdateDiagnosticsController.php:21 | yes | no |
| artpulse/v1 | POST | /follow/venue | src/Rest/FollowVenueCuratorController.php:17 | yes | no |
| artpulse/v1 | GET | /followed/venues | src/Rest/FollowVenueCuratorController.php:28 | yes | no |
| artpulse/v1 | POST | /follow/curator | src/Rest/FollowVenueCuratorController.php:36 | yes | no |
| artpulse/v1 | GET | /followed/curators | src/Rest/FollowVenueCuratorController.php:47 | yes | no |
| 'artpulse/v1' | WP_REST_Server::READABLE | /artist | src/Rest/ArtistOverviewController.php:21 | yes | no |
| artpulse/v1 | GET | /orgs/(?P<id>\d+)/roles | src/Rest/OrgUserRolesController.php:19 | yes | no |
| artpulse/v1 | DELETE | /orgs/(?P<id>\d+)/roles/(?P<user_id>\d+) | src/Rest/OrgUserRolesController.php:34 | yes | no |
| artpulse/v1 | GET | /users/me/orgs | src/Rest/OrgUserRolesController.php:42 | yes | no |
| artpulse/v1 | GET | /trending | src/Rest/TrendingRestController.php:14 | yes | no |
| artpulse/v1 | GET | /dashboard-widgets | src/Rest/DashboardWidgetController.php:96 | yes | yes |
| artpulse/v1 | POST | /dashboard-widgets/save | src/Rest/DashboardWidgetController.php:103 | yes | yes |
| artpulse/v1 | GET | /dashboard-widgets/export | src/Rest/DashboardWidgetController.php:110 | yes | yes |
| artpulse/v1 | POST | /dashboard-widgets/import | src/Rest/DashboardWidgetController.php:117 | yes | yes |
| artpulse/v1 | GET | /artwork/(?P<id>\d+)/auction | src/Rest/ArtworkAuctionController.php:18 | yes | no |
| artpulse/v1 | POST | /artwork/(?P<id>\d+)/bid | src/Rest/ArtworkAuctionController.php:31 | yes | no |
| artpulse/v1 | POST | /rsvp | src/Rest/RsvpRestController.php:22 | yes | no |
| artpulse/v1 | POST | /rsvp/cancel | src/Rest/RsvpRestController.php:38 | yes | no |
| artpulse/v1 | POST | /waitlist/remove | src/Rest/RsvpRestController.php:54 | yes | no |
| artpulse/v1 | GET | /event/(?P<id>\d+)/attendees | src/Rest/RsvpRestController.php:70 | yes | no |
| artpulse/v1 | GET | /event/(?P<id>\d+)/attendees/export | src/Rest/RsvpRestController.php:79 | yes | no |
| artpulse/v1 | POST | /event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/attended | src/Rest/RsvpRestController.php:88 | yes | no |
| artpulse/v1 | POST | /event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/remove | src/Rest/RsvpRestController.php:100 | yes | no |
| artpulse/v1 | POST | /event/(?P<event_id>\d+)/email-rsvps | src/Rest/RsvpRestController.php:112 | yes | no |
| artpulse/v1 | POST | /event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/message | src/Rest/RsvpRestController.php:123 | yes | no |
| artpulse/v1 | WP_REST_Server::READABLE | /events | src/Rest/DirectoryController.php:13 | yes | no |
| artpulse/v1 | GET | /activity | src/Rest/ActivityRestController.php:18 | yes | no |
| artpulse/v1 | WP_REST_Server::READABLE | /analytics/community/messaging | src/Rest/CommunityAnalyticsController.php:19 | yes | no |
| artpulse/v1 | WP_REST_Server::READABLE | /analytics/community/comments | src/Rest/CommunityAnalyticsController.php:31 | yes | no |
| artpulse/v1 | WP_REST_Server::READABLE | /analytics/community/forums | src/Rest/CommunityAnalyticsController.php:40 | yes | no |
| 'artpulse/v1' | GET | /event-card/(?P<id>\\d+) | src/Rest/EventCardController.php:20 | yes | yes |
| artpulse/v1 | POST | /org/(?P<id>\\d+)/invite | src/Rest/UserInvitationController.php:22 | yes | no |
| artpulse/v1 | POST | /org/(?P<id>\\d+)/users/batch | src/Rest/UserInvitationController.php:35 | yes | no |
| artpulse/v1 | WP_REST_Server::READABLE | /curators | src/Rest/CuratorRestController.php:19 | yes | no |
| artpulse/v1 | WP_REST_Server::READABLE | /curator/(?P<slug>[a-z0-9-]+) | src/Rest/CuratorRestController.php:29 | yes | no |
| artpulse/v1 | GET | /widgets | src/Rest/WidgetEditorController.php:21 | yes | no |
| artpulse/v1 | GET | /roles | src/Rest/WidgetEditorController.php:27 | yes | no |
| artpulse/v1 | ['GET | /layout | src/Rest/WidgetEditorController.php:33 | yes | no |
| artpulse/v1 | GET | /event-list | src/Rest/EventListController.php:21 | yes | no |
| 'artpulse/v1' | GET | /role-widget-map | src/Rest/RoleWidgetMapController.php:16 | yes | no |
| artpulse/v1 | POST | /feedback | src/Rest/FeedbackRestController.php:23 | yes | no |
| artpulse/v1 | POST | /feedback/(?P<id>\\d+)/vote | src/Rest/FeedbackRestController.php:37 | yes | no |
| artpulse/v1 | GET | /feedback/(?P<id>\\d+)/comments | src/Rest/FeedbackRestController.php:45 | yes | no |
| artpulse/v1 | GET | /widget-settings/(?P<id>[a-z0-9_-]+) | src/Rest/WidgetSettingsRestController.php:23 | yes | yes |
| artpulse/v1 | GET | /org-metrics | src/Rest/OrgAnalyticsController.php:14 | yes | no |
| artpulse/v1 | GET | /event/(?P<id>\d+)/rsvp-stats | src/Rest/OrgAnalyticsController.php:24 | yes | no |
| artpulse/v1 | GET | /portfolio/(?P<user_id>\d+) | src/Rest/PortfolioRestController.php:19 | yes | no |
| artpulse/v1 | GET | /report-template/(?P<type>[^/]+) | src/Rest/ReportTemplateController.php:23 | yes | no |
| artpulse/v1 | POST | /spotlight/view | src/Rest/SpotlightAnalyticsController.php:20 | yes | no |
| artpulse/v1 | GET | /calendar | src/Rest/CalendarFeedController.php:16 | yes | no |
| 'artpulse/v1' | WP_REST_Server::READABLE | /status | src/Rest/StatusController.php:21 | yes | no |
| artpulse/v1 | GET | /analytics/trends | src/Rest/AnalyticsRestController.php:19 | yes | no |
| artpulse/v1 | GET | /analytics/export | src/Rest/AnalyticsRestController.php:33 | yes | no |
| artpulse/v1 | GET | /dashboard/layout | src/Rest/DashboardLayoutRestController.php:25 | yes | no |
| artpulse/v1 | GET | /user/export | src/Rest/UserAccountRestController.php:24 | yes | no |
| artpulse/v1 | POST | /user/delete | src/Rest/UserAccountRestController.php:44 | yes | no |
| artpulse/v1 | GET | /dashboard-config | src/Rest/DashboardConfigController.php:18 | yes | yes |
| artpulse/v1 | GET | /org/(?P<id>\d+)/events/summary | src/Rest/OrgDashboardController.php:16 | yes | no |
| artpulse/v1 | POST | /org/(?P<id>\d+)/team/invite | src/Rest/OrgDashboardController.php:25 | yes | no |
| artpulse/v1 | GET | /org/(?P<id>\d+)/tickets/metrics | src/Rest/OrgDashboardController.php:34 | yes | no |
| artpulse/v1 | POST | /org/(?P<id>\d+)/message/broadcast | src/Rest/OrgDashboardController.php:43 | yes | no |
| artpulse/v1 | GET | /orgs | src/Rest/RestRoutes.php:12 | yes | no |
| artpulse/v1 | POST | /dashboard-analytics | src/Rest/DashboardAnalyticsController.php:19 | yes | yes |
| artpulse/v1 | POST | /org-roles/invite | src/Rest/OrgRoleInviteController.php:21 | yes | no |
| artpulse/v1 | POST | /org-roles/accept | src/Rest/OrgRoleInviteController.php:29 | yes | no |
| artpulse/v1 | POST | /share | src/Rest/ShareController.php:18 | yes | no |
| 'artpulse/v1' | WP_REST_Server::CREATABLE | /widget-layout | src/Rest/WidgetLayoutController.php:22 | yes | no |
| artpulse/v1 | GET | /profile-metrics/(?P<id>\d+) | src/Rest/ProfileMetricsController.php:18 | yes | no |
| artpulse/v1 | POST | /analytics/pilot/invite | src/Rest/AnalyticsPilotController.php:19 | yes | no |
| 'artpulse/v1' | WP_REST_Server::CREATABLE | /submissions | src/Rest/SubmissionRestController.php:47 | yes | yes |
| artpulse/v1 | GET | /me | src/Rest/CurrentUserController.php:11 | yes | no |
| artpulse/v1 | POST | /messages/(?P<id>\d+)/reply | src/Rest/MessagesController.php:16 | yes | no |
| artpulse/v1 | GET | /orgs/(?P<id>\\d+)/grant-report | src/Rest/GrantReportController.php:19 | yes | no |
| artpulse/v1 | POST | /competitions/(?P<id>\d+)/entries | src/Rest/CompetitionRestController.php:19 | yes | no |
| artpulse/v1 | POST | /competitions/(?P<id>\d+)/entries/(?P<entry_id>\d+)/vote | src/Rest/CompetitionRestController.php:31 | yes | no |
| artpulse/v1 | GET | /location/geonames | src/Rest/LocationRestController.php:12 | yes | no |
| artpulse/v1 | GET | /location/google | src/Rest/LocationRestController.php:19 | yes | no |
| artpulse/v1 | GET | /events/nearby | src/Rest/NearbyEventsController.php:25 | yes | no |
| artpulse/v1 | GET | /recommendations | src/Personalization/RecommendationRestController.php:18 | yes | no |
| artpulse/v1 | POST | /payment/webhook | src/Monetization/PaymentWebhookController.php:17 | yes | no |
| artpulse/v1 | POST | /boost/create-checkout | src/Monetization/EventBoostManager.php:47 | yes | no |
| artpulse/v1 | POST | /boost/webhook | src/Monetization/EventBoostManager.php:54 | yes | no |
| artpulse/v1 | GET | /event/(?P<id>\\d+)/tickets | src/Monetization/TicketManager.php:25 | yes | no |
| artpulse/v1 | POST | /event/(?P<id>\\d+)/buy-ticket | src/Monetization/TicketManager.php:34 | yes | no |
| artpulse/v1 | POST | /event/(?P<id>\\d+)/ticket-tier | src/Monetization/TicketManager.php:43 | yes | no |
| artpulse/v1 | PUT | /ticket-tier/(?P<tier_id>\\d+) | src/Monetization/TicketManager.php:51 | yes | no |
| artpulse/v1 | POST | /donations | src/Monetization/DonationManager.php:18 | yes | no |
| artpulse/v1 | ['GET | /user/membership | src/Monetization/MembershipManager.php:16 | yes | no |
| artpulse/v1 | GET | /user/sales | src/Monetization/SalesOverview.php:20 | yes | no |
| artpulse/v1 | GET | /user/payouts | src/Monetization/PayoutManager.php:21 | yes | no |
| artpulse/v1 | POST | /user/payouts/settings | src/Monetization/PayoutManager.php:28 | yes | no |
| artpulse/v1 | POST | /event/(?P<id>\\d+)/promo-code/apply | src/Monetization/PromoManager.php:17 | yes | no |
| artpulse/v1 | POST | /event/(?P<id>\\d+)/feature | src/Monetization/EventPromotionManager.php:19 | yes | no |
| artpulse/v1 | POST | /artist/(?P<id>\\d+)/tip | src/Monetization/TipManager.php:21 | yes | no |
| artpulse/v1 | GET | /reporting/snapshot | src/Reporting/SnapshotExportController.php:26 | yes | no |
| artpulse/v1 | GET | /reporting/snapshot.csv | src/Reporting/SnapshotExportController.php:34 | yes | no |
| artpulse/v1 | GET | /reporting/snapshot.pdf | src/Reporting/SnapshotExportController.php:42 | yes | no |
| artpulse/v1 | GET | /orgs/(?P<id>\d+)/report | src/Reporting/OrgReportController.php:22 | yes | no |
| artpulse/v1 | GET | /budget/export | src/Reporting/BudgetExportController.php:25 | yes | no |
| artpulse/v1 | GET | /event/(?P<id>\\d+)/comments | src/Community/EventCommentsController.php:21 | yes | no |
| artpulse/v1 | POST | /event/comment/(?P<comment_id>\\d+)/moderate | src/Community/EventCommentsController.php:41 | yes | no |
| artpulse/v1 | GET | /artwork/(?P<id>\\d+)/comments | src/Community/ArtworkCommentsController.php:18 | yes | no |
| artpulse/v1 | GET | /qa-thread/(?P<event_id>\d+) | src/Community/QaThreadRestController.php:15 | yes | no |
| artpulse/v1 | POST | /qa-thread/(?P<event_id>\d+)/post | src/Community/QaThreadRestController.php:24 | yes | no |
| artpulse/v1 | WP_REST_Server::READABLE | /forum/threads | src/Community/ForumRestController.php:20 | yes | no |
| artpulse/v1 | WP_REST_Server::READABLE | /forum/thread/(?P<id>\\d+)/comments | src/Community/ForumRestController.php:39 | yes | no |
| artpulse/v1 | ['POST | /follows | src/Community/FollowRestController.php:21 | yes | no |
| artpulse/v1 | GET | /followers/(?P<user_id>\\d+) | src/Community/FollowRestController.php:28 | yes | no |
| artpulse/v1 | POST | /referral/redeem | src/Community/ReferralManager.php:44 | yes | no |
| artpulse/v1 | POST | /artist-upgrade | src/Community/ArtistUpgradeRestController.php:21 | yes | no |
| artpulse/v1 | DELETE | /favorites | src/Community/FavoritesRestController.php:20 | yes | no |
| artpulse/v1 | POST | /comment/(?P<id>\\d+)/report | src/Community/CommentReportRestController.php:18 | yes | no |
| artpulse/v1 | GET | /leaderboards/most-helpful | src/Community/LeaderboardRestController.php:17 | yes | no |
| artpulse/v1 | GET | /leaderboards/most-upvoted | src/Community/LeaderboardRestController.php:25 | yes | no |
| artpulse/v1 | POST | /event/(?P<id>\d+)/vote | src/Community/EventVoteRestController.php:15 | yes | no |
| artpulse/v1 | GET | /event/(?P<id>\d+)/votes | src/Community/EventVoteRestController.php:23 | yes | no |
| artpulse/v1 | GET | /leaderboards/top-events | src/Community/EventVoteRestController.php:33 | yes | no |
| artpulse/v1 | GET | /inbox | src/Community/UnifiedInboxController.php:22 | yes | no |
| artpulse/v1 | POST | /inbox/read | src/Community/UnifiedInboxController.php:33 | yes | no |
| artpulse/v1 | POST | /inbox/unread | src/Community/UnifiedInboxController.php:46 | yes | no |
| artpulse/v1 | POST | /messages/send | src/Community/DirectMessages.php:115 | yes | yes |
| artpulse/v1 | POST | /messages | src/Community/DirectMessages.php:132 | yes | yes |
| artpulse/v1 | GET | /messages/updates | src/Community/DirectMessages.php:159 | yes | yes |
| artpulse/v1 | POST | /messages/seen | src/Community/DirectMessages.php:171 | yes | yes |
| artpulse/v1 | GET | /messages/search | src/Community/DirectMessages.php:182 | yes | yes |
| artpulse/v1 | GET | /messages/context/(?P<type>[a-zA-Z0-9_-]+)/(?P<id>\d+) | src/Community/DirectMessages.php:193 | yes | yes |
| artpulse/v1 | POST | /messages/block | src/Community/DirectMessages.php:201 | yes | yes |
| artpulse/v1 | POST | /messages/label | src/Community/DirectMessages.php:212 | yes | yes |
| artpulse/v1 | GET | /messages/thread | src/Community/DirectMessages.php:225 | yes | yes |
| artpulse/v1 | POST | /messages/bulk | src/Community/DirectMessages.php:236 | yes | yes |
| artpulse/v1 | GET | /conversations | src/Community/DirectMessages.php:249 | yes | yes |
| artpulse/v1 | POST | /message/read | src/Community/DirectMessages.php:259 | yes | yes |
| artpulse/v1 | POST | /link-request | src/Community/ProfileLinkRequestRestController.php:13 | yes | no |
| artpulse/v1 | POST | /link-request/(?P<id>\d+)/approve | src/Community/ProfileLinkRequestRestController.php:25 | yes | no |
| artpulse/v1 | POST | /link-request/(?P<id>\d+)/deny | src/Community/ProfileLinkRequestRestController.php:33 | yes | no |
| artpulse/v1 | GET | /link-requests | src/Community/ProfileLinkRequestRestController.php:41 | yes | no |
| artpulse/v1 | POST | /link-requests/bulk | src/Community/ProfileLinkRequestRestController.php:63 | yes | no |
| artpulse/v1 | POST | /user-preferences | src/Community/UserPreferencesRestController.php:73 | yes | no |
| artpulse/v1 | GET | /notifications | src/Community/NotificationRestController.php:20 | yes | no |
| artpulse/v1 | POST | /notifications/(?P<id>\\d+)/read | src/Community/NotificationRestController.php:28 | yes | no |
| artpulse/v1 | POST | /notifications/mark-all-read | src/Community/NotificationRestController.php:36 | yes | no |
| artpulse/v1 | GET | /filtered-posts | src/Blocks/AjaxFilterBlock.php:35 | yes | no |
| artpulse/v1 | ['GET | /event/(?P<id>\\d+)/survey | src/Admin/SurveyManager.php:17 | yes | no |
| artpulse/v1 | ['GET | /dashboard-layout/(?P<context>\\w+) | src/Admin/DashboardLayoutEndpoint.php:14 | yes | no |
| artpulse/v1 | GET | /admin/users | src/Admin/SegmentationManager.php:17 | yes | no |
| artpulse/v1 | GET | /event/(?P<id>\d+)/notes | src/Admin/EventNotesTasks.php:208 | no | no |
| artpulse/v1 | GET | /event/(?P<id>\d+)/tasks | src/Admin/EventNotesTasks.php:227 | no | no |
| artpulse/v1 | ['GET | /admin/reminders | src/Admin/ReminderManager.php:18 | yes | no |
| artpulse/v1 | ['GET | /event/(?P<id>\\d+)/rsvp/custom-fields | src/Admin/CustomFieldsManager.php:23 | yes | no |
| artpulse/v1 | GET | /admin/export | src/Admin/ReportingManager.php:23 | yes | no |
| artpulse/v1 | GET | /filter | src/Core/DirectoryManager.php:47 | yes | no |
| artpulse/v1 | GET | /org/dashboard | src/Core/OrgDashboardManager.php:18 | yes | no |
| artpulse/v1 | POST | /stripe-webhook | src/Core/MembershipManager.php:142 | yes | no |
| artpulse/v1 | POST | /membership/pause | src/Core/MembershipManager.php:153 | yes | no |
| artpulse/v1 | POST | /membership/resume | src/Core/MembershipManager.php:164 | yes | no |
| artpulse/v1 | GET | /membership/levels | src/Core/MembershipManager.php:175 | yes | no |
| artpulse/v1 | DELETE | /membership/levels/(?P<level>[\\w-]+) | src/Core/MembershipManager.php:188 | yes | no |
| artpulse/v1 | GET | /member/account | src/Core/FrontendMembershipPage.php:17 | yes | no |
| artpulse/v1 | GET | /user/dashboard | src/Core/UserDashboardManager.php:193 | yes | no |
| artpulse/v1 | POST | /user/profile | src/Core/UserDashboardManager.php:206 | yes | no |
| artpulse/v1 | GET | /user/engagement | src/Core/UserDashboardManager.php:219 | yes | no |
| artpulse/v1 | POST | /user/onboarding | src/Core/UserDashboardManager.php:236 | yes | no |
| artpulse/v1 | POST | /dashboard-tour | src/Core/UserDashboardManager.php:252 | yes | no |
| artpulse/v1 | GET | /ap/widgets/available | src/Core/UserDashboardManager.php:265 | yes | no |
| artpulse/v1 | POST | /ap/layout/reset | src/Core/UserDashboardManager.php:305 | yes | no |
| artpulse/v1 | POST | /ap/layout/revert | src/Core/UserDashboardManager.php:315 | yes | no |
| artpulse/v1 | GET | /ap_dashboard_layout | src/Core/UserDashboardManager.php:325 | yes | no |
| artpulse/v1 | POST | /ap_dashboard_layout | src/Core/UserDashboardManager.php:326 | yes | no |
| artpulse/v1 | POST | /newsletter-optin | src/Frontend/NewsletterOptinEndpoint.php:18 | yes | no |
| artpulse/v1 | GET | /widgets/embed.js | src/Frontend/WidgetsController.php:16 | yes | no |
| artpulse/v1 | GET | /widgets/render | src/Frontend/WidgetsController.php:21 | yes | no |
| artpulse/v1 | POST | /bids | src/Marketplace/AuctionManager.php:40 | yes | no |
| artpulse/v1 | GET | /bids/(?P<artwork_id>\\d+) | src/Marketplace/AuctionManager.php:48 | yes | no |
| artpulse/v1 | GET | /auctions/live | src/Marketplace/AuctionManager.php:61 | yes | no |
| artpulse/v1 | GET | /promoted | src/Marketplace/PromotionManager.php:34 | yes | no |
| artpulse/v1 | POST | /promote | src/Marketplace/PromotionManager.php:44 | yes | no |
| artpulse/v1 | POST | /artworks | src/Marketplace/MarketplaceManager.php:85 | yes | no |
| artpulse/v1 | GET | /artworks/(?P<id>\\d+) | src/Marketplace/MarketplaceManager.php:101 | yes | no |
| artpulse/v1 | POST | /orders | src/Marketplace/MarketplaceManager.php:111 | yes | no |
| artpulse/v1 | GET | /orders/mine | src/Marketplace/MarketplaceManager.php:118 | yes | no |
| artpulse/v1 | POST | /ai/generate-grant-copy | src/AI/GrantAssistant.php:17 | yes | no |
| artpulse/v1 | POST | /ai/rewrite | src/AI/GrantAssistant.php:25 | yes | no |
| artpulse/v1 | POST | /bio-summary | src/AI/BioSummaryRestController.php:28 | yes | no |
| artpulse/v1 | POST | /tag | src/AI/AutoTaggerRestController.php:28 | yes | no |

## AJAX Actions

| Action | nopriv | File:Line | Nonce | Capabilities |
|--------|--------|-----------|-------|--------------|
| ap_ajax_test | False | artpulse.php:187 | yes | administrator |
| submit_react_form | False | artpulse-management.php:1256 | yes | manage_options |
| submit_react_form | True | artpulse-management.php:1257 | yes | manage_options |
| ap_apply_preset | False | artpulse-management.php:1260 | yes | manage_options |
| ap_reset_layout | False | artpulse-management.php:1276 | yes | manage_options |
| ap_follow_post | False | includes/user-actions.php:8 | yes |  |
| ap_follow_post | True | includes/user-actions.php:9 | yes |  |
| ap_unfollow_post | False | includes/user-actions.php:10 | yes |  |
| ap_unfollow_post | True | includes/user-actions.php:11 | yes |  |
| ap_follow_toggle | False | includes/user-actions.php:13 | yes |  |
| ap_follow_toggle | True | includes/user-actions.php:14 | yes |  |
| ap_save_dashboard_widget_config | False | includes/dashboard-widgets.php:129 | yes | manage_options,read |
| ap_save_widget_layout | False | includes/dashboard-widgets.php:131 | yes | manage_options,read |
| ap_save_role_layout | False | includes/dashboard-widgets.php:132 | yes | manage_options,read |
| ap_save_user_layout | False | includes/dashboard-widgets.php:133 | yes | manage_options,read |
| save_widget_order | False | includes/dashboard-widgets.php:134 | yes | manage_options,read |
| ap_save_dashboard_order | False | includes/dashboard-widgets.php:135 | yes | manage_options,read |
| ap_render_widget | False | includes/dashboard-widgets.php:136 | yes | manage_options,read |
| save_dashboard_layout | False | includes/dashboard-widgets.php:137 | yes | manage_options,read |
| save_dashboard_layout | False | src/Rest/LayoutSaveEndpoint.php:8 | yes |  |
| ap_filter_posts | False | src/Ajax/FrontendFilterHandler.php:8 | yes |  |
| ap_filter_posts | True | src/Ajax/FrontendFilterHandler.php:9 | yes |  |
| ap_search_posts | False | src/Admin/MetaBoxesRelationship.php:68 | yes | edit_page,edit_post |
| ap_search_collection_items | False | src/Admin/MetaBoxesCollection.php:9 | yes | edit_post |
| ap_save_event_gallery_order | False | src/Admin/AdminColumnsEvent.php:11 | yes | edit_post |
| ap_dismiss_release_notes | False | src/Admin/ReleaseNotes.php:14 | yes | manage_options |
| ap_dashboard_feedback | False | src/Core/DashboardFeedbackManager.php:11 | yes |  |
| ap_submit_feedback | False | src/Core/FeedbackManager.php:9 | yes |  |
| ap_submit_feedback | True | src/Core/FeedbackManager.php:10 | yes |  |
| ap_save_user_layout | False | src/Core/DashboardWidgetManager.php:22 | yes |  |
| ap_save_role_layout | False | src/Core/DashboardWidgetManager.php:23 | yes |  |
| update_profile_field | False | src/Frontend/ProfileEditShortcode.php:11 | yes |  |
| ap_delete_artwork | False | src/Frontend/ArtistDashboardShortcode.php:7 | yes | delete_post,edit_post |
| save_artwork_order | False | src/Frontend/ArtistDashboardShortcode.php:8 | yes | delete_post,edit_post |
| ap_do_login | False | src/Frontend/LoginShortcode.php:17 | yes |  |
| ap_do_login | True | src/Frontend/LoginShortcode.php:18 | yes |  |
| ap_do_register | False | src/Frontend/LoginShortcode.php:19 | yes |  |
| ap_do_register | True | src/Frontend/LoginShortcode.php:20 | yes |  |
| ap_save_portfolio | False | src/Frontend/PortfolioBuilder.php:11 | yes |  |
| ap_get_portfolio_item | False | src/Frontend/PortfolioBuilder.php:12 | yes |  |
| ap_toggle_visibility | False | src/Frontend/PortfolioBuilder.php:13 | yes |  |
| ap_delete_portfolio_item | False | src/Frontend/PortfolioBuilder.php:14 | yes |  |
| ap_save_portfolio_order | False | src/Frontend/PortfolioBuilder.php:15 | yes |  |
| ap_filter_events | False | src/Frontend/EventFilter.php:13 | yes |  |
| ap_filter_events | True | src/Frontend/EventFilter.php:14 | yes |  |
| ap_save_event | False | src/Frontend/EditEventShortcode.php:10 | yes | delete_post,edit_post,view_artpulse_dashboard |
| ap_delete_event | False | src/Frontend/EditEventShortcode.php:11 | yes | delete_post,edit_post,view_artpulse_dashboard |
| ap_add_org_event | False | src/Frontend/OrganizationDashboardShortcode.php:9 | yes | administrator,create_artpulse_events,delete_post,edit_post |
| ap_get_org_event | False | src/Frontend/OrganizationDashboardShortcode.php:10 | yes | administrator,create_artpulse_events,delete_post,edit_post |
| ap_update_org_event | False | src/Frontend/OrganizationDashboardShortcode.php:11 | yes | administrator,create_artpulse_events,delete_post,edit_post |
| ap_delete_org_event | False | src/Frontend/OrganizationDashboardShortcode.php:12 | yes | administrator,create_artpulse_events,delete_post,edit_post |