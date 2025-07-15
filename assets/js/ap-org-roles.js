(() => {
    const { createElement, render, useEffect, useState } = wp.element;
    const apiFetch = wp.apiFetch;

    apiFetch.use(apiFetch.createNonceMiddleware(ArtPulseOrgRoles.nonce));

    function OrgRolesMatrix() {
        const [roles, setRoles] = useState([]);
        const [users, setUsers] = useState([]);
        const [matrix, setMatrix] = useState({});

        useEffect(() => {
            apiFetch({ path: ArtPulseOrgRoles.api_url })
                .then(setRoles)
                .catch(() => {});
            apiFetch({ path: ArtPulseOrgRoles.api_url + '/users' })
                .then((list) => {
                    setUsers(list);
                    const m = {};
                    list.forEach((u) => {
                        m[u.ID] = {};
                        (u.roles || []).forEach((r) => {
                            m[u.ID][r] = true;
                        });
                    });
                    setMatrix(m);
                })
                .catch(() => {});
        }, []);

        const toggle = (uid, role) => {
            const checked = !matrix[uid]?.[role];
            const current = { ...(matrix[uid] || {}) };
            current[role] = checked;
            const newMatrix = { ...matrix, [uid]: current };
            setMatrix(newMatrix);
            apiFetch({
                path: ArtPulseOrgRoles.api_url + '/assign',
                method: 'POST',
                data: { user_id: uid, roles: Object.keys(current).filter((k) => current[k]) },
            });
        };

        if (!roles.length || !users.length) {
            return createElement('p', null, 'Loading…');
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
                    createElement('th', null, 'User'),
                    roles.map((r) => createElement('th', { key: r.key }, r.label))
                )
            ),
            createElement(
                'tbody',
                null,
                users.map((u) =>
                    createElement(
                        'tr',
                        { key: u.ID },
                        createElement('td', null, u.display_name),
                        roles.map((r) =>
                            createElement('td', { key: r.key, style: { textAlign: 'center' } },
                                createElement('input', {
                                    type: 'checkbox',
                                    checked: matrix[u.ID]?.[r.key] || false,
                                    onChange: () => toggle(u.ID, r.key),
                                })
                            )
                        )
                    )
                )
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
