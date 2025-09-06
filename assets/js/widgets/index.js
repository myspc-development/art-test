import { NearbyEventsMapWidget } from './NearbyEventsMapWidget.jsx';
import { MyFavoritesWidget } from './MyFavoritesWidget.jsx';
import RsvpButtonWidget from './RsvpButtonWidget.jsx';
import EventChatWidget from './EventChatWidget.jsx';
import ShareThisEventWidget from './ShareThisEventWidget.jsx';
import { ArtistInboxPreviewWidget } from './ArtistInboxPreviewWidget.jsx';
import { ActivityFeedWidget } from './ActivityFeedWidget.jsx';
import { MyUpcomingEventsWidget } from './MyUpcomingEventsWidget.jsx';
import { ArtPulseNewsFeedWidget } from './ArtPulseNewsFeedWidget.jsx';
import { QAChecklistWidget } from './QAChecklistWidget.jsx';
import { EventsWidget } from './EventsWidget.jsx';
import { WelcomeBoxWidget } from './WelcomeBoxWidget.jsx';
import { WidgetEventsWidget } from './WidgetEventsWidget.jsx';
import { FavoritesOverviewWidget } from './FavoritesOverviewWidget.jsx';
import { ArtistRevenueSummaryWidget } from './ArtistRevenueSummaryWidget.jsx';
import { ArtistSpotlightWidget } from './ArtistSpotlightWidget.jsx';
import { ArtistArtworkManagerWidget } from './ArtistArtworkManagerWidget.jsx';
import { ArtistAudienceInsightsWidget } from './ArtistAudienceInsightsWidget.jsx';
import { ArtistEarningsWidget } from './ArtistEarningsWidget.jsx';
import { ArtistFeedPublisherWidget } from './ArtistFeedPublisherWidget.jsx';
import { ArtistCollaborationWidget } from './ArtistCollaborationWidget.jsx';
import { OnboardingTrackerWidget } from './OnboardingTrackerWidget.jsx';
import { AudienceCRMWidget } from './AudienceCRMWidget.jsx';
import { SponsoredEventConfigWidget } from './SponsoredEventConfigWidget.jsx';
import { EmbedToolWidget } from './EmbedToolWidget.jsx';
import { OrgBrandingSettingsPanel } from './OrgBrandingSettingsPanel.jsx';
import { OrgEventOverviewWidget } from './OrgEventOverviewWidget.jsx';
import { OrgTeamRosterWidget } from './OrgTeamRosterWidget.jsx';
import { OrgApprovalCenterWidget } from './OrgApprovalCenterWidget.jsx';
import { OrgTicketInsightsWidget } from './OrgTicketInsightsWidget.jsx';
import { OrgBroadcastBoxWidget } from './OrgBroadcastBoxWidget.jsx';
const { __ } = wp.i18n;

const widgets = [
	{
		id: 'nearby_events_map',
		title: __( 'Nearby Events Map', 'artpulse' ),
		component: NearbyEventsMapWidget,
		roles: ['member', 'artist']
	},
	{
		id: 'my_favorites',
		title: __( 'My Favorites', 'artpulse' ),
		component: MyFavoritesWidget,
		roles: ['member', 'artist']
	},
	{
		id: 'my_upcoming_events',
		title: __( 'My Upcoming Events', 'artpulse' ),
		component: MyUpcomingEventsWidget,
		roles: ['member', 'artist']
	},
	{
		id: 'news_feed',
		title: __( 'News Feed', 'artpulse' ),
		component: ArtPulseNewsFeedWidget,
		roles: ['member']
	},
	{
		id: 'rsvp_button',
		title: __( 'RSVP Button', 'artpulse' ),
		component: RsvpButtonWidget,
		roles: ['member']
	},
	{
		id: 'event_chat',
		title: __( 'Event Chat', 'artpulse' ),
		component: EventChatWidget,
		roles: ['member']
	},
	{
		id: 'share_this_event',
		title: __( 'Share This Event', 'artpulse' ),
		component: ShareThisEventWidget,
		roles: ['member']
	},
	{
		id: 'sample_events',
		title: __( 'Sample Events', 'artpulse' ),
		component: EventsWidget,
		roles: ['member', 'artist', 'organization']
	},
	{
		id: 'welcome_box',
		title: __( 'Welcome Box', 'artpulse' ),
		component: WelcomeBoxWidget,
		roles: ['member']
	},
	{
		id: 'widget_events',
		title: __( 'Upcoming Events', 'artpulse' ),
		component: WidgetEventsWidget,
		roles: ['member', 'organization']
	},
	{
		id: 'widget_favorites',
		title: __( 'Favorites Overview', 'artpulse' ),
		component: FavoritesOverviewWidget,
		roles: ['member']
	},
	{
		id: 'artist_inbox_preview',
		title: __( 'Artist Inbox Preview', 'artpulse' ),
		component: ArtistInboxPreviewWidget,
		roles: ['member', 'artist']
	},
	{
		id: 'artist_revenue_summary',
		title: __( 'Revenue Summary', 'artpulse' ),
		component: ArtistRevenueSummaryWidget,
		roles: ['artist']
	},
	{
		id: 'artist_spotlight',
		title: __( 'Artist Spotlight', 'artpulse' ),
		component: ArtistSpotlightWidget,
		roles: ['artist']
	},
	{
		id: 'artist_artwork_manager',
		title: __( 'Artwork Manager', 'artpulse' ),
		component: ArtistArtworkManagerWidget,
		roles: ['artist'],
		default: true
			},
			{
				id: 'artist_audience_insights',
				title: __( 'Audience Insights', 'artpulse' ),
				component: ArtistAudienceInsightsWidget,
				roles: ['artist']
			},
			{
				id: 'artist_earnings_summary',
				title: __( 'Earnings Summary', 'artpulse' ),
				component: ArtistEarningsWidget,
				roles: ['artist']
			},
			{
				id: 'artist_feed_publisher',
				title: __( 'Post & Engage', 'artpulse' ),
				component: ArtistFeedPublisherWidget,
				roles: ['artist']
			},
			{
				id: 'collab_requests',
				title: __( 'Collab Requests', 'artpulse' ),
				component: ArtistCollaborationWidget,
				roles: ['artist']
			},
			{
				id: 'onboarding_tracker',
				title: __( 'Onboarding Checklist', 'artpulse' ),
				component: OnboardingTrackerWidget,
				roles: ['artist']
			},
			{
				id: 'audience_crm',
				title: __( 'Audience CRM', 'artpulse' ),
				component: AudienceCRMWidget,
				roles: ['organization']
			},
			{
				id: 'sponsored_event_config',
				title: __( 'Sponsored Event Config', 'artpulse' ),
				component: SponsoredEventConfigWidget,
				roles: ['organization']
			},
			{
				id: 'embed_tool',
				title: __( 'Embed Tool', 'artpulse' ),
				component: EmbedToolWidget,
				roles: ['organization']
			},
			{
				id: 'branding_settings_panel',
				title: __( 'Branding Settings', 'artpulse' ),
				component: OrgBrandingSettingsPanel,
				roles: ['organization']
			},
			{
				id: 'org_event_overview',
				title: __( 'Event Overview', 'artpulse' ),
				component: OrgEventOverviewWidget,
				roles: ['organization']
			},
			{
				id: 'org_team_roster',
				title: __( 'Team Management', 'artpulse' ),
				component: OrgTeamRosterWidget,
				roles: ['organization']
			},
			{
				id: 'org_approval_center',
				title: __( 'Approval Center', 'artpulse' ),
				component: OrgApprovalCenterWidget,
				roles: ['organization']
			},
			{
				id: 'org_ticket_insights',
				title: __( 'Ticket Analytics', 'artpulse' ),
				component: OrgTicketInsightsWidget,
				roles: ['organization']
			},
			{
				id: 'org_broadcast_box',
				title: __( 'Announcement Tool', 'artpulse' ),
				component: OrgBroadcastBoxWidget,
				roles: ['organization']
			}
			];

			if ( window.AP_DEV_MODE ) {
						widgets.push(
							{
								id: 'activity_feed',
								title: __( 'Activity Feed', 'artpulse' ),
								component: ActivityFeedWidget,
								roles: ['member', 'artist', 'organization']
							}
						);
									widgets.push(
										{
											id: 'qa_checklist',
											title: __( 'QA Checklist', 'artpulse' ),
											component: QAChecklistWidget,
											roles: ['member']
										}
									);
			}

			export default widgets;
