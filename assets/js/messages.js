document.addEventListener("DOMContentLoaded", () => {
  const inbox = document.getElementById("ap-inbox");

  fetch('/wp-json/artpulse/v1/messages/inbox', {
    headers: {
      'X-WP-Nonce': window.wpApiSettings.nonce
    }
  })
    .then(res => {
      if (res.status === 401 || res.status === 403) {
        inbox.textContent = 'Please log in to view messages.';
        throw new Error('unauthorized');
      }
      return res.json();
    })
    .then(messages => {
      if (!Array.isArray(messages)) {
        throw new Error('API did not return an array');
      }

      inbox.innerHTML = messages.map(msg =>
        `<div class="message ${msg.is_read ? 'read' : 'unread'}">\n` +
        `  <strong>From: ${msg.sender_id}</strong><br/>\n` +
        `  ${msg.content}<br/>\n` +
        `  <small>${msg.created_at}</small>\n` +
        `</div>`
      ).join('');
    })
    .catch(err => {
      console.warn('Messages API Error:', err.message);
      inbox.textContent = '⚠ Could not load messages.';
    });
});
