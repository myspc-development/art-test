import { useEffect, useState } from 'react';

export default function RoleMatrix({ selectedOrg = 0 }) {
  const [roles, setRoles] = useState([]);
  const [users, setUsers] = useState([]);
  const [changes, setChanges] = useState({});

  useEffect(() => {
    fetch(`/wp-json/artpulse/v1/org-roles?org_id=${selectedOrg}`, {
      headers: {
        'X-WP-Nonce': ArtPulseData.rest_nonce,
      },
    })
      .then(res => {
        if (!res.ok) throw new Error(`API Error: ${res.status}`);
        return res.json();
      })
      .then(data => {
        if (!data || !Array.isArray(data.roles) || !Array.isArray(data.users)) {
          throw new Error('Invalid response format');
        }
        setRoles(data.roles);
        setUsers(data.users);
      })
      .catch(err => {
        console.error('Failed to load org roles:', err);
        setRoles([]);
        setUsers([]);
      });
  }, [selectedOrg]);

  function updateMatrix(userId, roleSlug) {
    setChanges(prev => ({ ...prev, [userId]: roleSlug }));
    setUsers(prev => prev.map(u => (u.id === userId ? { ...u, role: roleSlug } : u)));
  }

  const saveChanges = () => {
    fetch('/wp-json/artpulse/v1/org-roles/update', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': ArtPulseData.rest_nonce,
      },
      body: JSON.stringify({ org_id: selectedOrg, roles: changes }),
    });
  };

  if (!roles || !Array.isArray(roles) || roles.length === 0) {
    return <p>No roles found or unauthorized.</p>;
  }

  return (
    <div>
      <table>
        <thead>
          <tr>
            <th>User</th>
            {roles.map(role => (
              <th key={role.slug}>{role.name}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {users.map(user => (
            <tr key={user.id}>
              <td>{user.name}</td>
              {roles.map(role => (
                <td key={role.slug}>
                  <input
                    type="radio"
                    checked={user.role === role.slug}
                    onChange={() => updateMatrix(user.id, role.slug)}
                  />
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
      <button onClick={saveChanges}>Save</button>
    </div>
  );
}
