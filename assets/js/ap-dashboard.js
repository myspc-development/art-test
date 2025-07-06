(function (React, ReactDOM, Icons) {
  'use strict';

  function _interopNamespaceDefault(e) {
    var n = Object.create(null);
    if (e) {
      Object.keys(e).forEach(function (k) {
        if (k !== 'default') {
          var d = Object.getOwnPropertyDescriptor(e, k);
          Object.defineProperty(n, k, d.get ? d : {
            enumerable: true,
            get: function () { return e[k]; }
          });
        }
      });
    }
    n.default = e;
    return Object.freeze(n);
  }

  var Icons__namespace = /*#__PURE__*/_interopNamespaceDefault(Icons);

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

  var rolesMenus = {
    user: [{
      label: "Dashboard",
      icon: "Home",
      section: "dashboard"
    }, {
      label: "My Profile",
      icon: "User",
      section: "profile"
    }, {
      label: "Events",
      icon: "Calendar",
      section: "events"
    }, {
      label: "Favorites",
      icon: "Heart",
      section: "favorites"
    }, {
      label: "Forum",
      icon: "MessageCircle",
      section: "forum"
    }],
    artist: [{
      label: "Artist Dashboard",
      icon: "Palette",
      section: "dashboard"
    }, {
      label: "Edit Profile",
      icon: "User",
      section: "profile"
    }, {
      label: "My Artworks",
      icon: "Image",
      section: "artworks"
    }, {
      label: "Add Artwork",
      icon: "Plus",
      section: "add-artwork"
    }, {
      label: "My Events",
      icon: "Calendar",
      section: "events"
    }, {
      label: "My Collections",
      icon: "Folder",
      section: "collections"
    }, {
      label: "Forum",
      icon: "MessageCircle",
      section: "forum"
    }],
    organization: [{
      label: "Org Dashboard",
      icon: "Building2",
      section: "dashboard"
    }, {
      label: "Edit Org Profile",
      icon: "Edit",
      section: "profile"
    }, {
      label: "Members",
      icon: "Users",
      section: "members"
    }, {
      label: "Add Member",
      icon: "UserPlus",
      section: "add-member"
    }, {
      label: "Org Events",
      icon: "Calendar",
      section: "events"
    }, {
      label: "Add Event",
      icon: "Plus",
      section: "add-event"
    }, {
      label: "My Collections",
      icon: "Folder",
      section: "collections"
    }, {
      label: "Forum",
      icon: "MessageCircle",
      section: "forum"
    }],
    curator: [{
      label: "Curator Dashboard",
      icon: "LayoutGrid",
      section: "dashboard"
    }, {
      label: "My Collections",
      icon: "Folder",
      section: "collections"
    }, {
      label: "Forum",
      icon: "MessageCircle",
      section: "forum"
    }]
  };

  function SidebarMenu(_ref) {
    var role = _ref.role,
      activeSection = _ref.activeSection,
      setActiveSection = _ref.setActiveSection;
    var menu = rolesMenus[role] || [];
    return /*#__PURE__*/React.createElement("nav", {
      className: "ap-dashboard-sidebar"
    }, /*#__PURE__*/React.createElement("ul", null, menu.map(function (item) {
      var Icon = Icons__namespace[item.icon] || function () {
        return null;
      };
      var active = item.section === activeSection;
      return /*#__PURE__*/React.createElement("li", {
        key: item.section
      }, /*#__PURE__*/React.createElement("button", {
        type: "button",
        onClick: function onClick() {
          return setActiveSection(item.section);
        },
        className: "ap-sidebar-link ".concat(active ? 'active' : '')
      }, /*#__PURE__*/React.createElement(Icon, {
        className: "ap-icon"
      }), /*#__PURE__*/React.createElement("span", null, item.label)));
    })));
  }

  function DashboardApp(_ref) {
    var _rolesMenus$role, _rolesMenus$role$;
    var role = _ref.role;
    var _useState = React.useState(((_rolesMenus$role = rolesMenus[role]) === null || _rolesMenus$role === void 0 ? void 0 : (_rolesMenus$role$ = _rolesMenus$role[0]) === null || _rolesMenus$role$ === void 0 ? void 0 : _rolesMenus$role$.section) || ''),
      _useState2 = _slicedToArray(_useState, 2),
      activeSection = _useState2[0],
      setActiveSection = _useState2[1];
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-dashboard-wrapper"
    }, /*#__PURE__*/React.createElement(SidebarMenu, {
      role: role,
      activeSection: activeSection,
      setActiveSection: setActiveSection
    }), /*#__PURE__*/React.createElement("div", {
      className: "ap-dashboard-main"
    }, (rolesMenus[role] || []).map(function (item) {
      return /*#__PURE__*/React.createElement("section", {
        key: item.section,
        style: {
          display: activeSection === item.section ? 'block' : 'none'
        }
      }, /*#__PURE__*/React.createElement("div", {
        id: "ap-".concat(item.section)
      }));
    })));
  }
  document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('ap-dashboard-root');
    if (root) {
      ReactDOM.render(/*#__PURE__*/React.createElement(DashboardApp, {
        role: APDashboard.role
      }), root);
    }
  });

})(React, ReactDOM, lucideReact);
