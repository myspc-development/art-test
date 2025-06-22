document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.ap-submission-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const postType = form.dataset.postType || 'artpulse_event';

    const title = formData.get('title');
  const eventDate = formData.get('event_date');
  const countrySel = form.querySelector('.ap-address-country');
  const stateSel = form.querySelector('.ap-address-state');
  const citySel = form.querySelector('.ap-address-city');
  const addressComponentsInput = form.querySelector('[name="address_components"]');
  const addressComponents = addressComponentsInput ? addressComponentsInput.value : '';
  let eventLocation = formData.get('event_location');
  if (citySel) {
    const parts = [];
    if (citySel.value) parts.push(citySel.options[citySel.selectedIndex].text);
    if (stateSel && stateSel.value) parts.push(stateSel.options[stateSel.selectedIndex].text);
    if (countrySel && countrySel.value) parts.push(countrySel.options[countrySel.selectedIndex].text);
    eventLocation = parts.join(', ');
    form.querySelector('[name="event_location"]').value = eventLocation;
  }
  const images = form.querySelector('#ap-images').files;

    const imageIds = [];

    try {
      // Upload each image and get the media ID
      for (const file of images) {
        const mediaId = await uploadMedia(file);
        imageIds.push(mediaId);
      }

      const submission = {
        post_type: postType,
        title: title,
        event_date: eventDate,
        event_location: eventLocation,
        image_ids: imageIds
      };
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
        alert('Submission successful!');
        console.log(data);
      } else {
        alert(data.message || 'Submission failed.');
      }
    } catch (err) {
      console.error(err);
      alert('Error: ' + err.message);
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

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Image upload failed');
    }

    const result = await response.json();
    return result.id;
  }
});
