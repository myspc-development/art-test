import { useEffect, useState } from 'react';

export default function RoleMatrix({ selectedOrg = 0 }) {
  const [roles, setRoles] = useState([]);
  const [users, setUsers] = useState([]);
  const [changes, setChanges] = useState({});

  useEffect(() => {
    fetch(`/wp-json/artpulse/v1/org-roles?org_id=${selectedOrg}`)
      .then(res => res.json())
      .then(data => {
        setRoles(data.roles);
        setUsers(data.users);
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
        'X-WP-Nonce': window.wpApiSettings.nonce,
      },
      body: JSON.stringify({ org_id: selectedOrg, roles: changes }),
    });
  };

  if (!roles.length) return <p>Loading…</p>;

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
