(() => {
    const { createElement, render, useEffect, useState } = wp.element;
    const apiFetch = wp.apiFetch;

    apiFetch.use(apiFetch.createNonceMiddleware(ArtPulseOrgRoles.nonce));

    function OrgRolesMatrix() {
        const [roles, setRoles] = useState([]);
        const [users, setUsers] = useState([]);
        const [matrix, setMatrix] = useState({});

        const base = `${ArtPulseOrgRoles.base}/orgs/${ArtPulseOrgRoles.orgId}/roles`;

        useEffect(() => {
            apiFetch({ path: base })
                .then((list) => {
                    setUsers(list);
                    setRoles(['admin', 'editor', 'curator', 'promoter']);
                    const m = {};
                    list.forEach((row) => {
                        m[row.user_id] = { [row.role]: true };
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
                path: base,
                method: 'POST',
                data: { user_id: uid, role },
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
