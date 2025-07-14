document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('ap-toggle-dark-mode');
  if (!toggle) return;
  const saved = localStorage.getItem('ap-dark-mode') === 'true';
  if (saved) {
    document.body.setAttribute('data-theme', 'dark');
    toggle.checked = true;
  }
  toggle.addEventListener('change', () => {
    const enabled = toggle.checked;
    document.body.setAttribute('data-theme', enabled ? 'dark' : 'light');
    localStorage.setItem('ap-dark-mode', enabled);
  });
});
