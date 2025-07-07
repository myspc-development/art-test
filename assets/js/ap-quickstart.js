document.addEventListener('DOMContentLoaded', () => {
  const mermaidEl = document.querySelector('.mermaid');
  if (mermaidEl) {
    const script = document.createElement('script');
    script.src = apQuickstart.mermaidUrl;
    script.onload = () => {
      if (window.mermaid) window.mermaid.initialize({ startOnLoad: true });
    };
    document.body.appendChild(script);
  }
  const modal = document.getElementById('ap-qs-modal');
  const img = document.getElementById('ap-qs-modal-img');
  const close = document.getElementById('ap-qs-modal-close');
  document.querySelectorAll('.qs-thumb').forEach(el => {
    el.addEventListener('click', () => {
      img.src = el.dataset.img;
      modal.classList.add('open');
    });
  });
  close?.addEventListener('click', () => {
    modal.classList.remove('open');
    img.src = '';
  });
});
