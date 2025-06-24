document.addEventListener('DOMContentLoaded', () => {
  const inviteForm = document.getElementById('ap-org-invite-form');
  const csvInput = document.getElementById('ap-invite-csv');
  const emailInput = document.getElementById('ap-invite-emails');
  const userForm = document.getElementById('ap-org-user-list');
  const selectAll = document.getElementById('ap-select-all');

  inviteForm?.addEventListener('submit', e => {
    e.preventDefault();
    const emails = [];
    if (csvInput && csvInput.files.length) {
      const reader = new FileReader();
      reader.onload = () => {
        parseEmails(reader.result).forEach(e => emails.push(e));
        sendInvites(emails);
      };
      reader.readAsText(csvInput.files[0]);
    } else if (emailInput) {
      parseEmails(emailInput.value).forEach(e => emails.push(e));
      sendInvites(emails);
    }
  });

  function parseEmails(text) {
    return text
      .split(/[\n,;]+/)
      .map(t => t.trim())
      .filter(t => t.length);
  }

  function sendInvites(emails) {
    if (!emails.length) return;
    fetch(`${APOrgUserManager.apiRoot}artpulse/v1/org/${APOrgUserManager.orgId}/invite`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': APOrgUserManager.nonce
      },
      body: JSON.stringify({ emails })
    }).then(() => {
      if (emailInput) emailInput.value = '';
      if (csvInput) csvInput.value = '';
    });
  }

  userForm?.addEventListener('submit', e => {
    e.preventDefault();
    const ids = Array.from(userForm.querySelectorAll('.ap-user-select:checked')).map(el => el.value);
    const action = document.getElementById('ap-org-bulk-action').value;
    if (!ids.length || !action) return;
    fetch(`${APOrgUserManager.apiRoot}artpulse/v1/org/${APOrgUserManager.orgId}/users/batch`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': APOrgUserManager.nonce
      },
      body: JSON.stringify({ action, user_ids: ids })
    }).then(() => window.location.reload());
  });

  selectAll?.addEventListener('change', () => {
    userForm.querySelectorAll('.ap-user-select').forEach(cb => {
      cb.checked = selectAll.checked;
    });
  });
});
