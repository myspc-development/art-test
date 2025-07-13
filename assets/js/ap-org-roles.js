(function () {
    'use strict';

    if (typeof ArtPulseOrgRoles === 'undefined') {
        console.error('ArtPulseOrgRoles not localised');
        return;
    }

    const { ajax_url, nonce, user_id } = ArtPulseOrgRoles;

    async function loadOrgRoles() {
        try {
            const formData = new FormData();
            formData.append('action', 'ap_get_org_roles');
            formData.append('nonce', nonce);
            formData.append('user_id', user_id);

            const res = await fetch(ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData,
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();
            if (!json.success) throw new Error(json.data || 'Unknown error');

            renderRoles(json.data.roles);
        } catch (err) {
            console.error('OrgRoles AJAX failed:', err);
            renderError(err.message);
        }
    }

    function renderRoles(roles) {
        const list = document.getElementById('ap-org-roles-list');
        if (!list) return;

        list.innerHTML = roles
            .map((r) => `<li data-role-id="${r.id}">${r.name}</li>`)
            .join('');
    }

    function renderError(msg) {
        const list = document.getElementById('ap-org-roles-list');
        if (!list) return;
        list.innerHTML = `<li class="error">${msg}</li>`;
    }

    document.addEventListener('DOMContentLoaded', loadOrgRoles);
})();
