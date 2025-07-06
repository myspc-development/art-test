var APSidebarMenu = (function (React) {
  'use strict';

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

  function _arrayLikeToArray(r, a) {
    (null == a || a > r.length) && (a = r.length);
    for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
    return n;
  }
  function _arrayWithHoles(r) {
    if (Array.isArray(r)) return r;
  }
  function _arrayWithoutHoles(r) {
    if (Array.isArray(r)) return _arrayLikeToArray(r);
  }
  function _defineProperty(e, r, t) {
    return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: true,
      configurable: true,
      writable: true
    }) : e[r] = t, e;
  }
  function _iterableToArray(r) {
    if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r);
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
  function _nonIterableSpread() {
    throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
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
  function _objectWithoutProperties(e, t) {
    if (null == e) return {};
    var o,
      r,
      i = _objectWithoutPropertiesLoose(e, t);
    if (Object.getOwnPropertySymbols) {
      var n = Object.getOwnPropertySymbols(e);
      for (r = 0; r < n.length; r++) o = n[r], -1 === t.indexOf(o) && {}.propertyIsEnumerable.call(e, o) && (i[o] = e[o]);
    }
    return i;
  }
  function _objectWithoutPropertiesLoose(r, e) {
    if (null == r) return {};
    var t = {};
    for (var n in r) if ({}.hasOwnProperty.call(r, n)) {
      if (-1 !== e.indexOf(n)) continue;
      t[n] = r[n];
    }
    return t;
  }
  function _slicedToArray(r, e) {
    return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest();
  }
  function _toConsumableArray(r) {
    return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread();
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

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var defaultAttributes = {
    xmlns: "http://www.w3.org/2000/svg",
    width: 24,
    height: 24,
    viewBox: "0 0 24 24",
    fill: "none",
    stroke: "currentColor",
    strokeWidth: 2,
    strokeLinecap: "round",
    strokeLinejoin: "round"
  };

  var _excluded = ["color", "size", "strokeWidth", "absoluteStrokeWidth", "className", "children"];
  var toKebabCase = function toKebabCase(string) {
    return string.replace(/([a-z0-9])([A-Z])/g, "$1-$2").toLowerCase().trim();
  };
  var createLucideIcon = function createLucideIcon(iconName, iconNode) {
    var Component = /*#__PURE__*/React.forwardRef(function (_ref, ref) {
      var _ref$color = _ref.color,
        color = _ref$color === void 0 ? "currentColor" : _ref$color,
        _ref$size = _ref.size,
        size = _ref$size === void 0 ? 24 : _ref$size,
        _ref$strokeWidth = _ref.strokeWidth,
        strokeWidth = _ref$strokeWidth === void 0 ? 2 : _ref$strokeWidth,
        absoluteStrokeWidth = _ref.absoluteStrokeWidth,
        _ref$className = _ref.className,
        className = _ref$className === void 0 ? "" : _ref$className,
        children = _ref.children,
        rest = _objectWithoutProperties(_ref, _excluded);
      return /*#__PURE__*/React.createElement("svg", _objectSpread2(_objectSpread2({
        ref: ref
      }, defaultAttributes), {}, {
        width: size,
        height: size,
        stroke: color,
        strokeWidth: absoluteStrokeWidth ? Number(strokeWidth) * 24 / Number(size) : strokeWidth,
        className: ["lucide", "lucide-".concat(toKebabCase(iconName)), className].join(" ")
      }, rest), [].concat(_toConsumableArray(iconNode.map(function (_ref2) {
        var _ref3 = _slicedToArray(_ref2, 2),
          tag = _ref3[0],
          attrs = _ref3[1];
        return /*#__PURE__*/React.createElement(tag, attrs);
      })), _toConsumableArray(Array.isArray(children) ? children : [children])));
    });
    Component.displayName = "".concat(iconName);
    return Component;
  };

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var Building2 = createLucideIcon("Building2", [["path", {
    d: "M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z",
    key: "1b4qmf"
  }], ["path", {
    d: "M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2",
    key: "i71pzd"
  }], ["path", {
    d: "M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2",
    key: "10jefs"
  }], ["path", {
    d: "M10 6h4",
    key: "1itunk"
  }], ["path", {
    d: "M10 10h4",
    key: "tcdvrf"
  }], ["path", {
    d: "M10 14h4",
    key: "kelpxr"
  }], ["path", {
    d: "M10 18h4",
    key: "1ulq68"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var Calendar = createLucideIcon("Calendar", [["path", {
    d: "M8 2v4",
    key: "1cmpym"
  }], ["path", {
    d: "M16 2v4",
    key: "4m81vk"
  }], ["rect", {
    width: "18",
    height: "18",
    x: "3",
    y: "4",
    rx: "2",
    key: "1hopcy"
  }], ["path", {
    d: "M3 10h18",
    key: "8toen8"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var Folder = createLucideIcon("Folder", [["path", {
    d: "M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z",
    key: "1kt360"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var Heart = createLucideIcon("Heart", [["path", {
    d: "M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z",
    key: "c3ymky"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var Home = createLucideIcon("Home", [["path", {
    d: "m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z",
    key: "y5dka4"
  }], ["polyline", {
    points: "9 22 9 12 15 12 15 22",
    key: "e2us08"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var Image = createLucideIcon("Image", [["rect", {
    width: "18",
    height: "18",
    x: "3",
    y: "3",
    rx: "2",
    ry: "2",
    key: "1m3agn"
  }], ["circle", {
    cx: "9",
    cy: "9",
    r: "2",
    key: "af1f0g"
  }], ["path", {
    d: "m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21",
    key: "1xmnt7"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var LayoutGrid = createLucideIcon("LayoutGrid", [["rect", {
    width: "7",
    height: "7",
    x: "3",
    y: "3",
    rx: "1",
    key: "1g98yp"
  }], ["rect", {
    width: "7",
    height: "7",
    x: "14",
    y: "3",
    rx: "1",
    key: "6d4xhi"
  }], ["rect", {
    width: "7",
    height: "7",
    x: "14",
    y: "14",
    rx: "1",
    key: "nxv5o0"
  }], ["rect", {
    width: "7",
    height: "7",
    x: "3",
    y: "14",
    rx: "1",
    key: "1bb6yr"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var MessageCircle = createLucideIcon("MessageCircle", [["path", {
    d: "M7.9 20A9 9 0 1 0 4 16.1L2 22Z",
    key: "vv11sd"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var Palette = createLucideIcon("Palette", [["circle", {
    cx: "13.5",
    cy: "6.5",
    r: ".5",
    fill: "currentColor",
    key: "1okk4w"
  }], ["circle", {
    cx: "17.5",
    cy: "10.5",
    r: ".5",
    fill: "currentColor",
    key: "f64h9f"
  }], ["circle", {
    cx: "8.5",
    cy: "7.5",
    r: ".5",
    fill: "currentColor",
    key: "fotxhn"
  }], ["circle", {
    cx: "6.5",
    cy: "12.5",
    r: ".5",
    fill: "currentColor",
    key: "qy21gx"
  }], ["path", {
    d: "M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z",
    key: "12rzf8"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var Plus = createLucideIcon("Plus", [["path", {
    d: "M5 12h14",
    key: "1ays0h"
  }], ["path", {
    d: "M12 5v14",
    key: "s699le"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var SquarePen = createLucideIcon("SquarePen", [["path", {
    d: "M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7",
    key: "1m0v6g"
  }], ["path", {
    d: "M18.375 2.625a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z",
    key: "1lpok0"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var UserPlus = createLucideIcon("UserPlus", [["path", {
    d: "M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2",
    key: "1yyitq"
  }], ["circle", {
    cx: "9",
    cy: "7",
    r: "4",
    key: "nufk8"
  }], ["line", {
    x1: "19",
    x2: "19",
    y1: "8",
    y2: "14",
    key: "1bvyxn"
  }], ["line", {
    x1: "22",
    x2: "16",
    y1: "11",
    y2: "11",
    key: "1shjgl"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var User = createLucideIcon("User", [["path", {
    d: "M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2",
    key: "975kel"
  }], ["circle", {
    cx: "12",
    cy: "7",
    r: "4",
    key: "17ys0d"
  }]]);

  /**
   * @license lucide-react v0.346.0 - ISC
   *
   * This source code is licensed under the ISC license.
   * See the LICENSE file in the root directory of this source tree.
   */

  var Users = createLucideIcon("Users", [["path", {
    d: "M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2",
    key: "1yyitq"
  }], ["circle", {
    cx: "9",
    cy: "7",
    r: "4",
    key: "nufk8"
  }], ["path", {
    d: "M22 21v-2a4 4 0 0 0-3-3.87",
    key: "kshegd"
  }], ["path", {
    d: "M16 3.13a4 4 0 0 1 0 7.75",
    key: "1da9ce"
  }]]);

  var icons = {
    Home: Home,
    User: User,
    Calendar: Calendar,
    Heart: Heart,
    MessageCircle: MessageCircle,
    Palette: Palette,
    Image: Image,
    Plus: Plus,
    Folder: Folder,
    Building2: Building2,
    Edit: SquarePen,
    Users: Users,
    UserPlus: UserPlus,
    LayoutGrid: LayoutGrid
  };
  function SidebarMenu(_ref) {
    var role = _ref.role,
      activeSection = _ref.activeSection,
      setActiveSection = _ref.setActiveSection;
    var menu = rolesMenus[role] || [];
    return /*#__PURE__*/React.createElement("nav", {
      className: "ap-dashboard-sidebar"
    }, /*#__PURE__*/React.createElement("ul", null, menu.map(function (item) {
      var Icon = icons[item.icon] || function () {
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

})(React);
