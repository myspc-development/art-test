import React from 'react';

export default function TopUsersTable({ users = [] }) {
  return (
    <table className="min-w-full text-sm">
      <thead>
        <tr>
          <th className="text-left">User</th>
          <th className="text-right">Count</th>
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
