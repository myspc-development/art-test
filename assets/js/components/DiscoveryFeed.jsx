import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';

export default function DiscoveryFeed() {
  const [items, setItems] = useState([]);

  useEffect(() => {
    fetch('/wp-json/artpulse/v1/trending')
      .then(r => r.json())
      .then(data => setItems(Array.isArray(data) ? data : []))
      .catch(() => setItems([]));
  }, []);

  return (
    <div className="ap-discovery-feed grid md:grid-cols-3 gap-4">
      {items.map(item => (
        <div key={item.id} className="border p-2 rounded bg-white">
          <a href={item.link} className="font-bold block mb-1">
            {item.title}
          </a>
          <span className="text-sm text-gray-500">Score: {item.score}</span>
        </div>
      ))}
    </div>
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('ap-discovery-feed');
  if (el && window.React && createRoot) {
    const root = createRoot(el);
    root.render(<DiscoveryFeed />);
  }
});
