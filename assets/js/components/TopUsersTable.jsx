import React from 'react';
const { __ } = wp.i18n;

export default function TopUsersTable({ users = [] }) {
  return (
    <table className="min-w-full text-sm">
      <thead>
        <tr>
          <th className="text-left">{__('User', 'artpulse')}</th>
          <th className="text-right">{__('Count', 'artpulse')}</th>
        </tr>
      </thead>
      <tbody>
        {users.map(u => (
          <tr key={u.user_id} className="border-b">
            <td>{u.user_id}</td>
            <td className="text-right">{u.c}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}
