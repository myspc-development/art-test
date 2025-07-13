(function () {
    'use strict';

    if (typeof ArtPulseOrgRoles === 'undefined') {
        console.error('ArtPulseOrgRoles not localised');
        return;
    }

    const { ajax_url, nonce, user_id } = ArtPulseOrgRoles;

    async function loadOrgRoles() {
        try {
            const res = await fetch(ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ap_get_org_roles',
                    nonce,
                    user_id,
                }),
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();
            if (!json.success || !Array.isArray(json.data.roles)) {
                throw new Error(json.data || 'Invalid data format');
            }
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
