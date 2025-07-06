var APSidebarMenu = (function (React, Icons) {
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

  return SidebarMenu;

})(React, lucideReact);
