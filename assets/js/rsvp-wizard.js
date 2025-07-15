document.addEventListener('DOMContentLoaded', () => {
  const eventsList = document.getElementById('ap-org-events');
  if (!eventsList || typeof APRsvpWizard === 'undefined') return;

  let modal, stepIndex = 0;
  const fields = {
    rsvpEnabled: null,
    rsvpLimit: null,
    waitlistEnabled: null,
    organizerEmail: null,
    eventId: null,
  };

  function createModal() {
    if (modal) return;
    modal = document.createElement('div');
    modal.id = 'ap-rsvp-modal';
    modal.className = 'ap-org-modal';
    modal.innerHTML = `
      <div class="ap-rsvp-wrap">
        <button id="ap-rsvp-close" type="button" class="ap-form-button nectar-button">Close</button>
        <div id="ap-rsvp-msg" class="ap-form-messages" role="status" aria-live="polite"></div>
        <form id="ap-rsvp-form" class="ap-form-container" data-no-ajax="true">
          <input type="hidden" name="ap_event_id" id="ap_rsvp_event_id">
          <div class="ap-rsvp-step" data-step="0">
            <label class="ap-form-label"><input type="checkbox" name="ap_event_rsvp_enabled" value="1"> Enable RSVP</label>
            <label class="ap-form-label" for="ap_event_organizer_email">Organizer Email
              <input class="ap-input" id="ap_event_organizer_email" type="email" name="ap_event_organizer_email">
            </label>
            <p id="ap-rsvp-counts"></p>
            <button type="button" class="ap-next ap-form-button nectar-button">Next</button>
          </div>
          <div class="ap-rsvp-step" data-step="1">
            <label class="ap-form-label" for="ap_event_rsvp_limit">RSVP Limit
              <input class="ap-input" id="ap_event_rsvp_limit" type="number" name="ap_event_rsvp_limit">
            </label>
            <label class="ap-form-label"><input type="checkbox" name="ap_event_waitlist_enabled" value="1"> Enable Waitlist</label>
            <div class="ap-rsvp-actions">
              <button type="button" class="ap-prev ap-form-button nectar-button">Back</button>
              <button type="submit" class="ap-form-button nectar-button">Save</button>
            </div>
          </div>
        </form>
      </div>`;
    document.body.appendChild(modal);
    modal.querySelector('#ap-rsvp-close').addEventListener('click', () => modal.classList.remove('open'));
    modal.querySelector('.ap-next').addEventListener('click', () => showStep(1));
    modal.querySelector('.ap-prev').addEventListener('click', () => showStep(0));
    modal.querySelector('#ap-rsvp-form').addEventListener('submit', handleSubmit);
  }

  function showStep(i) {
    stepIndex = i;
    const steps = modal.querySelectorAll('.ap-rsvp-step');
    steps.forEach((s, idx) => { s.style.display = idx === i ? '' : 'none'; });
  }

  function loadEvent(eventId) {
    const form = modal.querySelector('#ap-rsvp-form');
    fetch(APRsvpWizard.ajax_url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'ap_get_org_event',
        nonce: APRsvpWizard.nonce,
        event_id: eventId
      })
    })
      .then(res => res.ok ? res.json() : null)
      .then(data => {
        if (!data || !data.success) return;
        fields.eventId = eventId;
        form.querySelector('#ap_rsvp_event_id').value = eventId;
        form.querySelector('[name="ap_event_rsvp_enabled"]').checked = data.data.ap_event_rsvp_enabled === '1';
        form.querySelector('#ap_event_rsvp_limit').value = data.data.ap_event_rsvp_limit || '';
        form.querySelector('[name="ap_event_waitlist_enabled"]').checked = data.data.ap_event_waitlist_enabled === '1';
        form.querySelector('#ap_event_organizer_email').value = data.data.ap_event_organizer_email || '';
      });

    fetch(`${APRsvpWizard.rest_root}artpulse/v1/event/${eventId}/attendees`, {
      headers: { 'X-WP-Nonce': APRsvpWizard.rest_nonce }
    })
      .then(res => res.ok ? res.json() : null)
      .then(data => {
        if (!data) return;
        const count = modal.querySelector('#ap-rsvp-counts');
        const r = data.attendees ? data.attendees.length : 0;
        const w = data.waitlist ? data.waitlist.length : 0;
        count.textContent = `Current RSVPs: ${r} | Waitlist: ${w}`;
      });
  }

  function handleSubmit(e) {
    e.preventDefault();
    const msg = modal.querySelector('#ap-rsvp-msg');
    msg.textContent = '';
    const formData = new FormData(e.target);
    formData.append('action', 'ap_update_org_event');
    if (!formData.has('nonce')) formData.append('nonce', APRsvpWizard.nonce);

    fetch(APRsvpWizard.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then(res => res.ok ? res.json() : null)
      .then(data => {
        if (!data) return;
        if (data.success) {
          msg.textContent = 'Saved';
          modal.classList.remove('open');
        } else {
          msg.textContent = data.data && data.data.message ? data.data.message : 'Error';
        }
      });
  }

  eventsList.addEventListener('click', e => {
    if (e.target.matches('.ap-config-rsvp')) {
      e.preventDefault();
      const id = e.target.dataset.id;
      if (!id) return;
      createModal();
      showStep(0);
      loadEvent(id);
      modal.classList.add('open');
    }
  });
});
