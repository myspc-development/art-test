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
        matrix = _useState6[0],
        setMatrix = _useState6[1];
      var base = "".concat(ArtPulseOrgRoles.base, "/orgs/").concat(ArtPulseOrgRoles.orgId, "/roles");
      useEffect(function () {
        apiFetch({
          path: base
        }).then(function (list) {
          setUsers(list);
          setRoles(['admin', 'editor', 'curator', 'promoter']);
          var m = {};
          list.forEach(function (row) {
            m[row.user_id] = _defineProperty({}, row.role, true);
          });
          setMatrix(m);
        })["catch"](function () {});
      }, []);
      var toggle = function toggle(uid, role) {
        var _matrix$uid;
        var checked = !((_matrix$uid = matrix[uid]) !== null && _matrix$uid !== void 0 && _matrix$uid[role]);
        var current = _objectSpread2({}, matrix[uid] || {});
        current[role] = checked;
        var newMatrix = _objectSpread2(_objectSpread2({}, matrix), {}, _defineProperty({}, uid, current));
        setMatrix(newMatrix);
        apiFetch({
          path: base,
          method: 'POST',
          data: {
            user_id: uid,
            role: role
          }
        });
      };
      if (!roles.length || !users.length) {
        return createElement('p', null, 'Loading…');
      }
      return createElement('table', {
        className: 'widefat striped'
      }, createElement('thead', null, createElement('tr', null, createElement('th', null, 'User'), roles.map(function (r) {
        return createElement('th', {
          key: r.key
        }, r.label);
      }))), createElement('tbody', null, users.map(function (u) {
        return createElement('tr', {
          key: u.ID
        }, createElement('td', null, u.display_name), roles.map(function (r) {
          var _matrix$u$ID;
          return createElement('td', {
            key: r.key,
            style: {
              textAlign: 'center'
            }
          }, createElement('input', {
            type: 'checkbox',
            checked: ((_matrix$u$ID = matrix[u.ID]) === null || _matrix$u$ID === void 0 ? void 0 : _matrix$u$ID[r.key]) || false,
            onChange: function onChange() {
              return toggle(u.ID, r.key);
            }
          }));
        }));
      })));
    }
    document.addEventListener('DOMContentLoaded', function () {
      var root = document.getElementById('ap-org-roles-root');
      if (root) {
        render(createElement(OrgRolesMatrix), root);
      }
    });
  })();

})();
