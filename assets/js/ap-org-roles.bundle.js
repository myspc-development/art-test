(function () {
  'use strict';

  function _arrayLikeToArray(r, a) {
    (null == a || a > r.length) && (a = r.length);
    for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
    return n;
  }
  function _arrayWithHoles(r) {
    if (Array.isArray(r)) return r;
  }
  function _defineProperty(e, r, t) {
    return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: true,
      configurable: true,
      writable: true
    }) : e[r] = t, e;
  }
  function _iterableToArrayLimit(r, l) {
    var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
    if (null != t) {
      var e,
        n,
        i,
        u,
        a = [],
        f = true,
        o = false;
      try {
        if (i = (t = t.call(r)).next, 0 === l) ; else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0);
      } catch (r) {
        o = true, n = r;
      } finally {
        try {
          if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return;
        } finally {
          if (o) throw n;
        }
      }
      return a;
    }
  }
  function _nonIterableRest() {
    throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }
  function ownKeys(e, r) {
    var t = Object.keys(e);
    if (Object.getOwnPropertySymbols) {
      var o = Object.getOwnPropertySymbols(e);
      r && (o = o.filter(function (r) {
        return Object.getOwnPropertyDescriptor(e, r).enumerable;
      })), t.push.apply(t, o);
    }
    return t;
  }
  function _objectSpread2(e) {
    for (var r = 1; r < arguments.length; r++) {
      var t = null != arguments[r] ? arguments[r] : {};
      r % 2 ? ownKeys(Object(t), true).forEach(function (r) {
        _defineProperty(e, r, t[r]);
      }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) {
        Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
      });
    }
    return e;
  }
  function _slicedToArray(r, e) {
    return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest();
  }
  function _toPrimitive(t, r) {
    if ("object" != typeof t || !t) return t;
    var e = t[Symbol.toPrimitive];
    if (void 0 !== e) {
      var i = e.call(t, r);
      if ("object" != typeof i) return i;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return ("string" === r ? String : Number)(t);
  }
  function _toPropertyKey(t) {
    var i = _toPrimitive(t, "string");
    return "symbol" == typeof i ? i : i + "";
  }
  function _unsupportedIterableToArray(r, a) {
    if (r) {
      if ("string" == typeof r) return _arrayLikeToArray(r, a);
      var t = {}.toString.call(r).slice(8, -1);
      return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
    }
  }

  (function () {
    var _wp$element = wp.element,
      createElement = _wp$element.createElement,
      render = _wp$element.render,
      useEffect = _wp$element.useEffect,
      useState = _wp$element.useState;
    var apiFetch = wp.apiFetch;
    apiFetch.use(apiFetch.createNonceMiddleware(ArtPulseOrgRoles.nonce));
    function OrgRolesMatrix() {
      var _useState = useState([]),
        _useState2 = _slicedToArray(_useState, 2),
        roles = _useState2[0],
        setRoles = _useState2[1];
      var _useState3 = useState([]),
        _useState4 = _slicedToArray(_useState3, 2),
        users = _useState4[0],
        setUsers = _useState4[1];
      var _useState5 = useState({}),
        _useState6 = _slicedToArray(_useState5, 2),
        changes = _useState6[0],
        setChanges = _useState6[1];
      useEffect(function () {
        fetch("/wp-json/artpulse/v1/org-roles?org_id=".concat(ArtPulseOrgRoles.orgId)).then(function (res) {
          return res.json();
        }).then(function (data) {
          setRoles(data.roles);
          setUsers(data.users);
        });
      }, []);
      var updateMatrix = function updateMatrix(uid, role) {
        setChanges(function (prev) {
          return _objectSpread2(_objectSpread2({}, prev), {}, _defineProperty({}, uid, role));
        });
        setUsers(function (prev) {
          return prev.map(function (u) {
            return u.id === uid ? _objectSpread2(_objectSpread2({}, u), {}, {
              role: role
            }) : u;
          });
        });
      };
      var saveChanges = function saveChanges() {
        fetch('/wp-json/artpulse/v1/org-roles/update', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.wpApiSettings.nonce
          },
          body: JSON.stringify({
            org_id: ArtPulseOrgRoles.orgId,
            roles: changes
          })
        });
      };
      if (!roles.length) {
        return createElement('p', null, 'Loading…');
      }
      return createElement('div', null, createElement('table', null, createElement('thead', null, createElement('tr', null, createElement('th', null, 'User'), roles.map(function (r) {
        return createElement('th', {
          key: r.slug
        }, r.name);
      }))), createElement('tbody', null, users.map(function (u) {
        return createElement('tr', {
          key: u.id
        }, createElement('td', null, u.name), roles.map(function (r) {
          return createElement('td', {
            key: r.slug
          }, createElement('input', {
            type: 'radio',
            checked: u.role === r.slug,
            onChange: function onChange() {
              return updateMatrix(u.id, r.slug);
            }
          }));
        }));
      }))), createElement('button', {
        onClick: saveChanges
      }, 'Save'));
    }
    document.addEventListener('DOMContentLoaded', function () {
      var root = document.getElementById('ap-org-roles-root');
      if (root) {
        render(createElement(OrgRolesMatrix), root);
      }
    });
  })();

})();
