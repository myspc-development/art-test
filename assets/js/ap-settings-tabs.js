document.addEventListener('DOMContentLoaded', () => {
  const nav = document.getElementById('ap-settings-nav');
  if (!nav) return;
  const sections = document.querySelectorAll('.ap-settings-section');

  function activate(tab) {
    nav.querySelectorAll('.nav-tab').forEach(link => {
      const match = link.dataset.tab === tab;
      link.classList.toggle('nav-tab-active', match);
    });
    sections.forEach(sec => {
      sec.style.display = sec.dataset.tab === tab ? 'block' : 'none';
    });
  }

  const initial = location.hash.replace('#', '') || nav.querySelector('.nav-tab').dataset.tab;
  activate(initial);

  nav.addEventListener('click', e => {
    if (e.target.classList.contains('nav-tab')) {
      e.preventDefault();
      const tab = e.target.dataset.tab;
      activate(tab);
      history.replaceState(null, '', '#' + tab);
    }
  });

  const updateBtn = document.getElementById('ap-update-btn');
  if (updateBtn) {
    updateBtn.addEventListener('click', () => {
      const spinner = updateBtn.querySelector('.spinner');
      if (spinner) spinner.classList.add('is-active');
    });
  }
});
