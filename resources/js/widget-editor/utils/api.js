const REST_ROOT =
  (window.APWidgetEditor && window.APWidgetEditor.root) ||
  (window.wpApiSettings && window.wpApiSettings.root) ||
  '/wp-json/';

export async function fetchWidgets() {
  try {
    const res = await fetch(`${REST_ROOT}artpulse/v1/widgets`);
    if (!res.ok) return [];
    return await res.json();
  } catch (e) {
    return [];
  }
}

export async function fetchRoles() {
  try {
    const res = await fetch(`${REST_ROOT}artpulse/v1/roles`);
    if (!res.ok) return [];
    return await res.json();
  } catch (e) {
    return [];
  }
}
