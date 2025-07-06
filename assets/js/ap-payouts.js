(function(){
  document.addEventListener('DOMContentLoaded', () => {
    const cfg = window.APPayouts || {};
    const restRoot = cfg.root || (window.wpApiSettings && window.wpApiSettings.root) || '';
    const nonce = cfg.nonce || '';
    const headers = nonce ? { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' } : { 'Content-Type': 'application/json' };

    const balanceEl = document.getElementById('ap-payout-balance');
    const historyEl = document.getElementById('ap-payout-history');
    const form = document.getElementById('ap-payout-settings');
    const statusEl = document.getElementById('ap-payout-status');

    function renderHistory(items) {
      if (!historyEl) return;
      historyEl.innerHTML = '';
      if (!items || !items.length) {
        historyEl.textContent = cfg.i18n?.noHistory || 'No payouts yet.';
        return;
      }
      const ul = document.createElement('ul');
      items.forEach(p => {
        const li = document.createElement('li');
        li.textContent = `${p.payout_date} - $${p.amount} (${p.status})`;
        ul.appendChild(li);
      });
      historyEl.appendChild(ul);
    }

    function loadPayouts() {
      fetch(restRoot + 'artpulse/v1/user/payouts', { headers })
        .then(r => r.json())
        .then(data => {
          if (balanceEl) {
            balanceEl.textContent = (cfg.i18n?.balanceLabel || 'Current Balance:') + ' $' + data.balance;
          }
          renderHistory(data.payouts);
        });
    }

    form?.addEventListener('submit', e => {
      e.preventDefault();
      const method = form.method.value;
      fetch(restRoot + 'artpulse/v1/user/payouts/settings', {
        method: 'POST',
        headers,
        body: JSON.stringify({ method })
      }).then(res => res.json()).then(() => {
        if (statusEl) statusEl.textContent = cfg.i18n?.updated || 'Updated.';
      });
    });

    loadPayouts();
  });
})();
