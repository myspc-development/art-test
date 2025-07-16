(function(){
  const { createElement, useState, useEffect, render } = wp.element;

  function Message({ message }) {
    return createElement('div', { className: 'ap-message' },
      createElement('p', null, message.content),
      message.attachments && message.attachments.length ?
        createElement('ul', null, message.attachments.map(id => createElement('li', { key: id }, 'Attachment #' + id))) : null,
      message.tags && message.tags.length ?
        createElement('p', { className: 'ap-tags' }, 'Tags: ' + message.tags.join(', ')) : null
    );
  }

  function InboxApp() {
    const [messages, setMessages] = useState(APInbox.messages || []);

    useEffect(() => {
      if (APInbox.threadId) {
        fetch(APInbox.apiRoot + 'artpulse/v1/messages/thread?id=' + APInbox.threadId, {
          headers: { 'X-WP-Nonce': APInbox.nonce }
        }).then(r => r.json()).then(setMessages);
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
