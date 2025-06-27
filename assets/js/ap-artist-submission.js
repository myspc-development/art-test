document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.ap-artist-submission-form');
  const messageBox = document.querySelector('.ap-form-messages');
  if (!form) return;

  const fileInput = form.querySelector('#ap-artist-images');
  const previewWrap = document.createElement('div');
  previewWrap.className = 'ap-image-previews';
  fileInput.insertAdjacentElement('afterend', previewWrap);
  const orderInput = document.createElement('input');
  orderInput.type = 'hidden';
  orderInput.name = 'image_order';
  form.appendChild(orderInput);

  let files = [];
  let order = [];

  fileInput.addEventListener('change', () => {
    files = Array.from(fileInput.files).slice(0, 5);
    order = files.map((_, i) => i);
    renderPreviews();
    updateOrderInput();
  });

  let dragIndex = null;
  function renderPreviews() {
    previewWrap.innerHTML = '';
    files.forEach((file, i) => {
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
        renderPreviews();
        updateOrderInput();
      });
      previewWrap.appendChild(img);
    });
  }

  function updateOrderInput() {
    orderInput.value = order.join(',');
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

  const formData = new FormData(form);
  const title = formData.get('title');
  const bio = formData.get('artist_bio');
  const org = formData.get('artist_org');
  const images = files;

    const imageIds = [];
    if (messageBox) messageBox.textContent = '';

    try {
      for (const file of images.slice(0, 5)) {
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
        files = [];
        order = [];
        previewWrap.innerHTML = '';
        if (form.querySelector('#ap-artist-images')) form.querySelector('#ap-artist-images').value = '';
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
