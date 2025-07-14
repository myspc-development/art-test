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
    const el = document.getElementById('ap-messages-dashboard-widget');
    if (!el) return;
    el.innerHTML = data.map(m => `<p><strong>${m.sender_name}:</strong> ${m.content}</p>`).join('');
  })
  .catch(err => {
    console.error('Failed to load messages', err);
  });
