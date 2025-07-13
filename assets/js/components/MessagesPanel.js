import React, { useEffect, useState } from 'react';

export default function MessagesPanel() {
  const [messages, setMessages] = useState([]);

  useEffect(() => {
    fetch('/wp-json/artpulse/v1/dashboard/messages')
      .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(setMessages)
      .catch(err => console.error('Messages load error:', err));
  }, []);

  return (
    <div className="ap-widget bg-white p-4 rounded shadow mb-4">
      {messages.map(msg => (
        <p key={msg.id}>{msg.content}</p>
      ))}
    </div>
  );
}
