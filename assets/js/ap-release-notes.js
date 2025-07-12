document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('ap-release-notes-modal');
  if (!modal) return;
  modal.classList.add('open');
  const close = document.getElementById('ap-release-close');

  const closeModal = () => {
    modal.classList.remove('open');
    const data = new FormData();
    data.append('action', 'ap_dismiss_release_notes');
    data.append('nonce', APReleaseNotes.nonce);
    fetch(APReleaseNotes.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data });
  };

  close?.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      closeModal();
    }
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeModal();
    }
  });
});
