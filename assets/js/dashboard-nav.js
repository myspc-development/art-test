document.addEventListener('DOMContentLoaded', function () {
  if (window.APDashboardMenu) {
    if (APDashboardMenu.debug) {
      console.log('AP roles:', APDashboardMenu.roles);
      console.log('Merged menu:', APDashboardMenu.menu);
      const dbg = document.createElement('div');
      dbg.className = 'ap-menu-debug';
      dbg.textContent = `Roles: ${APDashboardMenu.roles.join(', ')} | Items: ${APDashboardMenu.menu.length}`;
      document.querySelector('.dashboard-nav')?.appendChild(dbg);
    }
  }
  const sections = document.querySelectorAll('.ap-dashboard-section');
  const links = document.querySelectorAll('.dashboard-link');

  const observer = new IntersectionObserver(
    entries => {
      entries.forEach(entry => {
        const id = entry.target.id;
        if (entry.isIntersecting) {
          links.forEach(link => {
            link.classList.toggle('active', link.dataset.section === id);
          });
        }
      });
    },
    { threshold: 0.4 }
  );

  sections.forEach(section => observer.observe(section));
});
