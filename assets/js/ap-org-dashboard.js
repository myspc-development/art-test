document.addEventListener('DOMContentLoaded', () => {
  const addBtn = document.getElementById('ap-add-event-btn');
  addBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    if (APOrgDashboard.eventFormUrl) {
      window.location.href = APOrgDashboard.eventFormUrl;
    }
  });

  const eventsContainer = document.getElementById('ap-org-events');
  eventsContainer?.addEventListener('click', function (e) {
    if (e.target.matches('.ap-delete-event')) {
      const eventId = e.target.dataset.id;
      if (!confirm('Delete this event?')) return;

      fetch(APOrgDashboard.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'ap_delete_org_event',
          nonce: APOrgDashboard.nonce,
          event_id: eventId
        })
      })
      .then(res => res.ok ? res.json() : Promise.reject('Request failed'))
      .then(data => {
        if (data.success) {
          eventsContainer.innerHTML = data.data.updated_list_html;
        } else {
          alert(data.data.message || 'Failed to delete.');
        }
      });
    }
  });
});
