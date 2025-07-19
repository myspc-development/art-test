import React from 'react';

export default function RoleSelector({ roles = [], value, onChange }) {
  return (
    <select value={value} onChange={e => onChange(e.target.value)}>
      <option value="">Select Role</option>
      {roles.map(role => (
        <option key={role} value={role}>
          {role}
        </option>
      ))}
    </select>
  );
}
