"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = SidebarMenu;
var _react = _interopRequireDefault(require("react"));
var _rolesMenus = require("./rolesMenus");
var Icons = _interopRequireWildcard(require("lucide-react"));
function _interopRequireWildcard(e, t) { if ("function" == typeof WeakMap) var r = new WeakMap(), n = new WeakMap(); return (_interopRequireWildcard = function _interopRequireWildcard(e, t) { if (!t && e && e.__esModule) return e; var o, i, f = { __proto__: null, "default": e }; if (null === e || "object" != _typeof(e) && "function" != typeof e) return f; if (o = t ? n : r) { if (o.has(e)) return o.get(e); o.set(e, f); } for (var _t in e) "default" !== _t && {}.hasOwnProperty.call(e, _t) && ((i = (o = Object.defineProperty) && Object.getOwnPropertyDescriptor(e, _t)) && (i.get || i.set) ? o(f, _t, i) : f[_t] = e[_t]); return f; })(e, t); }
function _interopRequireDefault(e) { return e && e.__esModule ? e : { "default": e }; }
function SidebarMenu(_ref) {
  var role = _ref.role,
    activeSection = _ref.activeSection,
    setActiveSection = _ref.setActiveSection;
  var menu = _rolesMenus.rolesMenus[role] || [];
  return /*#__PURE__*/_react["default"].createElement("nav", {
    className: "ap-dashboard-sidebar"
  }, /*#__PURE__*/_react["default"].createElement("ul", null, menu.map(function (item) {
    var Icon = Icons[item.icon] || function () {
      return null;
    };
    var active = item.section === activeSection;
    return /*#__PURE__*/_react["default"].createElement("li", {
      key: item.section
    }, /*#__PURE__*/_react["default"].createElement("button", {
      type: "button",
      onClick: function onClick() {
        return setActiveSection(item.section);
      },
      className: "ap-sidebar-link ".concat(active ? 'active' : '')
    }, /*#__PURE__*/_react["default"].createElement(Icon, {
      className: "ap-icon"
    }), /*#__PURE__*/_react["default"].createElement("span", null, item.label)));
  })));
}
