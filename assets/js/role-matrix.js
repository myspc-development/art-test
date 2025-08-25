(document => {
  const state = new Map(); // key userId|role -> checked

  function markDirty(saveBtn, chip) {
    const dirty = state.size > 0;
    saveBtn.disabled = !dirty;
    chip.hidden = !dirty;
  }

  function recordChange(cb, saveBtn, chip) {
    const key = cb.dataset.userId + '|' + cb.dataset.role;
    const original = cb.dataset.original === '1';
    if (cb.checked === original) {
      state.delete(key);
    } else {
      state.set(key, cb.checked);
    }
    markDirty(saveBtn, chip);
  }

  function visible(el) {
    return el.offsetParent !== null;
  }

  function bulkToggle(items, saveBtn, chip) {
    const shouldCheck = items.some(cb => !cb.checked);
    items.forEach(cb => {
      cb.checked = shouldCheck;
      recordChange(cb, saveBtn, chip);
    });
  }

  function setupFilter(table, input) {
    const headers = Array.from(table.querySelectorAll('thead th')).slice(1);
    input.addEventListener('input', () => {
      const term = input.value.toLowerCase();
      const colMatches = headers.map(th => th.textContent.toLowerCase().includes(term));
      const anyCol = colMatches.some(Boolean);

      headers.forEach((th, idx) => {
        const show = !term || colMatches[idx];
        th.style.display = show ? '' : 'none';
        table.querySelectorAll(`tbody tr td:nth-child(${idx + 2})`).forEach(td => {
          td.style.display = show ? '' : 'none';
        });
      });

      table.querySelectorAll('tbody tr').forEach(tr => {
        const rowHeader = tr.querySelector('th');
        const match = rowHeader.textContent.toLowerCase().includes(term);
        tr.style.display = term && !match && anyCol ? 'none' : '';
      });
    });
  }

  function showToast(toast, msg, isError = false) {
    if (!toast) return;
    toast.textContent = msg;
    toast.classList.toggle('error', isError);
    setTimeout(() => {
      toast.textContent = '';
    }, 3000);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('.ap-role-matrix table');
    if (!table) return;

    const saveBtn = document.getElementById('ap-role-save');
    const chip = document.getElementById('ap-unsaved-chip');
    const toast = document.getElementById('ap-role-toast');
    const filterInput = document.getElementById('ap-role-filter');

    table.querySelectorAll('.ap-role-toggle').forEach(cb => {
      cb.addEventListener('change', () => recordChange(cb, saveBtn, chip));
    });

    document.querySelectorAll('.ap-col-toggle').forEach(btn => {
      btn.addEventListener('click', () => {
        const idx = Array.from(btn.closest('tr').children).indexOf(btn.parentElement);
        const items = Array.from(table.querySelectorAll(`tbody tr`))
          .filter(visible)
          .map(tr => tr.children[idx].querySelector('.ap-role-toggle'))
          .filter(cb => cb && visible(cb));
        bulkToggle(items, saveBtn, chip);
      });
    });

    document.querySelectorAll('.ap-row-toggle').forEach(btn => {
      btn.addEventListener('click', () => {
        const tr = table.querySelector(`tbody tr[data-user-id="${btn.dataset.row}"]`);
        const items = Array.from(tr.querySelectorAll('.ap-role-toggle')).filter(visible);
        bulkToggle(items, saveBtn, chip);
      });
    });

    setupFilter(table, filterInput);

    saveBtn.addEventListener('click', async () => {
      const payload = Array.from(state.entries()).map(([key, val]) => {
        const [user_id, role] = key.split('|');
        return { user_id, role, checked: val };
      });
      try {
        const res = await fetch(AP_RoleMatrix.rest_url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': AP_RoleMatrix.nonce
          },
          body: JSON.stringify({ changes: payload })
        });
        if (res.ok) {
          state.clear();
          markDirty(saveBtn, chip);
          showToast(toast, 'Saved');
        } else {
          showToast(toast, 'Error', true);
        }
      } catch (e) {
        showToast(toast, 'Error', true);
      }
    });

    window.addEventListener('beforeunload', e => {
      if (state.size > 0) {
        e.preventDefault();
        e.returnValue = '';
      }
    });
  });
})(document);
