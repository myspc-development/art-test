document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.ap-form-container');
  const messageBox = document.querySelector('.ap-form-messages');

  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    const submission = {};

    // ✅ Required fields
    submission.post_type = form.dataset.postType || 'artpulse_event';
    submission.title = formData.get('event_title') || formData.get('title');

    // 🚨 Skip submission if required fields are missing
    if (!submission.title || !submission.post_type) {
      if (messageBox) messageBox.textContent = 'Title is required.';
      return;
    }

    // Optional fields (send only if available)
    const optionalFields = [
      'artist_name', 'ead_org_name',
      'event_date', 'event_start_date', 'event_end_date',
      'venue_name', 'event_location', 'event_street_address', 'event_city'
    ];

    optionalFields.forEach(key => {
      const val = formData.get(key);
      if (val) submission[key] = val;
    });

    try {
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
      } else {
        if (messageBox) {
          messageBox.textContent = data.message || 'Submission failed.';
        }
      }
    } catch (err) {
      console.error('Submission error:', err);
      if (messageBox) messageBox.textContent = 'An unexpected error occurred.';
    }
  });
});
