document.addEventListener('DOMContentLoaded', () => {
    if (typeof ArtPulseOrgRoles === 'undefined') {
        console.error('ArtPulseOrgRoles not localised');
        return;
    }

    const root = document.getElementById('ap-org-roles-root');
    if (!root) return;

    root.innerHTML = '<p>Loading organization roles...</p>';

    fetch(ArtPulseOrgRoles.api_url, {
        headers: {
            'X-WP-Nonce': ArtPulseOrgRoles.nonce
        }
    })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            root.innerHTML = `<ul>${data.map(role => `<li>${role.label}</li>`).join('')}</ul>`;
        })
        .catch(err => {
            root.innerHTML = `<p>Error: ${err.message}</p>`;
        });
});
