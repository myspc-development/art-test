import { useState, useEffect } from "react";
import { CheckIcon, XMarkIcon } from "@heroicons/react/24/solid";

export default function RoleMatrix() {
  const [users, setUsers] = useState([]);
  const [roles, setRoles] = useState([]);
  const [matrix, setMatrix] = useState({});

  useEffect(() => {
    (async () => {
      const res = await fetch(AP_RoleMatrix.rest_seed);
      const json = await res.json();
      setUsers(json.users);
      setRoles(json.roles);
      setMatrix(json.matrix);
    })();
  }, []);

  const toggle = (u, r) =>
    setMatrix((m) => ({ ...m, [u]: { ...m[u], [r]: !m[u][r] } }));

  const saveAll = async () => {
    await fetch(AP_RoleMatrix.rest_batch, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": AP_RoleMatrix.nonce,
      },
      body: JSON.stringify(matrix),
    });
    alert("Roles saved!");
  };

  if (!users.length) return <p>Loading…</p>;

  return (
    <div className="space-y-4">
      <table className="min-w-full border text-sm">
        <thead>
          <tr>
            <th className="border p-2 text-left">User</th>
            {roles.map((role) => (
              <th key={role.key} className="border p-2" title={role.caps.join(", ")}> 
                {role.name}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {users.map((u) => (
            <tr key={u.ID}>
              <td className="border p-2">{u.display_name}</td>
              {roles.map((r) => (
                <td
                  key={r.key}
                  onClick={() => toggle(u.ID, r.key)}
                  className="border p-2 cursor-pointer text-center"
                >
                  {matrix[u.ID]?.[r.key] ? (
                    <CheckIcon className="mx-auto h-5 w-5 text-green-600" />
                  ) : (
                    <XMarkIcon className="mx-auto h-5 w-5 text-red-500" />
                  )}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>

      <button
        onClick={saveAll}
        className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
      >
        Save All
      </button>
    </div>
  );
}
