(function (element, apiFetch) {
  'use strict';

  function _arrayLikeToArray(r, a) {
    (null == a || a > r.length) && (a = r.length);
    for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
    return n;
  }
  function _arrayWithHoles(r) {
    if (Array.isArray(r)) return r;
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
  function _slicedToArray(r, e) {
    return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest();
  }
  function _unsupportedIterableToArray(r, a) {
    if (r) {
      if ("string" == typeof r) return _arrayLikeToArray(r, a);
      var t = {}.toString.call(r).slice(8, -1);
      return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
    }
  }

  apiFetch.use(apiFetch.createNonceMiddleware(ArtPulseOrgRoles.nonce));
  var LoadingSpinner = function LoadingSpinner() {
    return /*#__PURE__*/React.createElement("p", {
      className: "ap-org-roles-loading"
    }, "Loading\u2026");
  };
  var ErrorMessage = function ErrorMessage(_ref) {
    var message = _ref.message;
    return /*#__PURE__*/React.createElement("p", {
      className: "ap-org-roles-error",
      style: {
        color: 'red'
      }
    }, "Error: ", message);
  };
  var RoleTableRow = function RoleTableRow(_ref2) {
    var _role$user_count;
    var role = _ref2.role;
    return /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, role.label), /*#__PURE__*/React.createElement("td", null, role.description || ''), /*#__PURE__*/React.createElement("td", {
      style: {
        textAlign: 'center'
      }
    }, (_role$user_count = role.user_count) !== null && _role$user_count !== void 0 ? _role$user_count : 0));
  };
  function OrgRolesPanel() {
    var _useState = element.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      roles = _useState2[0],
      setRoles = _useState2[1];
    var _useState3 = element.useState(''),
      _useState4 = _slicedToArray(_useState3, 2),
      error = _useState4[0],
      setError = _useState4[1];
    element.useEffect(function () {
      apiFetch({
        path: ArtPulseOrgRoles.api_url
      }).then(setRoles)["catch"](function (e) {
        return setError(e.message);
      });
    }, []);
    if (error) {
      return /*#__PURE__*/React.createElement(ErrorMessage, {
        message: error
      });
    }
    if (!roles) {
      return /*#__PURE__*/React.createElement(LoadingSpinner, null);
    }
    return /*#__PURE__*/React.createElement("table", {
      className: "widefat striped"
    }, /*#__PURE__*/React.createElement("thead", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Role Name"), /*#__PURE__*/React.createElement("th", null, "Description"), /*#__PURE__*/React.createElement("th", null, "Members"))), /*#__PURE__*/React.createElement("tbody", null, roles.map(function (r) {
      return /*#__PURE__*/React.createElement(RoleTableRow, {
        key: r.key,
        role: r
      });
    })));
  }
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof ArtPulseOrgRoles === 'undefined') {
      console.error('ArtPulseOrgRoles not localised');
      return;
    }
    var root = document.getElementById('ap-org-roles-root');
    if (!root) return;
    element.render(/*#__PURE__*/React.createElement(OrgRolesPanel, null), root);
  });

})(wp.element, wp.apiFetch);
