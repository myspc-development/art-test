export async function fetchWidgets() {
  try {
    const res = await fetch('/wp-json/artpulse/v1/widgets');
    if (!res.ok) return [];
    return await res.json();
  } catch (e) {
    return [];
  }
}

export async function fetchRoles() {
  try {
    const res = await fetch('/wp-json/artpulse/v1/roles');
    if (!res.ok) return [];
    return await res.json();
  } catch (e) {
    return [];
  }
}
