document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.ap-form-container');
  const messageBox = document.querySelector('.ap-form-messages');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const postType = form.dataset.postType || 'artpulse_event';

    const title = formData.get('title');
  const eventDate = formData.get('event_date');
  const startDate = formData.get('event_start_date');
  const endDate = formData.get('event_end_date');
  const venueName = formData.get('venue_name');
  const countrySel = form.querySelector('.ap-address-country');
  const stateSel = form.querySelector('.ap-address-state');
  const citySel = form.querySelector('.ap-address-city');
  const streetInput = form.querySelector('.ap-address-street');
  const postcodeInput = form.querySelector('.ap-address-postcode');
  const addressComponentsInput = form.querySelector('[name="address_components"]');
  const addressComponents = addressComponentsInput ? addressComponentsInput.value : '';
  let eventLocation = formData.get('event_location');
  if (citySel) {
    const parts = [];
    if (citySel.value) parts.push(citySel.value);
    if (stateSel && stateSel.value) parts.push(stateSel.value);
    if (countrySel && countrySel.value) parts.push(countrySel.value);
    eventLocation = parts.join(', ');
    form.querySelector('[name="event_location"]').value = eventLocation;
  }
  const imagesInput = form.querySelector('#ap-images');
  const images = imagesInput ? imagesInput.files : [];
  const bannerInput = form.querySelector('#ap-banner');
  const bannerFile = bannerInput && bannerInput.files.length ? bannerInput.files[0] : null;

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
        event_start_date: startDate,
        event_end_date: endDate,
        venue_name: venueName,
        event_location: eventLocation,
        event_street_address: streetInput ? streetInput.value : formData.get('event_street_address'),
        event_city: citySel ? citySel.value : formData.get('event_city'),
        event_state: stateSel ? stateSel.value : formData.get('event_state'),
        event_country: countrySel ? countrySel.value : formData.get('event_country'),
        event_postcode: postcodeInput ? postcodeInput.value : formData.get('event_postcode'),
        event_organizer_name: formData.get('event_organizer_name'),
        event_organizer_email: formData.get('event_organizer_email'),
        event_rsvp_enabled: formData.has('event_rsvp_enabled') ? '1' : '0',
        event_rsvp_limit: formData.get('event_rsvp_limit'),
        event_waitlist_enabled: formData.has('event_waitlist_enabled') ? '1' : '0',
        event_featured: formData.has('event_featured') ? '1' : '0',
        image_ids: imageIds
      };
      if (bannerFile) {
        const bannerId = await uploadMedia(bannerFile);
        submission.event_banner_id = bannerId;
      }
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
