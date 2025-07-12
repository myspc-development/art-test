document.addEventListener('DOMContentLoaded', function () {
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
