import React, { useState } from 'react';

export default function Tasks() {
  const [tasks, setTasks] = useState([
    { label: 'Finish portfolio', done: false },
    { label: 'Reply to messages', done: false },
    { label: 'Plan next event', done: false }
  ]);

  const toggle = (i: number) =>
    setTasks(ts => ts.map((t, idx) => (idx === i ? { ...t, done: !t.done } : t)));

  return (
    <ul className="space-y-1">
      {tasks.map((t, i) => (
        <li key={i}>
          <label className="flex items-center space-x-2">
            <input type="checkbox" checked={t.done} onChange={() => toggle(i)} />
            <span className={t.done ? 'line-through' : ''}>{t.label}</span>
          </label>
        </li>
      ))}
    </ul>
  );
}

