const PREFIX = 'ap_widget_layout_';

export function loadLayout(role) {
  const json = localStorage.getItem(PREFIX + role);
  if (!json) return null;
  try {
    return JSON.parse(json);
  } catch (e) {
    return null;
  }
}

export function saveLayout(role, layout) {
  if (!role) return;
  localStorage.setItem(PREFIX + role, JSON.stringify(layout));
}
