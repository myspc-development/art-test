document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.ap-artist-submission-form');
  const messageBox = document.querySelector('.ap-form-messages');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const title = formData.get('title');
    const bio = formData.get('artist_bio');
    const org = formData.get('artist_org');
    const images = form.querySelector('#ap-artist-images').files;

    const imageIds = [];
    if (messageBox) messageBox.textContent = '';

    try {
      for (const file of Array.from(images).slice(0, 5)) {
        const id = await uploadMedia(file);
        imageIds.push(id);
      }

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
        if (form.querySelector('#ap-artist-images')) form.querySelector('#ap-artist-images').value = '';
        setTimeout(() => {
          window.location.href = APSubmission.dashboardUrl;
        }, 3000);
      } else {
        if (messageBox) messageBox.textContent = data.message || 'Submission failed.';
      }
    } catch (err) {
      console.error(err);
      if (messageBox) messageBox.textContent = 'Error: ' + err.message;
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
