import React, { useEffect, useState } from 'react';
const { __ } = wp.i18n;

/**
 * Simple event chat widget.
 *
 * Props:
 * - eventId: Event ID for the chat thread.
 * - apiRoot: REST API base.
 * - nonce: WP nonce.
 */
export default function EventChatWidget({ eventId, apiRoot, nonce }) {
  const [messages, setMessages] = useState([]);
  const [text, setText] = useState('');

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/event/${eventId}/chat`)
      .then(r => r.json())
      .then(setMessages);
  }, [eventId]);

  const send = async () => {
    const resp = await fetch(`${apiRoot}artpulse/v1/event/${eventId}/message`, {
      method: 'POST',
      headers: {
        'X-WP-Nonce': nonce,
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ content: text })
    });
    if (resp.ok) {
      setText('');
      fetch(`${apiRoot}artpulse/v1/event/${eventId}/chat`)
        .then(r => r.json())
        .then(setMessages);
    }
  };

  return (
    <div className="ap-event-chat-widget">
      <ul className="chat-thread">
        {messages.map((m, i) => (
          <li key={i}><strong>{m.author}:</strong> {m.content}</li>
        ))}
      </ul>
      <div className="chat-form">
        <input
          value={text}
          onChange={e => setText(e.target.value)}
          placeholder={__('Write a message...', 'artpulse')}
        />
        <button onClick={send}>{__('Send', 'artpulse')}</button>
      </div>
    </div>
  );
}
