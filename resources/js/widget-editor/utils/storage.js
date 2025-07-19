const REST_ROOT =
  (window.APWidgetEditor && window.APWidgetEditor.root) ||
  (window.wpApiSettings && window.wpApiSettings.root) ||
  '/wp-json/';
const REST_NONCE =
  (window.APWidgetEditor && window.APWidgetEditor.nonce) ||
  (window.wpApiSettings && window.wpApiSettings.nonce) ||
  '';

export async function loadLayout(role) {
  if (!role) return [];
  try {
    const res = await fetch(`${REST_ROOT}artpulse/v1/layout/${role}`);
    if (!res.ok) return [];
    return await res.json();
  } catch (e) {
    return [];
  }
}

export async function saveLayout(role, layout) {
  if (!role) return;
  try {
    await fetch(`${REST_ROOT}artpulse/v1/layout/${role}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': REST_NONCE,
      },
      body: JSON.stringify({ layout }),
    });
  } catch (e) {
    // ignore
  }
}
