document.addEventListener('DOMContentLoaded', () => {
  const root = (window.APBioSummary && APBioSummary.root) || (window.wpApiSettings && window.wpApiSettings.root) || '';
  const nonce = (window.APBioSummary && APBioSummary.nonce) || '';
  const headers = nonce ? { 'X-WP-Nonce': nonce } : {};

  document.querySelectorAll('.ap-bio-summary').forEach(el => {
    const id = el.dataset.id;
    if (!id) return;
    fetch(root + 'artpulse/v1/bio-summary/' + id, { headers })
      .then(r => r.json())
      .then(data => {
        if (data.summary) {
          el.textContent = data.summary;
        }
      });
  });
});
