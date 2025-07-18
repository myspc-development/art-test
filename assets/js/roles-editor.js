(function(){
  const { createElement, useState, useEffect, render } = wp.element;
  const { TextControl, Button, Spinner } = wp.components;
  const apiFetch = wp.apiFetch;

  if (window.ArtPulseRoles && ArtPulseRoles.apiNonce) {
    apiFetch.use(apiFetch.createNonceMiddleware(ArtPulseRoles.apiNonce));
  }

  function RolesEditor() {
    const [roles, setRoles] = useState([]);
    const [loading, setLoading] = useState(true);
    const [newRole, setNewRole] = useState({ key: '', label: '' });

    useEffect(() => {
      apiFetch({ path: ArtPulseRoles.restUrl }).then((r) => {
        setRoles(Array.isArray(r) ? r : []);
      }).finally(() => setLoading(false));
    }, []);

    const addRole = () => {
      apiFetch({
        path: ArtPulseRoles.restUrl,
        method: 'POST',
        data: { role_key: newRole.key, label: newRole.label },
      }).then((role) => {
        setRoles([...roles, { role_key: newRole.key, label: newRole.label }]);
        setNewRole({ key: '', label: '' });
      });
    };

    const deleteRole = (roleKey) => {
      apiFetch({
        path: ArtPulseRoles.restUrl + '/' + roleKey,
        method: 'DELETE',
      }).then(() => {
        setRoles(roles.filter((r) => r.role_key !== roleKey));
      });
    };

    if (loading) {
      return createElement(Spinner, null);
    }

    return createElement('div', null,
      createElement('h2', null, 'Roles Editor'),
      createElement('ul', null,
        roles.map((r) =>
          createElement('li', { key: r.role_key }, [
            createElement('strong', null, r.display_name || r.label || r.role_key),
            ' ',
            createElement(Button, { isDestructive: true, onClick: () => deleteRole(r.role_key) }, 'Delete')
          ])
        )
      ),
      createElement(TextControl, {
        label: 'Role Key',
        value: newRole.key,
        onChange: (val) => setNewRole({ ...newRole, key: val })
      }),
      createElement(TextControl, {
        label: 'Role Label',
        value: newRole.label,
        onChange: (val) => setNewRole({ ...newRole, label: val })
      }),
      createElement(Button, { isPrimary: true, onClick: addRole }, 'Add Role')
    );
  }

  document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('artpulse-roles-root');
    if (root) {
      render(createElement(RolesEditor), root);
    }
  });
})();
