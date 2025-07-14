document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.ap-artist-submission-form');
  const messageBox = document.querySelector('.ap-form-messages');
  if (!form) return;

  const inputs = Array.from(form.querySelectorAll('.ap-artist-image'));
  const previews = [];

  inputs.forEach((input, idx) => {
    const preview = document.createElement('img');
    preview.className = 'ap-image-preview';
    preview.style.display = 'none';
    input.insertAdjacentElement('afterend', preview);
    previews[idx] = preview;

    input.addEventListener('change', () => {
      const file = input.files[0];
      if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = '';
      } else {
        preview.src = '';
        preview.style.display = 'none';
      }
    });
  });

  const submitBtn = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (submitBtn) submitBtn.disabled = true;

    const formData = new FormData(form);
    const title = formData.get('title');
    const bio = formData.get('artist_bio');
    const org = formData.get('artist_org');
    const files = inputs.map(i => i.files[0]).filter(Boolean).slice(0, 5);

    const imageIds = [];
    if (messageBox) messageBox.textContent = '';

    try {
      for (let i = 0; i < files.length; i++) {
        if (messageBox) messageBox.textContent = `Uploading image ${i + 1} of ${files.length}`;
        const id = await uploadMedia(files[i]);
        imageIds.push(id);
      }
      if (messageBox) messageBox.textContent = 'Submitting form...';

      const submission = {
        post_type: 'artpulse_artist',
        title,
        artist_bio: bio,
        artist_org: org,
        artist_name: title
      };
      if (imageIds.length) submission.image_ids = imageIds;

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
        previews.forEach(p => { p.src = ''; p.style.display = 'none'; });
        inputs.forEach(i => { i.value = ''; });
        setTimeout(() => { window.location.href = APSubmission.dashboardUrl; }, 3000);
      } else if (messageBox) {
        messageBox.textContent = data.message || 'Submission failed.';
      }
    } catch (err) {
      console.error(err);
      if (messageBox) messageBox.textContent = 'Error: ' + err.message;
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });

  async function uploadMedia(file) {
    const fd = new FormData();
    fd.append('file', file);

    const response = await fetch(APSubmission.mediaEndpoint, {
      method: 'POST',
      headers: { 'X-WP-Nonce': APSubmission.nonce },
      body: fd
    });

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.message || 'Image upload failed');
    }
    return result.id;
  }
});
