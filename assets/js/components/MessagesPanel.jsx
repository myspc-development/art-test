import React, { useEffect, useState } from 'react';
const { __ } = wp.i18n;

export default function MessagesPanel() {
  const [messages, setMessages] = useState([]);
  const apiRoot = window.ArtPulseDashboardApi?.apiUrl || window.ArtPulseDashboardApi?.root || '/wp-json/';
  const nonce = window.apNonce || window.ArtPulseDashboardApi?.nonce || '';
  const token = window.ArtPulseDashboardApi?.apiToken || '';

  useEffect(() => {
    const headers = { 'X-WP-Nonce': nonce };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    fetch(`${apiRoot}artpulse/v1/dashboard/messages`, {
      headers,
      credentials: 'same-origin'
    })
      .then(res => (res.status === 401 || res.status === 403 || res.status === 404 ? [] : res.json()))
      .then(setMessages)
      .catch(() => setMessages([]));
  }, []);

  return (
    <div className="ap-widget bg-white p-4 rounded shadow mb-4">
      {messages.length === 0 ? (
        <p>{__('No messages available.', 'artpulse')}</p>
      ) : (
        messages.map(msg => <p key={msg.id}>{msg.content}</p>)
      )}
    </div>
  );
}
