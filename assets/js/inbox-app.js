(function(){
  const { createElement, useState, useEffect, render } = wp.element;
  const { __ } = wp.i18n;

  function Message({ message }) {
    return createElement('div', { className: 'ap-message' },
      createElement('p', null, message.content),
      message.attachments && message.attachments.length ?
        createElement('ul', null, message.attachments.map(id => createElement('li', { key: id }, __('Attachment #', 'artpulse') + id))) : null,
      message.tags && message.tags.length ?
        createElement('p', { className: 'ap-tags' }, __('Tags:', 'artpulse') + ' ' + message.tags.join(', ')) : null
    );
  }

  function InboxApp() {
    const [messages, setMessages] = useState(APInbox.messages || []);

    useEffect(() => {
      if (APInbox.threadId) {
        fetch(APInbox.apiRoot + 'artpulse/v1/messages/thread?id=' + APInbox.threadId, {
          headers: { 'X-WP-Nonce': APInbox.nonce }
        }).then(res => {
          if (res.status === 401 || res.status === 403) {
            setMessages([{ id: 0, content: __('Please log in to view this thread.', 'artpulse') }]);
            return Promise.reject('unauthorized');
          }
          return res.json();
        }).then(setMessages).catch(() => {});
      }
    }, []);

    return createElement('div', null,
      messages.map(m => createElement(Message, { message: m, key: m.id }))
    );
  }

  document.addEventListener('DOMContentLoaded', function(){
    const el = document.getElementById('ap-inbox-app');
    if (el) {
      render(createElement(InboxApp), el);
    }
  });
})();
