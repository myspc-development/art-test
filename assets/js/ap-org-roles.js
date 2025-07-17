(() => {
    const { createElement, render, useEffect, useState } = wp.element;
    const apiFetch = wp.apiFetch;

    apiFetch.use(apiFetch.createNonceMiddleware(ArtPulseOrgRoles.nonce));

    function OrgRolesMatrix() {
        const [roles, setRoles] = useState([]);
        const [users, setUsers] = useState([]);
        const [changes, setChanges] = useState({});

        useEffect(() => {
            apiFetch({
                path: `${ArtPulseOrgRoles.api_path}?org_id=${ArtPulseOrgRoles.orgId}`,
            })
                .then((data) => {
                    setRoles(Array.isArray(data.roles) ? data.roles : []);
                    setUsers(Array.isArray(data.users) ? data.users : []);
                })
                .catch((err) => {
                    console.error('Failed to load roles:', err);
                    setRoles([]);
                    setUsers([]);
                });
        }, []);

        const updateMatrix = (uid, role) => {
            setChanges((prev) => ({ ...prev, [uid]: role }));
            setUsers((prev) => prev.map((u) => (u.id === uid ? { ...u, role } : u)));
        };

        const saveChanges = () => {
            apiFetch({
                path: ArtPulseOrgRoles.api_path + '/update',
                method: 'POST',
                data: { org_id: ArtPulseOrgRoles.orgId, roles: changes },
            });
        };

        if (!Array.isArray(roles) || roles.length === 0) {
            return createElement('p', null, 'No roles loaded or unauthorized.');
        }

        return createElement(
            'div',
            null,
            createElement(
                'table',
                null,
                createElement(
                    'thead',
                    null,
                    createElement(
                        'tr',
                        null,
                        createElement('th', null, 'User'),
                        roles.map((r) => createElement('th', { key: r.slug }, r.name))
                    )
                ),
                createElement(
                    'tbody',
                    null,
                    users.map((u) =>
                        createElement(
                            'tr',
                            { key: u.id },
                            createElement('td', null, u.name),
                            roles.map((r) =>
                                createElement(
                                    'td',
                                    { key: r.slug },
                                    createElement('input', {
                                        type: 'radio',
                                        checked: u.role === r.slug,
                                        onChange: () => updateMatrix(u.id, r.slug),
                                    })
                                )
                            )
                        )
                    )
                )
            ),
            createElement(
                'button',
                { onClick: saveChanges },
                'Save'
            )
        );
    }

    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('ap-org-roles-root');
        if (root) {
            render(createElement(OrgRolesMatrix), root);
        }
    });
})();
