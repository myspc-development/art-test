import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import { Mail, Bell, Calendar } from 'lucide-react';

function UnifiedInboxWidget({ apiRoot, nonce }) {
  const [items, setItems] = useState([]);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/inbox?limit=20`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin',
    })
      .then((r) => r.json())
      .then(setItems);
  }, []);

  const counts = items.reduce((acc, item) => {
    if (!item.read) {
      acc[item.type] = (acc[item.type] || 0) + 1;
    }
    return acc;
  }, {});

  return (
    <div className="ap-inbox-widget">
      <div className="ap-inbox-counts">
        <span><Mail size={16} /> {counts.message || 0}</span>
        <span><Bell size={16} /> {counts.notification || 0}</span>
        <span><Calendar size={16} /> {counts.rsvp || 0}</span>
      </div>
      <ul className="ap-inbox-list">
        {items.map((item) => (
          <li key={item.type + item.id} className={item.read ? 'read' : 'unread'}>
            {item.content}
          </li>
        ))}
      </ul>
    </div>
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('ap-unified-inbox');
  if (el && window.APInboxData) {
    ReactDOM.render(
      <UnifiedInboxWidget apiRoot={APInboxData.apiRoot} nonce={APInboxData.nonce} />,
      el
    );
  }
});

export default UnifiedInboxWidget;
