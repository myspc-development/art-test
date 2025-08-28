(function(){
  document.addEventListener('DOMContentLoaded', () => {
    const cfg = window.APAccountSettings || {};
    const form = document.getElementById('ap-notification-prefs');
    const status = document.getElementById('ap-notification-status');
    if (!form) return;
    const root = cfg.root || (window.wpApiSettings && window.wpApiSettings.root) || '';
    const nonce = cfg.nonce || '';
    const headers = nonce ? { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' } : { 'Content-Type': 'application/json' };
    form.addEventListener('submit', e => {
      e.preventDefault();
      const prefs = {
        email: form.email.checked,
        push: form.push.checked,
        sms: form.sms.checked
      };
      const nonceField = form.querySelector('input[name="ap_notification_nonce"]');
      fetch(root + 'artpulse/v1/user-preferences', {
        method: 'POST',
        headers,
        body: JSON.stringify({
          ap_notification_nonce: nonceField ? nonceField.value : '',
          notification_prefs: prefs,
          digest_frequency: form.digest_frequency.value,
          digest_topics: form.digest_topics.value
        })
      }).then(r => r.json()).then(() => {
        if (status) status.textContent = cfg.i18n?.saved || 'Saved';
      });
    });
  });
})();
