import React, { useEffect, useState } from 'react';
const { __ } = wp.i18n;

export default function MessagesPanel() {
  const [messages, setMessages] = useState([]);
  const apiRoot = window.ArtPulseDashboardApi?.root || '/wp-json/';
  const nonce = window.ArtPulseDashboardApi?.nonce || '';

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/dashboard/messages`, {
      headers: { 'X-WP-Nonce': nonce }
    })
      .then(res => {
        if (res.status === 401 || res.status === 403) {
          setMessages([{ id: 0, content: __('Please log in to view messages.', 'artpulse') }]);
          return Promise.reject('unauthorized');
        }
        return res.json();
      })
      .then(setMessages)
      .catch(() => {});
  }, []);

  return (
    <div className="ap-widget bg-white p-4 rounded shadow mb-4">
      {messages.map(msg => (
        <p key={msg.id}>{msg.content}</p>
      ))}
    </div>
  );
}
