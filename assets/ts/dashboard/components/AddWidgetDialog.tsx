import React, { useState } from 'react';
import type { WidgetMeta } from '../RoleDashboard';

export default function AddWidgetDialog({
  available,
  onAdd,
  onClose
}: {
  available: WidgetMeta[];
  onAdd: (id: string) => void;
  onClose: () => void;
}) {
  const [query, setQuery] = useState('');
  const list = available.filter(w =>
    w.title.toLowerCase().includes(query.toLowerCase())
  );
  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center">
      <div className="bg-white p-4 rounded w-80">
        <h2 className="text-lg mb-2">Add widget</h2>
        <input
          className="border w-full mb-2 p-1"
          placeholder="Search"
          value={query}
          onChange={e => setQuery(e.target.value)}
        />
        <ul className="max-h-40 overflow-auto">
          {list.map(w => (
            <li key={w.id}>
              <button
                className="w-full text-left py-1 hover:bg-gray-100"
                onClick={() => onAdd(w.id)}
              >
                {w.title}
              </button>
            </li>
          ))}
          {list.length === 0 && (
            <li className="text-sm text-gray-500">No widgets</li>
          )}
        </ul>
        <div className="text-right mt-2">
          <button className="px-2 py-1 border rounded" onClick={onClose}>
            Close
          </button>
        </div>
      </div>
    </div>
  );
}
