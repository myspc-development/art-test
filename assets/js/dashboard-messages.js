fetch('/wp-json/artpulse/v1/dashboard/messages', {
  headers: {
    'X-WP-Nonce': ArtPulseData.nonce
  }
})
  .then(res => res.json())
  .then(data => {
    if (!Array.isArray(data)) {
      throw new Error('Invalid response format');
    }

    const container = document.getElementById('ap-messages-dashboard-widget');
    if (!container) return;

    const html = data.map(msg => {
      const time = new Date(msg.timestamp).toLocaleString();
      const isUnread = msg.status === 'unread';
      return `
        <div class="ap-message-card" style="padding: 8px; border-bottom: 1px solid #ddd;">
          <div style="display: flex; justify-content: space-between;">
            <strong>${msg.sender_name}</strong>
            <span style="font-size: 12px; color: #666;">${time}</span>
          </div>
          <div style="margin: 6px 0;">${msg.content}</div>
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="badge ${isUnread ? 'badge-unread' : 'badge-read'}" style="
              padding: 2px 6px; font-size: 11px;
              color: white;
              background-color: ${isUnread ? '#d9534f' : '#5cb85c'};
              border-radius: 4px;
            ">${isUnread ? 'Unread' : 'Read'}</span>
            <button class="reply-button" data-msg-id="${msg.id}" style="
              background-color: #0073aa;
              border: none;
              color: white;
              padding: 4px 8px;
              font-size: 12px;
              border-radius: 3px;
              cursor: pointer;
            ">Reply</button>
          </div>
        </div>
      `;
    }).join('');

    container.innerHTML = html;

    let activeMessageId = null;

    document.getElementById('ap-cancel-reply').onclick = () => {
      document.getElementById('ap-message-modal').style.display = 'none';
    };

    document.getElementById('ap-send-reply').onclick = () => {
      const text = document.getElementById('ap-reply-text').value;
      fetch(`/wp-json/artpulse/v1/messages/${activeMessageId}/reply`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': ArtPulseData.nonce
        },
        body: JSON.stringify({ message: text })
      })
        .then(res => res.json())
        .then(() => {
          alert('Reply sent!');
          document.getElementById('ap-message-modal').style.display = 'none';
        });
    };

    container.querySelectorAll('.reply-button').forEach(button => {
      button.addEventListener('click', (e) => {
        activeMessageId = e.target.getAttribute('data-msg-id');
        document.getElementById('ap-reply-text').value = '';
        document.getElementById('ap-message-modal').style.display = 'block';
      });
    });
  })
  .catch(err => {
    console.error('Failed to load messages', err);
  });
