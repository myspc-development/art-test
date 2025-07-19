var APWidgetMatrix = (function (React, client) {
  'use strict';

  function AdminWidgetMatrix() {
    const _React = React,
      useState = _React.useState,
      useEffect = _React.useEffect;

    const [widgets, setWidgets] = useState([]);
    const [roles, setRoles] = useState([]);
    const [matrix, setMatrix] = useState({});
    const restRoot =
      (window.APWidgetMatrix && window.APWidgetMatrix.root) ||
      (window.wpApiSettings && window.wpApiSettings.root) ||
      '/wp-json/';
    const nonce =
      (window.APWidgetMatrix && window.APWidgetMatrix.nonce) ||
      (window.wpApiSettings && window.wpApiSettings.nonce) ||
      '';

    useEffect(function () {
      fetch(restRoot + 'artpulse/v1/widgets')
        .then(function (r) {
          return r.json();
        })
        .then(setWidgets);
      fetch(restRoot + 'artpulse/v1/roles')
        .then(function (r) {
          return r.json();
        })
        .then(setRoles);
      fetch(restRoot + 'artpulse/v1/dashboard-config', {
        headers: { 'X-WP-Nonce': nonce }
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          return setMatrix(data.widget_roles || {});
        });
    }, []);

    const toggle = function (wid, role) {
      setMatrix(function (m) {
        const list = new Set(m[wid] || []);
        if (list.has(role)) list.delete(role);
        else list.add(role);
        const obj = Object.assign({}, m);
        obj[wid] = Array.from(list);
        return obj;
      });
    };

    const save = function () {
      fetch(restRoot + 'artpulse/v1/dashboard-config', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
        body: JSON.stringify({ widget_roles: matrix })
      }).then(function () {
        if (window.wp && window.wp.data && window.wp.data.dispatch) {
          window.wp.data
            .dispatch('core/notices')
            .createNotice('success', 'Saved', { isDismissible: true });
        }
      });
    };

    return React.createElement(
      'div',
      null,
      React.createElement(
        'table',
        { className: 'widefat striped' },
        React.createElement(
          'thead',
          null,
          React.createElement(
            'tr',
            null,
            React.createElement('th', null, 'Widget'),
            roles.map(function (r) {
              return React.createElement('th', { key: r }, r);
            })
          )
        ),
        React.createElement(
          'tbody',
          null,
          widgets.map(function (w) {
            return React.createElement(
              'tr',
              { key: w.id },
              React.createElement('td', null, w.name || w.id),
              roles.map(function (role) {
                return React.createElement(
                  'td',
                  { key: role, style: { textAlign: 'center' } },
                  React.createElement('input', {
                    type: 'checkbox',
                    checked: (matrix[w.id] || w.roles || []).includes(role),
                    onChange: function () {
                      return toggle(w.id, role);
                    }
                  })
                );
              })
            );
          })
        )
      ),
      React.createElement(
        'p',
        null,
        React.createElement(
          'button',
          { type: 'button', className: 'button button-primary', onClick: save },
          'Save'
        )
      )
    );
  }

  document.addEventListener('DOMContentLoaded', function () {
    var rootEl = document.getElementById('ap-widget-matrix-root');
    if (rootEl) {
      var root = client.createRoot(rootEl);
      root.render(React.createElement(AdminWidgetMatrix));
    }
  });
});

(React, ReactDOM);
