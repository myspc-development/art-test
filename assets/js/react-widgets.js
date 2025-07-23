import React from 'react';
import { createRoot } from 'react-dom/client';
import ChatWidget from './components/ChatWidget.jsx';
import QaWidget from './components/QaWidget.jsx';
import TicketWidget from './components/TicketWidget.jsx';
import initNearbyEventsMapWidget from './widgets/NearbyEventsMapWidget.jsx';
import initMyFavoritesWidget from './widgets/MyFavoritesWidget.jsx';
import initArtistInboxPreviewWidget from './widgets/ArtistInboxPreviewWidget.jsx';
import initArtistRevenueSummaryWidget from './widgets/ArtistRevenueSummaryWidget.jsx';
import initArtistSpotlightWidget from './widgets/ArtistSpotlightWidget.jsx';

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.ap-event-chat[data-event-id]').forEach(el => {
    const root = createRoot(el);
    const canPost = !!el.dataset.canPost;
    root.render(<ChatWidget eventId={el.dataset.eventId} canPost={canPost} />);
  });

  document.querySelectorAll('.ap-qa-thread[data-event-id]').forEach(el => {
    const root = createRoot(el);
    const canPost = !!el.dataset.canPost;
    root.render(<QaWidget eventId={el.dataset.eventId} canPost={canPost} />);
  });

  document.querySelectorAll('.ap-tickets[data-event-id]').forEach(el => {
    const root = createRoot(el);
    root.render(<TicketWidget eventId={el.dataset.eventId} />);
  });

  document.querySelectorAll('.ap-nearby-events-widget[data-api-root]').forEach(el => {
    initNearbyEventsMapWidget(el);
  });

  document.querySelectorAll('.ap-favorites-widget[data-api-root]').forEach(el => {
    initMyFavoritesWidget(el);
  });

  document.querySelectorAll('.ap-artist-inbox-preview[data-api-root]').forEach(el => {
    initArtistInboxPreviewWidget(el);
  });

  document.querySelectorAll('.ap-revenue-summary-widget[data-api-root]').forEach(el => {
    initArtistRevenueSummaryWidget(el);
  });

  document.querySelectorAll('.ap-artist-spotlight[data-api-root]').forEach(el => {
    initArtistSpotlightWidget(el);
  });
});
