import React from 'react';

export default function FlaggedActivityLog({ items = [] }) {
  return (
    <ul className="space-y-1 text-sm">
      {items.map(i => (
        <li key={i.post_id || i.thread_id} className="border-b pb-1">
          {i.post_id || i.thread_id} - {i.c} flags
        </li>
      ))}
    </ul>
  );
}
