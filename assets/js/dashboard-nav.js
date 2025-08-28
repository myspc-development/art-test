document.addEventListener('DOMContentLoaded', function () {
  const root = document.getElementById('ap-dashboard-root');
  if (root && root.dataset.apV2 !== '1') {
    return;
  }
  if (window.APDashboardMenu?.debug) {
    const env = typeof process !== 'undefined' ? process.env?.NODE_ENV : undefined;
    if (env && env !== 'production') {
      console.log('AP roles:', APDashboardMenu.roles);
      console.log('Merged menu:', APDashboardMenu.menu);
      const dbg = document.createElement('div');
      dbg.className = 'ap-menu-debug';
      dbg.textContent = `Roles: ${APDashboardMenu.roles.join(', ')} | Items: ${APDashboardMenu.menu.length}`;
      document.querySelector('.dashboard-nav')?.appendChild(dbg);
    }
  }

  const sections = document.querySelectorAll('.ap-dashboard-section');
  const links = Array.from(document.querySelectorAll('.dashboard-link'));
  let activeId = null;
  let rafId = null;

  const scheduleUpdate = () => {
    if (rafId) return;
    rafId = requestAnimationFrame(() => {
      links.forEach(link => {
        const isActive = link.dataset.section === activeId;
        link.classList.toggle('active', isActive);
        link.setAttribute('aria-selected', isActive ? 'true' : 'false');
        link.tabIndex = isActive ? 0 : -1;
      });
      rafId = null;
    });
  };

  const observer = new IntersectionObserver(
    entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          activeId = entry.target.id;
          scheduleUpdate();
        }
      });
    },
    { threshold: 0.4 }
  );

  sections.forEach(section => observer.observe(section));
});
