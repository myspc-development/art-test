import { NearbyEventsMapWidget } from './NearbyEventsMapWidget.jsx';
import { MyFavoritesWidget } from './MyFavoritesWidget.jsx';
import { RSVPButton } from './RSVPButton.jsx';
import { EventChatWidget } from './EventChatWidget.jsx';
import { ShareThisEventWidget } from './ShareThisEventWidget.jsx';

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
  }
];
