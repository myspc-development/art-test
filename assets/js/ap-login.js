document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('ap-login-form');
  const registerForm = document.getElementById('ap-register-form');
  const loginMsg = document.getElementById('ap-login-message');
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
    if (res.ok && data.success && action === 'ap_do_login') {
      window.location.reload();
    }
    return {res, data};
  }

  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      submitForm(loginForm, 'ap_do_login', loginMsg);
    });
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
