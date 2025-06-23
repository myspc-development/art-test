document.addEventListener('DOMContentLoaded', () => {
  const registerForm = document.getElementById('ap-register-form');
  const regMsg = document.getElementById('ap-register-message');
  const continueSelect = document.getElementById('ap_continue_as');

  async function submitForm(form, action, msgEl) {
    const formData = new FormData(form);
    formData.append('action', action);
    formData.append('nonce', APLogin.nonce);

    const res = await fetch(APLogin.ajaxUrl, {
      method: 'POST',
      body: formData
    });

    const data = await res.json();
    if (msgEl) msgEl.textContent = data.data && data.data.message ? data.data.message : data.message || '';
    return {res, data};
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const result = await submitForm(registerForm, 'ap_do_register', regMsg);
      if (result.res.ok && result.data.success) {
        const choice = continueSelect ? continueSelect.value : '';
        if (choice === 'organization') {
          window.location.href = APLogin.orgSubmissionUrl;
        } else if (choice === 'artist') {
          try {
            const res = await fetch(APLogin.artistEndpoint, {
              method: 'POST',
              headers: { 'X-WP-Nonce': APLogin.restNonce }
            });
            const data = await res.json();
            if (res.ok) {
              regMsg.textContent = data.message || 'Request submitted';
            } else {
              regMsg.textContent = data.message || 'Request failed';
            }
          } catch (err) {
            regMsg.textContent = err.message;
          }
        }
      }
    });
  }
});
