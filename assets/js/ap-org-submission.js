document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.ap-org-submission-form');
  const messageBox = document.querySelector('.ap-org-submission-message');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const title = formData.get('title');
  const images = form.querySelector('#ap-org-images').files;
  const logoFile = form.querySelector('#ead_org_logo_id') ? form.querySelector('#ead_org_logo_id').files[0] : null;
  const bannerFile = form.querySelector('#ead_org_banner_id') ? form.querySelector('#ead_org_banner_id').files[0] : null;
  const addressComponentsInput = form.querySelector('[name="address_components"]');
  const addressComponents = addressComponentsInput ? addressComponentsInput.value : '';

  const submission = { post_type: 'artpulse_org', title };
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
      for (const file of Array.from(images).slice(0, 5)) {
        const id = await uploadMedia(file);
        imageIds.push(id);
      }

      let logoId = null;
      if (logoFile) logoId = await uploadMedia(logoFile);
      let bannerId = null;
      if (bannerFile) bannerId = await uploadMedia(bannerFile);

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
        if (form.querySelector('#ap-org-images')) form.querySelector('#ap-org-images').value = '';
        if (form.querySelector('#ead_org_logo_id')) form.querySelector('#ead_org_logo_id').value = '';
        if (form.querySelector('#ead_org_banner_id')) form.querySelector('#ead_org_banner_id').value = '';
      } else {
        if (messageBox) messageBox.textContent = data.message || 'Submission failed.';
      }
    } catch (err) {
      console.error(err);
      if (messageBox) messageBox.textContent = 'Error: ' + err.message;
    }
  });

  async function uploadMedia(file) {
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch(APSubmission.mediaEndpoint, {
      method: 'POST',
      headers: {
        'X-WP-Nonce': APSubmission.nonce
      },
      body: formData
    });

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.message || 'Image upload failed');
    }

    return result.id;
  }
});
