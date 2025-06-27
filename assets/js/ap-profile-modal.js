document.addEventListener('DOMContentLoaded',() => {
  const links = document.querySelectorAll('.ap-edit-profile-link');
  let modal, formWrap, messageBox;
  let mediaFrame;

  function createModal() {
    if (modal) return;
    modal = document.createElement('div');
    modal.id = 'ap-profile-modal';
    modal.className = 'ap-org-modal';
    modal.innerHTML = `<button id="ap-profile-close" type="button" class="ap-form-button">Close</button><div id="ap-profile-msg" class="ap-form-messages" role="status" aria-live="polite"></div>`;
    formWrap = document.createElement('div');
    modal.appendChild(formWrap);
    document.body.appendChild(modal);
    messageBox = modal.querySelector('#ap-profile-msg');
    modal.querySelector('#ap-profile-close').addEventListener('click', () => modal.classList.remove('open'));
  }

  function openModalWithForm(form) {
    createModal();
    formWrap.innerHTML = '';
    formWrap.appendChild(form);
    attachMediaButton(form);
    modal.classList.add('open');
  }

  function attachMediaButton(form) {
    const btn = form.querySelector('#ap-avatar-media');
    const preview = form.querySelector('#ap-avatar-preview');
    const hidden = form.querySelector('#ap_avatar_id');
    if (!btn) return;
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      if (mediaFrame) {
        mediaFrame.open();
        return;
      }
      mediaFrame = wp.media({
        title: 'Select Avatar',
        button: { text: 'Use this image' },
        multiple: false
      });
      mediaFrame.on('select', () => {
        const attachment = mediaFrame.state().get('selection').first().toJSON();
        if (preview) {
          preview.src = attachment.url;
          preview.style.display = '';
        }
        if (hidden) hidden.value = attachment.id;
      });
      mediaFrame.open();
    });
  }

  function attachSubmit(form) {
    form.addEventListener('submit', e => {
      e.preventDefault();
      messageBox.textContent = '';
      const fd = new FormData(form);
      fd.append('action', 'update_profile_field');
      if (!fd.has('nonce') && APProfileModal.nonce) {
        fd.append('nonce', APProfileModal.nonce);
      }
      fetch(APProfileModal.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      }).then(r => r.json()).then(data => {
        if (data.success) {
          modal.classList.remove('open');
          if (data.data && data.data.display_name) {
            const nameEl = document.querySelector('.ap-user-name');
            if (nameEl) nameEl.textContent = data.data.display_name;
          }
        } else if (messageBox) {
          messageBox.textContent = data.data && data.data.message ? data.data.message : 'Error';
        }
      });
    }, { once: true });
  }

  links.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      fetch(link.href, { credentials: 'same-origin' })
        .then(res => res.text())
        .then(html => {
          const tmp = document.createElement('div');
          tmp.innerHTML = html;
          const form = tmp.querySelector('.ap-profile-edit-form');
          if (form) {
            attachSubmit(form);
            openModalWithForm(form);
          }
        });
    });
  });

  const existing = document.querySelector('.ap-profile-edit-form');
  if (existing) {
    attachSubmit(existing);
    openModalWithForm(existing);
  }
});
