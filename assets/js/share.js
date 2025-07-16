// Log share interactions via REST API
window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.ap-share-buttons').forEach(container => {
    const objectId = container.dataset.shareId;
    const objectType = container.dataset.shareType || 'post';
    container.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        const cls = Array.from(link.classList).find(c => c.startsWith('ap-share-'));
        const net = cls ? cls.replace('ap-share-', '') : '';
        if (!objectId) return;
        fetch(APShare.apiRoot + 'artpulse/v1/share', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': APShare.nonce
          },
          body: JSON.stringify({
            object_id: objectId,
            object_type: objectType,
            network: net
          })
        });
      });
    });
  });
});
