import React from 'react';
import { createRoot } from 'react-dom/client';
import ChatWidget from './components/ChatWidget.jsx';
import QaWidget from './components/QaWidget.jsx';
import TicketWidget from './components/TicketWidget.jsx';

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
});
