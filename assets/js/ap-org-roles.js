import { render, useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

apiFetch.use(apiFetch.createNonceMiddleware(ArtPulseOrgRoles.nonce));

const LoadingSpinner = () => (
    <p className="ap-org-roles-loading">Loading…</p>
);

const ErrorMessage = ({ message }) => (
    <p className="ap-org-roles-error" style={{ color: 'red' }}>Error: {message}</p>
);

const RoleTableRow = ({ role }) => (
    <tr>
        <td>{role.label}</td>
        <td>{role.description || ''}</td>
        <td style={{ textAlign: 'center' }}>{role.user_count ?? 0}</td>
    </tr>
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
        return <ErrorMessage message={error} />;
    }

    if (!roles) {
        return <LoadingSpinner />;
    }

    return (
        <table className="widefat striped">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Members</th>
                </tr>
            </thead>
            <tbody>
                {roles.map((r) => (
                    <RoleTableRow key={r.key} role={r} />
                ))}
            </tbody>
        </table>
    );
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof ArtPulseOrgRoles === 'undefined') {
        console.error('ArtPulseOrgRoles not localised');
        return;
    }

    const root = document.getElementById('ap-org-roles-root');
    if (!root) return;

    render(<OrgRolesPanel />, root);
});
