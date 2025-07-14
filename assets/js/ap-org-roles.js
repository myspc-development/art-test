(() => {
    const { createElement, render, useEffect, useState } = wp.element;
    const apiFetch = wp.apiFetch;

    apiFetch.use(apiFetch.createNonceMiddleware(ArtPulseOrgRoles.nonce));

    const LoadingSpinner = () =>
        createElement('p', { className: 'ap-org-roles-loading' }, 'Loading…');

    const ErrorMessage = ({ message }) =>
        createElement(
            'p',
            { className: 'ap-org-roles-error', style: { color: 'red' } },
            'Error: ',
            message
        );

    const RoleTableRow = ({ role }) =>
        createElement(
            'tr',
            null,
            createElement('td', null, role.label),
            createElement('td', null, role.description || ''),
            createElement(
                'td',
                { style: { textAlign: 'center' } },
                role.user_count ?? 0
            )
        );

    function OrgRolesPanel() {
        const [roles, setRoles] = useState(null);
        const [error, setError] = useState('');

        useEffect(() => {
            apiFetch({ path: ArtPulseOrgRoles.api_url })
                .then(setRoles)
                .catch((e) => setError(e.message));
        }, []);

        if (error) {
            return createElement(ErrorMessage, { message: error });
        }

        if (!roles) {
            return createElement(LoadingSpinner);
        }

        return createElement(
            'table',
            { className: 'widefat striped' },
            createElement(
                'thead',
                null,
                createElement(
                    'tr',
                    null,
                    createElement('th', null, 'Role Name'),
                    createElement('th', null, 'Description'),
                    createElement('th', null, 'Members')
                )
            ),
            createElement(
                'tbody',
                null,
                roles.map((r) =>
                    createElement(RoleTableRow, { key: r.key, role: r })
                )
            )
        );
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof ArtPulseOrgRoles === 'undefined') {
            console.error('ArtPulseOrgRoles not localised');
            return;
        }

        const root = document.getElementById('ap-org-roles-root');
        if (!root) return;

        render(createElement(OrgRolesPanel), root);
    });
})();
