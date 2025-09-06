import React from 'react';

export interface EventItem {
  id: string;
  title: string;
  startsAt: string;
}

export default function UpcomingEvents({ events = [] }: { events: EventItem[] }) {
  if (events.length === 0) {
    return <p>No upcoming events</p>;
  }
  return (
    <ul className="list-disc pl-4 space-y-1">
      {events.map(evt => (
        <li key={evt.id}>{evt.title}</li>
      ))}
    </ul>
  );
}

