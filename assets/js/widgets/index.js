import { NearbyEventsMapWidget } from './NearbyEventsMapWidget.jsx';
import { MyFavoritesWidget } from './MyFavoritesWidget.jsx';

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
  }
];
