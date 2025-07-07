(function(){
  const { createElement, useState, useEffect, render } = wp.element;

  function ThreadList({ onSelect }) {
    const [threads, setThreads] = useState([]);
    useEffect(() => {
      fetch(APForum.rest_url + 'artpulse/v1/forum/threads')
        .then(r => r.json())
        .then(setThreads);
    }, []);
    return createElement(
      'ul',
      { className: 'ap-thread-list' },
      threads.map(t =>
        createElement(
          'li',
          { key: t.id },
          createElement(
            'button',
            { type: 'button', onClick: () => onSelect(t.id) },
            t.title
          )
        )
      )
    );
  }

  function ThreadView({ threadId }) {
    const [thread, setThread] = useState(null);
    const [comments, setComments] = useState([]);
    const [text, setText] = useState('');

    useEffect(() => {
      if (!threadId) return;
      fetch(APForum.rest_url + 'wp/v2/ap_forum_thread/' + threadId)
        .then(r => r.json())
        .then(setThread);
      fetch(APForum.rest_url + 'artpulse/v1/forum/thread/' + threadId + '/comments')
        .then(r => r.json())
        .then(setComments);
    }, [threadId]);

    function submit(e) {
      e.preventDefault();
      if (!text) return;
      fetch(APForum.rest_url + 'artpulse/v1/forum/thread/' + threadId + '/comments', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': APForum.nonce },
        body: JSON.stringify({ content: text })
      })
        .then(() => {
          setText('');
          return fetch(APForum.rest_url + 'artpulse/v1/forum/thread/' + threadId + '/comments')
            .then(r => r.json())
            .then(setComments);
        });
    }

    if (!threadId) return createElement('p', null, 'Select a thread');
    if (!thread) return createElement('p', null, 'Loading...');

    return createElement(
      'div',
      { className: 'ap-thread' },
      createElement('h3', null, thread.title.rendered || thread.title),
      createElement('div', { dangerouslySetInnerHTML: { __html: thread.content.rendered } }),
      createElement(
        'ul',
        { className: 'ap-comment-list' },
        comments.map(c =>
          createElement('li', { key: c.id }, c.author + ': ' + c.content)
        )
      ),
      APForum.can_comment &&
        createElement(
          'form',
          { onSubmit: submit },
          createElement('textarea', {
            value: text,
            onChange: e => setText(e.target.value),
            required: true
          }),
          createElement('button', { type: 'submit' }, 'Reply')
        )
    );
  }

  function ForumApp() {
    const [active, setActive] = useState(null);
    return createElement(
      'div',
      { className: 'ap-forum' },
      createElement(ThreadList, { onSelect: setActive }),
      createElement(ThreadView, { threadId: active })
    );
  }

  document.addEventListener('DOMContentLoaded', function(){
    const el = document.getElementById('ap-forum');
    if (el) {
      render(createElement(ForumApp), el);
    }
  });
})();
