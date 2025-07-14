fetch('/wp-json/artpulse/v1/dashboard/messages')
  .then(res => res.json())
  .then(data => {
    const el = document.getElementById('ap-messages-dashboard-widget');
    if (!el) return;
    el.innerHTML = data.map(m => `<p><strong>${m.sender_name}:</strong> ${m.content}</p>`).join('');
  });
