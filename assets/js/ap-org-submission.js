document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.ap-org-submission-form');
  const messageBox = document.querySelector('.ap-form-messages');
  if (!form) return;

  const fileInput = form.querySelector('#ap-org-images');
  const previewWrap = document.createElement('div');
  previewWrap.className = 'ap-image-previews';
  fileInput.insertAdjacentElement('afterend', previewWrap);
  const orderInput = document.createElement('input');
  orderInput.type = 'hidden';
  orderInput.name = 'image_order';
  form.appendChild(orderInput);

  const submitBtn = form.querySelector('button[type="submit"]');
  let progressBars = [];

  let files = [];
  let order = [];

  fileInput.addEventListener('change', () => {
    files = Array.from(fileInput.files).slice(0, 5);
    order = files.map((_, i) => i);
    progressBars = [];
    renderPreviews();
    updateOrderInput();
  });

  let dragIndex = null;
  function renderPreviews() {
    previewWrap.innerHTML = '';
    files.forEach((file, i) => {
      const wrapper = document.createElement('div');
      wrapper.style.display = 'inline-block';
      wrapper.style.marginRight = '0.5rem';
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.className = 'ap-image-preview';
      img.draggable = true;
      img.dataset.index = i;
      img.addEventListener('dragstart', () => { dragIndex = i; });
      img.addEventListener('dragover', e => e.preventDefault());
      img.addEventListener('drop', e => {
        e.preventDefault();
        const target = parseInt(e.currentTarget.dataset.index, 10);
        if (dragIndex === null || dragIndex === target) return;
        const [f] = files.splice(dragIndex, 1);
        const [o] = order.splice(dragIndex, 1);
        files.splice(target, 0, f);
        order.splice(target, 0, o);
        progressBars = [];
        renderPreviews();
        updateOrderInput();
      });
      const progress = document.createElement('progress');
      progress.value = 0;
      progress.max = 100;
      progress.className = 'ap-upload-progress';
      wrapper.appendChild(img);
      wrapper.appendChild(progress);
      previewWrap.appendChild(wrapper);
      progressBars[i] = progress;
    });
  }

  function updateOrderInput() {
    orderInput.value = order.join(',');
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (submitBtn) submitBtn.disabled = true;

    const formData = new FormData(form);
    const title = formData.get('title');
  const images = files;
  const logoFile = form.querySelector('#ead_org_logo_id') ? form.querySelector('#ead_org_logo_id').files[0] : null;
  const bannerFile = form.querySelector('#ead_org_banner_id') ? form.querySelector('#ead_org_banner_id').files[0] : null;
  const addressComponentsInput = form.querySelector('[name="address_components"]');
  let addressComponents = addressComponentsInput ? addressComponentsInput.value : '';
  const countrySel = form.querySelector('.ap-address-country');
  const stateSel = form.querySelector('.ap-address-state');
  const citySel = form.querySelector('.ap-address-city');
  if (!addressComponents && countrySel) {
    addressComponents = JSON.stringify({
      country: countrySel.value,
      state: stateSel ? stateSel.value : '',
      city: citySel ? citySel.value : ''
    });
    if (addressComponentsInput) addressComponentsInput.value = addressComponents;
  }

  const submission = {
    post_type: 'artpulse_org',
    title,
    ead_org_name: title
  };
  formData.delete('title');
  formData.delete('images[]');
  formData.delete('ead_org_logo_id');
  formData.delete('ead_org_banner_id');
    for (const [key, value] of formData.entries()) {
      submission[key] = value;
    }
    document.querySelectorAll('.ap-org-submission-form input[type="checkbox"]').forEach(cb => {
      if (!formData.has(cb.name)) submission[cb.name] = '0';
    });

    const imageIds = [];
    if (messageBox) messageBox.textContent = '';

    try {
      for (let i = 0; i < images.slice(0, 5).length; i++) {
        if (messageBox) messageBox.textContent = `Uploading image ${i + 1} of ${images.length}`;
        const id = await uploadMedia(images[i], i);
        imageIds.push(id);
      }

      if (messageBox) messageBox.textContent = 'Uploading logo/banner...';
      let logoId = null;
      if (logoFile) logoId = await uploadMedia(logoFile, images.length);
      let bannerId = null;
      if (bannerFile) bannerId = await uploadMedia(bannerFile, images.length + 1);

      if (messageBox) messageBox.textContent = 'Submitting form...';

      submission.image_ids = imageIds;
  if (logoId) submission.ead_org_logo_id = logoId;
  if (bannerId) submission.ead_org_banner_id = bannerId;
  if (addressComponents) submission.address_components = addressComponents;

      const res = await fetch(APSubmission.endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APSubmission.nonce
        },
        body: JSON.stringify(submission)
      });

      const data = await res.json();

      if (res.ok) {
        if (messageBox) messageBox.textContent = 'Submission successful!';
        form.reset();
        files = [];
        order = [];
        previewWrap.innerHTML = '';
        if (form.querySelector('#ap-org-images')) form.querySelector('#ap-org-images').value = '';
        if (form.querySelector('#ead_org_logo_id')) form.querySelector('#ead_org_logo_id').value = '';
        if (form.querySelector('#ead_org_banner_id')) form.querySelector('#ead_org_banner_id').value = '';
        updateOrderInput();
        setTimeout(() => {
          window.location.href = APSubmission.dashboardUrl;
        }, 3000);
      } else {
        if (messageBox) messageBox.textContent = data.message || 'Submission failed.';
      }
    } catch (err) {
      console.error(err);
      if (messageBox) messageBox.textContent = 'Error: ' + err.message;
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });

  function uploadMedia(file, index) {
    return new Promise((resolve, reject) => {
      const formData = new FormData();
      formData.append('file', file);

      const xhr = new XMLHttpRequest();
      xhr.open('POST', APSubmission.mediaEndpoint);
      xhr.setRequestHeader('X-WP-Nonce', APSubmission.nonce);
      xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable && progressBars[index]) {
          progressBars[index].value = e.loaded;
          progressBars[index].max = e.total;
        }
      });
      xhr.onload = () => {
        let result = {};
        try { result = JSON.parse(xhr.responseText); } catch (_) {}
        if (xhr.status >= 200 && xhr.status < 300) {
          resolve(result.id);
        } else {
          reject(new Error(result.message || 'Image upload failed'));
        }
      };
      xhr.onerror = () => reject(new Error('Image upload failed'));
      xhr.send(formData);
    });
  }
});
