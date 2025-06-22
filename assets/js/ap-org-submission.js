document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.ap-org-submission-form');
  const messageBox = document.querySelector('.ap-org-submission-message');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const title = formData.get('title');
    const description = formData.get('description');
    const website = formData.get('org_website');
    const email = formData.get('org_email');
    const images = form.querySelector('#ap-org-images').files;

    const imageIds = [];
    if (messageBox) messageBox.textContent = '';

    try {
      for (const file of Array.from(images).slice(0, 5)) {
        const id = await uploadMedia(file);
        imageIds.push(id);
      }

      const submission = {
        post_type: 'artpulse_org',
        title: title,
        org_description: description,
        org_website: website,
        org_email: email,
        image_ids: imageIds
      };

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
