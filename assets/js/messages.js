document.addEventListener("DOMContentLoaded", () => {
  const inbox = document.getElementById("ap-inbox");



  function loadMessages(delay = 0) {
    setTimeout(() => {
      fetch('/wp-json/artpulse/v1/messages/inbox', {
        headers: {
          'X-WP-Nonce': window.wpApiSettings.nonce
        }
      })
        .then(res => {
          if (!res.ok) throw new Error('Network response was not ok');
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
          attempt = 0; // reset after success
        })
        .catch(err => {
          console.warn('Messages API Error:', err.message);
          if (attempt < maxRetries) {
            const nextDelay = Math.pow(2, attempt) * 1000; // 1s, 2s, 4s
            attempt++;
            loadMessages(nextDelay);
          } else {
            inbox.textContent = '⚠ Could not load messages.';
          }
        });
    }, delay);
  }

  loadMessages();
});
