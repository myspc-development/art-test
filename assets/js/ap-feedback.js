document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('ap-feedback-button');
  const modal = document.getElementById('ap-feedback-modal');
  const form = document.getElementById('ap-feedback-form');
  const closeBtn = document.getElementById('ap-feedback-close');
  const msg = document.getElementById('ap-feedback-message');
  const list = document.getElementById('ap-feedback-list');
  if (!btn || !modal || !form) return;

  function loadList() {
    if (!list) return;
    fetch(APFeedback.apiRoot + 'artpulse/v1/feedback')
      .then(r => r.json())
      .then(items => {
        list.innerHTML = '';
        items.forEach(item => {
          const li = document.createElement('li');
          li.textContent = item.description + ' ';
          const vote = document.createElement('button');
          vote.textContent = '▲ ' + item.votes;
          if (item.voted) vote.disabled = true;
          vote.addEventListener('click', () => {
            fetch(APFeedback.apiRoot + 'artpulse/v1/feedback/' + item.id + '/vote', {
              method: 'POST',
              headers: { 'X-WP-Nonce': APFeedback.restNonce }
            })
              .then(r => r.json())
              .then(res => {
                if (res.success) {
                  vote.textContent = '▲ ' + res.votes;
                  vote.disabled = true;
                }
              });
          });
          li.appendChild(vote);
          list.appendChild(li);
        });
      });
  }

  btn.addEventListener('click', () => {
    modal.hidden = false;
    form.querySelector('#ap-feedback-description').focus();
    loadList();
  });
  if (closeBtn) {
    closeBtn.addEventListener('click', () => { modal.hidden = true; });
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    msg.textContent = '';
    const data = new FormData(form);
    data.append('action', 'ap_submit_feedback');
    data.append('context', window.location.href);
    fetch(APFeedback.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: data
    })
      .then(r => r.json())
      .then(res => {
        if (res.success) {
          msg.textContent = APFeedback.thanks;
          form.reset();
          loadList();
        } else {
          msg.textContent = res.data && res.data.message ? res.data.message : 'Error';
        }
      });
  });
});
