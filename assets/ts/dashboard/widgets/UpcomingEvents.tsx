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
  // istanbul ignore next
  return events.length === 0 ? (
    <p>No upcoming events</p>
  ) : (
    <ul className="list-disc pl-4 space-y-1">{items}</ul>
  );
}
