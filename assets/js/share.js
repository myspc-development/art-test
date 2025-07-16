document.addEventListener('DOMContentLoaded', () => {
  const cfg = window.APShare || {};
  const apiRoot = cfg.apiRoot || (window.wpApiSettings && window.wpApiSettings.root) || '';
  const nonce = cfg.nonce || '';

  const headers = { 'Content-Type': 'application/json' };
  if (nonce) {
    headers['X-WP-Nonce'] = nonce;
  }

  document.body.addEventListener('click', ev => {
    const link = ev.target.closest('a[class^="ap-share-"]');
    if (!link) return;

    const networkClass = Array.from(link.classList).find(c => c.startsWith('ap-share-'));
    if (!networkClass) return;
    const network = networkClass.replace('ap-share-', '');

    const objectId = link.dataset.objectId;
    const objectType = link.dataset.objectType;
    if (!objectId || !objectType) return;

    fetch(apiRoot + 'artpulse/v1/share', {
      method: 'POST',
      headers,
      body: JSON.stringify({
        object_id: objectId,
        object_type: objectType,
        network
      })
    });
  });
});
