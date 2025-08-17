document.addEventListener('DOMContentLoaded', function () {
  if (window.APDashboardMenu?.debug) {
    if (process.env.NODE_ENV !== 'production') {
      console.log('AP roles:', APDashboardMenu.roles);
      console.log('Merged menu:', APDashboardMenu.menu);
    }
    const dbg = document.createElement('div');
    dbg.className = 'ap-menu-debug';
    dbg.textContent = `Roles: ${APDashboardMenu.roles.join(', ')} | Items: ${APDashboardMenu.menu.length}`;
    document.querySelector('.dashboard-nav')?.appendChild(dbg);
  }

  const sections = document.querySelectorAll('.ap-dashboard-section');
  const links = Array.from(document.querySelectorAll('.dashboard-link'));
  let activeId = null;
  let rafId = null;

  const scheduleUpdate = () => {
    if (rafId) return;
    rafId = requestAnimationFrame(() => {
      links.forEach(link => {
        link.classList.toggle('active', link.dataset.section === activeId);
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
