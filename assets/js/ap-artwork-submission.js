document.addEventListener('DOMContentLoaded', () => {
  const { __ } = wp.i18n;
  const form = document.querySelector('.ap-artwork-upload-form');
  const messageBox = document.querySelector('.ap-form-messages');
  if (!form) return;

  const saleCheckbox = form.querySelector('#ap-artwork-for-sale');
  const saleFields = form.querySelector('.ap-sale-fields');
  const saleEnabled = !!saleCheckbox;

  if (saleCheckbox && saleFields) {
    const toggleFields = () => {
      saleFields.style.display = saleCheckbox.checked ? '' : 'none';
    };
    saleCheckbox.addEventListener('change', toggleFields);
    toggleFields();
  }

  const imageInputs = Array.from(form.querySelectorAll('.ap-artwork-image'));
  const previews = [];
  imageInputs.forEach((input, idx) => {
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

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const title = formData.get('title');
    const medium = formData.get('artwork_medium');
    const dimensions = formData.get('artwork_dimensions');
    const materials = formData.get('artwork_materials');
    const forSale = saleCheckbox ? saleCheckbox.checked : false;
    const price = formData.get('price');
    const buyLink = formData.get('buy_link');
    const files = imageInputs.map(i => i.files[0]).filter(Boolean).slice(0, 5);

    const imageIds = [];
    if (messageBox) messageBox.textContent = '';

    try {
      for (let i = 0; i < files.length; i++) {
        const id = await uploadMedia(files[i]);
        imageIds.push(id);
      }

      const submission = {
        post_type: 'artpulse_artwork',
        title,
        artwork_medium: medium,
        artwork_dimensions: dimensions,
        artwork_materials: materials
      };
      if (saleEnabled) {
        submission.for_sale = forSale;
        if (forSale) {
          if (price) submission.price = price;
          if (buyLink) submission.buy_link = buyLink;
        }
      }
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
        if (messageBox) messageBox.textContent = __('Submission successful!', 'artpulse');
        form.reset();
        previews.forEach(p => { p.src = ''; p.style.display = 'none'; });
        imageInputs.forEach(i => { i.value = ''; });
        setTimeout(() => { window.location.reload(); }, 2000);
      } else if (messageBox) {
        messageBox.textContent = data.message || __('Submission failed.', 'artpulse');
      }
    } catch (err) {
      console.error(err);
      if (messageBox) messageBox.textContent = __('Error: ', 'artpulse') + err.message;
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
      throw new Error(result.message || __('Image upload failed', 'artpulse'));
    }
    return result.id;
  }
});
