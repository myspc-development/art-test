document.addEventListener("DOMContentLoaded", () => {
  const inbox = document.getElementById("ap-inbox");

  fetch('/wp-json/artpulse/v1/messages/inbox')
    .then(res => res.json())
    .then(messages => {
      inbox.innerHTML = messages.map(msg =>
        `<div class="message ${msg.is_read ? 'read' : 'unread'}">\n` +
        `  <strong>From: ${msg.sender_id}</strong><br/>\n` +
        `  ${msg.content}<br/>\n` +
        `  <small>${msg.created_at}</small>\n` +
        `</div>`
      ).join('');
    });
});
