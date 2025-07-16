var APRoleMatrix = (function (react) {
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

  function RoleMatrix(_ref) {
    var _ref$selectedOrg = _ref.selectedOrg,
      selectedOrg = _ref$selectedOrg === void 0 ? 0 : _ref$selectedOrg;
    var _useState = react.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      roles = _useState2[0],
      setRoles = _useState2[1];
    var _useState3 = react.useState([]),
      _useState4 = _slicedToArray(_useState3, 2),
      users = _useState4[0],
      setUsers = _useState4[1];
    var _useState5 = react.useState({}),
      _useState6 = _slicedToArray(_useState5, 2),
      pendingRoles = _useState6[0],
      setPendingRoles = _useState6[1];
    react.useEffect(function () {
      fetch("/wp-json/artpulse/v1/org-roles?org_id=".concat(selectedOrg)).then(function (res) {
        return res.json();
      }).then(function (data) {
        setRoles(data.roles);
        setUsers(data.users);
      });
    }, [selectedOrg]);
    function assignRole(userId, roleSlug) {
      setPendingRoles(function (prev) {
        return _objectSpread2(_objectSpread2({}, prev), {}, _defineProperty({}, userId, roleSlug));
      });
      setUsers(function (prev) {
        return prev.map(function (u) {
          return u.id === userId ? _objectSpread2(_objectSpread2({}, u), {}, {
            role: roleSlug
          }) : u;
        });
      });
    }
    var saveChanges = function saveChanges() {
      fetch('/wp-json/artpulse/v1/org-roles/update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          org_id: selectedOrg,
          roles: pendingRoles
        })
      });
    };
    if (!roles.length) return /*#__PURE__*/React.createElement("p", null, "Loading\u2026");
    return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("table", null, /*#__PURE__*/React.createElement("thead", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "User"), roles.map(function (role) {
      return /*#__PURE__*/React.createElement("th", {
        key: role.slug
      }, role.name);
    }))), /*#__PURE__*/React.createElement("tbody", null, users.map(function (user) {
      return /*#__PURE__*/React.createElement("tr", {
        key: user.id
      }, /*#__PURE__*/React.createElement("td", null, user.name), roles.map(function (role) {
        return /*#__PURE__*/React.createElement("td", {
          key: role.slug
        }, /*#__PURE__*/React.createElement("input", {
          type: "radio",
          checked: user.role === role.slug,
          onChange: function onChange() {
            return assignRole(user.id, role.slug);
          }
        }));
      }));
    }))), /*#__PURE__*/React.createElement("button", {
      onClick: saveChanges
    }, "Save"));
  }

  return RoleMatrix;

})(React);
