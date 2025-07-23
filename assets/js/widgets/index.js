import { NearbyEventsMapWidget } from './NearbyEventsMapWidget.jsx';
import { MyFavoritesWidget } from './MyFavoritesWidget.jsx';
import { RSVPButton } from './RSVPButton.jsx';
import { EventChatWidget } from './EventChatWidget.jsx';
import { ShareThisEventWidget } from './ShareThisEventWidget.jsx';
import { ArtistInboxPreviewWidget } from './ArtistInboxPreviewWidget.jsx';
import { ArtistRevenueSummaryWidget } from './ArtistRevenueSummaryWidget.jsx';
import { ArtistSpotlightWidget } from './ArtistSpotlightWidget.jsx';

export default [
  {
    id: 'nearby_events_map',
    title: 'Nearby Events Map',
    component: NearbyEventsMapWidget,
    roles: ['member', 'artist']
  },
  {
    id: 'my_favorites',
    title: 'My Favorites',
    component: MyFavoritesWidget,
    roles: ['member', 'artist']
  },
  {
    id: 'rsvp_button',
    title: 'RSVP Button',
    component: RSVPButton,
    roles: ['member', 'artist']
  },
  {
    id: 'event_chat',
    title: 'Event Chat',
    component: EventChatWidget,
    roles: ['member', 'artist']
  },
  {
    id: 'share_this_event',
    title: 'Share This Event',
    component: ShareThisEventWidget,
    roles: ['member', 'artist']
  },
  {
    id: 'artist_inbox_preview_widget',
    title: 'Artist Inbox Preview',
    component: ArtistInboxPreviewWidget,
    roles: ['artist']
  },
  {
    id: 'artist_revenue_summary',
    title: 'Revenue Summary',
    component: ArtistRevenueSummaryWidget,
    roles: ['artist']
  },
  {
    id: 'artist_spotlight',
    title: 'Artist Spotlight',
    component: ArtistSpotlightWidget,
    roles: ['artist']
  }
];
