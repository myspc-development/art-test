import React from 'react';

export default function CalendarHeatmap() {
  const cells = Array.from({ length: 35 }).map(() => Math.floor(Math.random() * 4));
  const colors = ['bg-gray-200', 'bg-green-200', 'bg-green-400', 'bg-green-600'];
  return (
    <div className="grid grid-cols-7 gap-1">
      {cells.map((lvl, i) => (
        <div key={i} className={`h-4 w-4 rounded ${colors[lvl]}`}></div>
      ))}
    </div>
  );
}

