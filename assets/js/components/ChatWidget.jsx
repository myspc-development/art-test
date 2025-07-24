import React, { useEffect, useState, useRef } from 'react';
import EmojiPicker from 'emoji-picker-react';
const { __ } = wp.i18n;

export default function ChatWidget({ eventId, canPost }) {
  const [messages, setMessages] = useState([]);
  const [text, setText] = useState('');
  const [showPicker, setShowPicker] = useState(false);
  const [error, setError] = useState(null);
  const fetching = useRef(false);

  const load = async () => {
    if (fetching.current) return;
    fetching.current = true;
    try {
      const resp = await fetch(`${APChat.apiRoot}artpulse/v1/event/${eventId}/chat`, {
        headers: { 'X-WP-Nonce': APChat.nonce },
        credentials: 'same-origin'
      });
      const data = await resp.json();
      if (Array.isArray(data)) {
        setMessages(data);
      } else {
        setMessages([]);
      }
    } catch (err) {
      setError(err);
    } finally {
      fetching.current = false;
    }
  };

  useEffect(() => {
    load();
    let id = null;
    if (!window.IS_DASHBOARD_BUILDER_PREVIEW) {
      id = setInterval(load, 5000);
    }
    return () => { if (id) clearInterval(id); };
  }, [eventId]);

  useEffect(() => {
    const list = document.querySelector('.ap-chat-list');
    if (list) list.scrollTop = list.scrollHeight;
  }, [messages]);

  const send = async e => {
    e.preventDefault();
    const msg = text.trim();
    if (!msg) return;
    try {
      await fetch(`${APChat.apiRoot}artpulse/v1/event/${eventId}/chat`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APChat.nonce
        },
        credentials: 'same-origin',
        body: JSON.stringify({ content: msg })
      });
      setText('');
      setShowPicker(false);
      load();
    } catch (err) {
      setError(err);
    }
  };

  const react = async (id, emoji) => {
    try {
      await fetch(`${APChat.apiRoot}artpulse/v1/chat/${id}/reaction`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': APChat.nonce },
        credentials: 'same-origin',
        body: JSON.stringify({ emoji })
      });
      load();
    } catch (err) {
      setError(err);
    }
  };

  return (
    <div className="ap-event-chat" data-event-id={eventId}>
      <ul className="ap-chat-list" role="status" aria-live="polite">
        {Array.isArray(messages) && messages.length > 0 ? (
          messages.map(m => (
            <li key={m.id}>
              <img className="ap-chat-avatar" src={m.avatar} alt="" />
              <span className="ap-chat-author">{m.author}</span>
              <span className="ap-chat-time">{new Intl.DateTimeFormat('en', { timeStyle: 'short' }).format(new Date(m.created_at))}</span>
              <p className="ap-chat-content">{m.content}</p>
              <div className="ap-chat-reactions">
                {m.reactions && Object.entries(m.reactions).map(([emo,c]) => (
                  <button key={emo} type="button" onClick={() => react(m.id, emo)}>{emo} {c}</button>
                ))}
                <button type="button" onClick={() => react(m.id, '❤️')}>❤️</button>
                <button type="button" onClick={() => react(m.id, '👍')}>👍</button>
              </div>
            </li>
          ))
        ) : (
          <li className="ap-chat-empty">{__('No messages yet.', 'artpulse')}</li>
        )}
      </ul>
      {error && (
        <p className="ap-chat-error">{__('Unable to load chat.', 'artpulse')}</p>
      )}
      {canPost ? (
        <form className="ap-chat-form" onSubmit={send}>
          <input
            type="text"
            aria-label={__('Chat message', 'artpulse')}
            value={text}
            onChange={e => setText(e.target.value)}
          />
          <button type="button" onClick={() => setShowPicker(!showPicker)}>😊</button>
          {showPicker && (
            <EmojiPicker onEmojiClick={e => setText(t => t + e.emoji)} />
          )}
          <button type="submit" aria-label={__('Send chat message', 'artpulse')}>{__('Send', 'artpulse')}</button>
        </form>
      ) : (
        <p>{APChat.loggedIn ? __('Only attendees can post messages', 'artpulse') : __('Please log in to chat.', 'artpulse')}</p>
      )}
    </div>
  );
}
