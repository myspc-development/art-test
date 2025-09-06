/* istanbul ignore file */
import React from 'react';

export interface EventItem {
  id: string;
  title: string;
  startsAt: string;
}

export interface UpcomingEventsProps {
  events?: EventItem[];
}

export default function UpcomingEvents({ events = [] }: UpcomingEventsProps) {
  const items = events.map(evt => <li key={evt.id}>{evt.title}</li>);
  if (events.length === 0) {
    return <p>No upcoming events</p>;
  } else {
    return <ul className="list-disc pl-4 space-y-1">{items}</ul>;
  }
}
