import React from 'react';
import { createRoot } from 'react-dom/client';
import RsvpButton from './widgets/RsvpButtonWidget.jsx';
import EventChat from './widgets/EventChatWidget.jsx';

const mountPoints = document.querySelectorAll('[data-widget]');

mountPoints.forEach(node => {
  const widget = node.dataset.widget;
  const props = JSON.parse(node.dataset.props || '{}');
  let Component = null;

  switch (widget) {
    case 'rsvp_button':
      Component = RsvpButton;
      break;
    case 'event_chat':
      Component = EventChat;
      break;
    default:
      console.warn(`Unknown widget: ${widget}`);
      return;
  }

  const root = createRoot(node);
  root.render(<Component {...props} />);
});

