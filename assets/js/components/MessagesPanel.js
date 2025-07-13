import React, { useEffect, useState } from 'react';

export default function MessagesPanel() {
  const [messages, setMessages] = useState([]);

  useEffect(() => {
    fetch('/wp-json/artpulse/v1/dashboard/messages')
      .then(res => res.json())
      .then(setMessages);
  }, []);

  return (
    <div className="ap-widget bg-white p-4 rounded shadow mb-4">
      {messages.map(msg => (
        <p key={msg.id}>{msg.content}</p>
      ))}
    </div>
  );
}
