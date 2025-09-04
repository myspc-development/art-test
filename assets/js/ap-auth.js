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
  const suburb = document.getElementById('ap_suburb');
  const street = document.getElementById('ap_street');
  const addr = document.getElementById('ap_address_components');

  async function submitForm(form, action, msgEl) {
    const formData = new FormData(form);
    if (action === 'ap_do_register') {
      if (displayName) formData.set('display_name', displayName.value);
      if (bio) formData.set('description', bio.value);
      if (password) formData.set('password', password.value);
      if (passConfirm) formData.set('password_confirm', passConfirm.value);
      if (country || state || city || suburb || street) {
        let comp = addr ? addr.value : '';
        if (!comp) {
          comp = JSON.stringify({
            country: country ? country.value : '',
            state: state ? state.value : '',
            city: city ? city.value : '',
            suburb: suburb ? suburb.value : '',
            street: street ? street.value : ''
          });
          if (addr) addr.value = comp;
        }
        formData.set('address_components', comp);
      }
    } else if (action === 'ap_do_login') {
      const remember = form.querySelector('[name="remember"]');
      if (remember && remember.checked) {
        formData.set('remember', '1');
      }
    }

    formData.append('action', action);
    formData.append('nonce', APLogin.nonce);

    const submitBtn = form.querySelector('[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    try {
      const res = await fetch(APLogin.ajaxUrl, {
        method: 'POST',
        body: formData
      });

      const data = await res.json();
      if (!res.ok || !data.success) {
        const invalidNames = data.data && data.data.invalid ? data.data.invalid : [];
        let field = null;
        if (invalidNames.length) {
          field = form.querySelector(`[name="${invalidNames[0]}"]`);
        }
        if (!field) {
          field = form.querySelector(':invalid');
        }
        if (field) field.focus();
      }
      if (msgEl) msgEl.textContent = data.data && data.data.message ? data.data.message : data.message || '';
      return {res, data};
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  }

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      try {
        const result = await submitForm(loginForm, 'ap_do_login', loginMsg);
        if (result.res.ok && result.data.success && result.data.data && result.data.data.dashboardUrl) {
          window.location.href = result.data.data.dashboardUrl;
        }
      } catch (err) {
        // ignore, messages handled in submitForm
      }
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (regSuccess) regSuccess.textContent = 'Submitting...';
      try {
        const result = await submitForm(registerForm, 'ap_do_register', regMsg);
        if (result.res.ok && result.data.success) {
          if (regSuccess) regSuccess.textContent = result.data.data && result.data.data.message ? result.data.data.message : result.data.message || 'Registration successful';
          window.location.href = APLogin.dashboardUrl;
        } else if (regSuccess) {
          regSuccess.textContent = '';
        }
      } catch (err) {
        if (regSuccess) regSuccess.textContent = '';
      }
    });
  }
});
