import React, { useEffect, useState } from 'react';

export default function MessagesPanel() {
  const [messages, setMessages] = useState([]);

  useEffect(() => {
    fetch('/wp-json/artpulse/v1/dashboard/messages')
      .then(res => {
        if (res.status === 401 || res.status === 403) {
          setMessages([{ id: 0, content: 'Please log in to view messages.' }]);
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
