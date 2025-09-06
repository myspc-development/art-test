import React from 'react';
import { render, screen } from '@testing-library/react';
import UpcomingEvents, { EventItem } from '../UpcomingEvents';

const EVTS: EventItem[] = [
  { id: 'e1', title: 'Opening Night', startsAt: new Date().toISOString() },
  { id: 'e2', title: 'Members Meetup', startsAt: new Date(Date.now() + 86400000).toISOString() },
];

test('lists upcoming events when present', () => {
  render(<UpcomingEvents events={EVTS} />);
  expect(screen.getByText(/opening night/i)).toBeInTheDocument();
  expect(screen.getByText(/members meetup/i)).toBeInTheDocument();
});

test('shows empty state with no events', () => {
  render(<UpcomingEvents events={[]} />);
  const empty = screen.queryByText(/no upcoming events/i) ?? screen.getByText(/no data|empty/i);
  expect(empty).toBeInTheDocument();
});

test('uses empty state when events prop omitted', () => {
  render(<UpcomingEvents />);
  expect(screen.getByText(/no upcoming events/i)).toBeInTheDocument();
});
