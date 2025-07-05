document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('ap-login-form');
  const loginMsg = document.getElementById('ap-login-message');
  const registerForm = document.getElementById('ap-register-form');
  const regMsg = document.getElementById('ap-register-message');
  const regSuccess = document.getElementById('ap-register-success');
  const displayName = document.getElementById('ap_reg_display_name');
  const bio = document.getElementById('ap_reg_bio');
  const password = document.getElementById('ap_reg_pass');
  const passConfirm = document.getElementById('ap_reg_confirm');
  const country = document.getElementById('ap_country');
  const state = document.getElementById('ap_state');
  const city = document.getElementById('ap_city');
  const addr = document.getElementById('ap_address_components');

  async function submitForm(form, action, msgEl) {
    const formData = new FormData(form);
    if (action === 'ap_do_register') {
      if (displayName) formData.set('display_name', displayName.value);
      if (bio) formData.set('description', bio.value);
      if (password) formData.set('password', password.value);
      if (passConfirm) formData.set('password_confirm', passConfirm.value);
      if (country || state || city) {
        let comp = addr ? addr.value : '';
        if (!comp) {
          comp = JSON.stringify({
            country: country ? country.value : '',
            state: state ? state.value : '',
            city: city ? city.value : ''
          });
          if (addr) addr.value = comp;
        }
        formData.set('address_components', comp);
      }
    }

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

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const result = await submitForm(loginForm, 'ap_do_login', loginMsg);
      if (result.res.ok && result.data.success) {
        const target = result.data.data && result.data.data.dashboardUrl ? result.data.data.dashboardUrl : APLogin.dashboardUrl;
        window.location.href = target;
      }
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (regSuccess) regSuccess.textContent = 'Submitting...';
      const result = await submitForm(registerForm, 'ap_do_register', regMsg);
      if (result.res.ok && result.data.success) {
        if (regSuccess) regSuccess.textContent = result.data.data && result.data.data.message ? result.data.data.message : result.data.message || 'Registration successful';
        window.location.href = APLogin.dashboardUrl;
      } else {
        if (regSuccess) regSuccess.textContent = '';
      }
    });
  }
});
