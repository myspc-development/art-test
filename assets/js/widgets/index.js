import { NearbyEventsMapWidget } from './NearbyEventsMapWidget.jsx';
import { MyFavoritesWidget } from './MyFavoritesWidget.jsx';
import { RSVPButton } from './RSVPButton.jsx';
import { EventChatWidget } from './EventChatWidget.jsx';
import { ShareThisEventWidget } from './ShareThisEventWidget.jsx';
import { ArtistInboxPreviewWidget } from './ArtistInboxPreviewWidget.jsx';
import { ArtistRevenueSummaryWidget } from './ArtistRevenueSummaryWidget.jsx';
import { ArtistSpotlightWidget } from './ArtistSpotlightWidget.jsx';
import { AudienceCRMWidget } from './AudienceCRMWidget.jsx';
import { SponsoredEventConfigWidget } from './SponsoredEventConfigWidget.jsx';
import { EmbedToolWidget } from './EmbedToolWidget.jsx';
import { OrgBrandingSettingsPanel } from './OrgBrandingSettingsPanel.jsx';
const { __ } = wp.i18n;

export default [
  {
    id: 'nearby_events_map',
    title: __('Nearby Events Map', 'artpulse'),
    component: NearbyEventsMapWidget,
    roles: ['member', 'artist']
  },
  {
    id: 'my_favorites',
    title: __('My Favorites', 'artpulse'),
    component: MyFavoritesWidget,
    roles: ['member', 'artist']
  },
  {
    id: 'rsvp_button',
    title: __('RSVP Button', 'artpulse'),
    component: RSVPButton,
    roles: ['member', 'artist']
  },
  {
    id: 'event_chat',
    title: __('Event Chat', 'artpulse'),
    component: EventChatWidget,
    roles: ['member', 'artist']
  },
  {
    id: 'share_this_event',
    title: __('Share This Event', 'artpulse'),
    component: ShareThisEventWidget,
    roles: ['member', 'artist']
  },
  {
    id: 'artist_inbox_preview',
    title: __('Artist Inbox Preview', 'artpulse'),
    component: ArtistInboxPreviewWidget,
    roles: ['artist']
  },
  {
    id: 'artist_revenue_summary',
    title: __('Revenue Summary', 'artpulse'),
    component: ArtistRevenueSummaryWidget,
    roles: ['artist']
  },
  {
    id: 'artist_spotlight',
    title: __('Artist Spotlight', 'artpulse'),
    component: ArtistSpotlightWidget,
    roles: ['artist']
  },
  {
    id: 'audience_crm',
    title: __('Audience CRM', 'artpulse'),
    component: AudienceCRMWidget,
    roles: ['organization']
  },
  {
    id: 'sponsored_event_config',
    title: __('Sponsored Event Config', 'artpulse'),
    component: SponsoredEventConfigWidget,
    roles: ['organization']
  },
  {
    id: 'embed_tool',
    title: __('Embed Tool', 'artpulse'),
    component: EmbedToolWidget,
    roles: ['organization']
  },
  {
    id: 'branding_settings_panel',
    title: __('Branding Settings', 'artpulse'),
    component: OrgBrandingSettingsPanel,
    roles: ['organization']
  }
];
