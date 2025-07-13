(function () {
    'use strict';

    if (typeof ArtPulseOrgRoles === 'undefined') {
        console.error('ArtPulseOrgRoles not localised');
        return;
    }

    const { api_url, nonce } = ArtPulseOrgRoles;

    async function loadOrgRoles() {
        try {
            const res = await fetch(api_url, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': nonce || ''
                }
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();
            const roles = json.roles || json;
            if (!Array.isArray(roles)) {
                throw new Error('Invalid data format');
            }
            renderRoles(roles);
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
