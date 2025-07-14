document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.ap-role-toggle').forEach(box => {
    box.addEventListener('change', async () => {
      const tr = box.closest('tr');
      const userId = tr.dataset.userId;
      const role = box.dataset.role;
      const checked = box.checked;
      box.disabled = true;

      try {
        const res = await fetch(AP_RoleMatrix.rest_url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': AP_RoleMatrix.nonce
          },
          body: JSON.stringify({ user_id: userId, role: role, checked: checked })
        });

        if (res.ok) {
          box.classList.remove('error');
        } else {
          box.classList.add('error');
        }
      } catch (e) {
        box.classList.add('error');
      }

      box.disabled = false;
    });
  });
});
