var APDashboardApp = (function (React$1, ReactDOM) {
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

  function DashboardNavbar(_ref) {
    var userRole = _ref.userRole,
      onLogout = _ref.onLogout;
    return /*#__PURE__*/React.createElement("nav", {
      className: "bg-white border-b shadow-sm px-4 py-2 flex justify-between items-center"
    }, /*#__PURE__*/React.createElement("div", {
      className: "text-lg font-bold"
    }, "ArtPulse Dashboard"), /*#__PURE__*/React.createElement("div", {
      className: "flex gap-4 items-center"
    }, userRole === 'artist' && /*#__PURE__*/React.createElement("a", {
      href: "#/artist",
      className: "text-blue-600"
    }, "Artist"), userRole === 'organization' && /*#__PURE__*/React.createElement("a", {
      href: "#/org",
      className: "text-blue-600"
    }, "Organization"), userRole === 'member' && /*#__PURE__*/React.createElement("a", {
      href: "#/member",
      className: "text-blue-600"
    }, "Member"), /*#__PURE__*/React.createElement("button", {
      onClick: onLogout,
      className: "bg-red-500 text-white px-3 py-1 rounded"
    }, "Logout")));
  }

  function MessagesPanel() {
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      messages = _useState2[0],
      setMessages = _useState2[1];
    React$1.useEffect(function () {
      fetch('/wp-json/artpulse/v1/dashboard/messages').then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      }).then(setMessages).catch(function (err) {
        return console.error('Messages load error:', err);
      });
    }, []);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-widget bg-white p-4 rounded shadow mb-4"
    }, messages.map(function (msg) {
      return /*#__PURE__*/React$1.createElement("p", {
        key: msg.id
      }, msg.content);
    }));
  }

  function AppDashboard() {
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      role = _useState2[0],
      setRole = _useState2[1];
    React$1.useEffect(function () {
      fetch('/wp-json/artpulse/v1/me').then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      }).then(function (data) {
        return setRole(data.role);
      }).catch(function (err) {
        return console.error('Profile fetch error:', err);
      });
    }, []);
    var logout = function logout() {
      return window.location.href = '/wp-login.php?action=logout';
    };
    return /*#__PURE__*/React$1.createElement("div", {
      className: "min-h-screen bg-gray-100"
    }, /*#__PURE__*/React$1.createElement(DashboardNavbar, {
      userRole: role,
      onLogout: logout
    }), /*#__PURE__*/React$1.createElement("main", {
      className: "p-4"
    }, /*#__PURE__*/React$1.createElement(MessagesPanel, null)));
  }
  document.addEventListener('DOMContentLoaded', function () {
    var rootEl = document.getElementById('ap-dashboard-root');
    if (rootEl && window.ReactDOM) {
      ReactDOM.render(/*#__PURE__*/React$1.createElement(AppDashboard, null), rootEl);
    }
  });

  return AppDashboard;

})(React, ReactDOM);
