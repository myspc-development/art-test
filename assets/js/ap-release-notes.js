document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('ap-release-notes-modal');
  if (!modal) return;
  modal.classList.add('open');
  const close = document.getElementById('ap-release-close');
  close?.addEventListener('click', () => {
    modal.classList.remove('open');
    const data = new FormData();
    data.append('action', 'ap_dismiss_release_notes');
    data.append('nonce', APReleaseNotes.nonce);
    fetch(APReleaseNotes.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data });
  });
});
