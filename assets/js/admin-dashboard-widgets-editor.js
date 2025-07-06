"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
var _react = _interopRequireWildcard(require("react"));
var _reactDom = _interopRequireDefault(require("react-dom"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { "default": e }; }
function _interopRequireWildcard(e, t) { if ("function" == typeof WeakMap) var r = new WeakMap(), n = new WeakMap(); return (_interopRequireWildcard = function _interopRequireWildcard(e, t) { if (!t && e && e.__esModule) return e; var o, i, f = { __proto__: null, "default": e }; if (null === e || "object" != _typeof(e) && "function" != typeof e) return f; if (o = t ? n : r) { if (o.has(e)) return o.get(e); o.set(e, f); } for (var _t in e) "default" !== _t && {}.hasOwnProperty.call(e, _t) && ((i = (o = Object.defineProperty) && Object.getOwnPropertyDescriptor(e, _t)) && (i.get || i.set) ? o(f, _t, i) : f[_t] = e[_t]); return f; })(e, t); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function WidgetsEditor(_ref) {
  var widgets = _ref.widgets,
    config = _ref.config,
    roles = _ref.roles,
    nonce = _ref.nonce,
    ajaxUrl = _ref.ajaxUrl;
  var roleKeys = Object.keys(roles);
  var _useState = (0, _react.useState)(roleKeys[0] || ''),
    _useState2 = _slicedToArray(_useState, 2),
    activeRole = _useState2[0],
    setActiveRole = _useState2[1];
  var _useState3 = (0, _react.useState)([]),
    _useState4 = _slicedToArray(_useState3, 2),
    active = _useState4[0],
    setActive = _useState4[1];
  var _useState5 = (0, _react.useState)([]),
    _useState6 = _slicedToArray(_useState5, 2),
    available = _useState6[0],
    setAvailable = _useState6[1];
  var activeRef = (0, _react.useRef)(null);
  var availRef = (0, _react.useRef)(null);
  (0, _react.useEffect)(function () {
    var activeIds = config[activeRole] || [];
    var activeWidgets = widgets.filter(function (w) {
      return activeIds.includes(w.id);
    });
    var availWidgets = widgets.filter(function (w) {
      return !activeIds.includes(w.id);
    });
    setActive(activeWidgets);
    setAvailable(availWidgets);
  }, [activeRole]);
  (0, _react.useEffect)(function () {
    if (typeof Sortable === 'undefined') return;
    if (!activeRef.current || !availRef.current) return;
    var opts = {
      group: 'widgets',
      animation: 150,
      onSort: updateFromDom,
      onAdd: updateFromDom,
      onRemove: updateFromDom
    };
    var act = Sortable.create(activeRef.current, opts);
    var avail = Sortable.create(availRef.current, opts);
    return function () {
      act.destroy();
      avail.destroy();
    };
  }, [activeRole]);
  function updateFromDom() {
    if (!activeRef.current || !availRef.current) return;
    var idsFrom = function idsFrom(ul) {
      return Array.from(ul.querySelectorAll('li')).map(function (li) {
        return li.dataset.id;
      });
    };
    var actIds = idsFrom(activeRef.current);
    var availIds = idsFrom(availRef.current);
    setActive(actIds.map(function (id) {
      return widgets.find(function (w) {
        return w.id === id;
      });
    }));
    setAvailable(availIds.map(function (id) {
      return widgets.find(function (w) {
        return w.id === id;
      });
    }));
  }
  function handleSave() {
    var form = new FormData();
    form.append('action', 'ap_save_dashboard_widget_config');
    form.append('nonce', nonce);
    active.forEach(function (w) {
      return form.append("config[".concat(activeRole, "][]"), w.id);
    });
    fetch(ajaxUrl, {
      method: 'POST',
      body: form
    }).then(function (r) {
      return r.json();
    }).then(function () {
      return alert('Saved');
    });
  }
  return /*#__PURE__*/_react["default"].createElement("div", {
    className: "ap-widgets-editor"
  }, /*#__PURE__*/_react["default"].createElement("select", {
    value: activeRole,
    onChange: function onChange(e) {
      return setActiveRole(e.target.value);
    }
  }, roleKeys.map(function (r) {
    return /*#__PURE__*/_react["default"].createElement("option", {
      key: r,
      value: r
    }, roles[r].name || r);
  })), /*#__PURE__*/_react["default"].createElement("div", {
    className: "ap-widgets-columns"
  }, /*#__PURE__*/_react["default"].createElement("div", {
    className: "ap-widgets-available"
  }, /*#__PURE__*/_react["default"].createElement("h4", null, "Available Widgets"), /*#__PURE__*/_react["default"].createElement("ul", {
    ref: availRef
  }, available.map(function (w) {
    return /*#__PURE__*/_react["default"].createElement("li", {
      key: w.id,
      "data-id": w.id
    }, w.name);
  }))), /*#__PURE__*/_react["default"].createElement("div", {
    className: "ap-widgets-active"
  }, /*#__PURE__*/_react["default"].createElement("h4", null, "Active Widgets"), /*#__PURE__*/_react["default"].createElement("ul", {
    ref: activeRef
  }, active.map(function (w) {
    return /*#__PURE__*/_react["default"].createElement("li", {
      key: w.id,
      "data-id": w.id
    }, w.name);
  })))), /*#__PURE__*/_react["default"].createElement("button", {
    onClick: handleSave
  }, "Save"));
}
document.addEventListener('DOMContentLoaded', function () {
  var el = document.getElementById('ap-dashboard-widgets-canvas');
  if (el && window.APDashboardWidgetsEditor) {
    _reactDom["default"].render(/*#__PURE__*/_react["default"].createElement(WidgetsEditor, APDashboardWidgetsEditor), el);
  }
});
