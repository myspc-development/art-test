"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
var _react = _interopRequireWildcard(require("react"));
var _reactDom = _interopRequireDefault(require("react-dom"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { "default": e }; }
function _interopRequireWildcard(e, t) { if ("function" == typeof WeakMap) var r = new WeakMap(), n = new WeakMap(); return (_interopRequireWildcard = function _interopRequireWildcard(e, t) { if (!t && e && e.__esModule) return e; var o, i, f = { __proto__: null, "default": e }; if (null === e || "object" != _typeof(e) && "function" != typeof e) return f; if (o = t ? n : r) { if (o.has(e)) return o.get(e); o.set(e, f); } for (var _t in e) "default" !== _t && {}.hasOwnProperty.call(e, _t) && ((i = (o = Object.defineProperty) && Object.getOwnPropertyDescriptor(e, _t)) && (i.get || i.set) ? o(f, _t, i) : f[_t] = e[_t]); return f; })(e, t); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
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
    ajaxUrl = _ref.ajaxUrl,
    _ref$l10n = _ref.l10n,
    l10n = _ref$l10n === void 0 ? {} : _ref$l10n;
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
  var _useState7 = (0, _react.useState)(false),
    _useState8 = _slicedToArray(_useState7, 2),
    showPreview = _useState8[0],
    setShowPreview = _useState8[1];
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
  function moveItem(list, from, to) {
    var copy = _toConsumableArray(list);
    var item = copy.splice(from, 1)[0];
    copy.splice(to, 0, item);
    return copy;
  }
  function handleKeyDown(e, index, listName) {
    var isActive = listName === 'active';
    var list = isActive ? active : available;
    if (e.key === 'ArrowUp' && index > 0) {
      var newList = moveItem(list, index, index - 1);
      isActive ? setActive(newList) : setAvailable(newList);
      e.preventDefault();
    } else if (e.key === 'ArrowDown' && index < list.length - 1) {
      var _newList = moveItem(list, index, index + 1);
      isActive ? setActive(_newList) : setAvailable(_newList);
      e.preventDefault();
    } else if (e.key === 'ArrowLeft' && isActive) {
      var item = list[index];
      var newAct = _toConsumableArray(active);
      newAct.splice(index, 1);
      setActive(newAct);
      setAvailable([item].concat(_toConsumableArray(available)));
      e.preventDefault();
    } else if (e.key === 'ArrowRight' && !isActive) {
      var _item = list[index];
      var newAvail = _toConsumableArray(available);
      newAvail.splice(index, 1);
      setAvailable(newAvail);
      setActive([].concat(_toConsumableArray(active), [_item]));
      e.preventDefault();
    }
  }
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
    }).then(function (res) {
      if (res.success) {
        var _window$wp, _window$wp$data;
        if ((_window$wp = window.wp) !== null && _window$wp !== void 0 && (_window$wp$data = _window$wp.data) !== null && _window$wp$data !== void 0 && _window$wp$data.dispatch) {
          wp.data.dispatch('core/notices').createNotice('success', l10n.saveSuccess || 'Saved', {
            isDismissible: true
          });
        }
        config[activeRole] = active.map(function (w) {
          return w.id;
        });
      } else {
        var _res$data, _window$wp2, _window$wp2$data;
        var msg = ((_res$data = res.data) === null || _res$data === void 0 ? void 0 : _res$data.message) || l10n.saveError || 'Error';
        if ((_window$wp2 = window.wp) !== null && _window$wp2 !== void 0 && (_window$wp2$data = _window$wp2.data) !== null && _window$wp2$data !== void 0 && _window$wp2$data.dispatch) {
          wp.data.dispatch('core/notices').createNotice('error', msg, {
            isDismissible: true
          });
        }
      }
    })["catch"](function () {
      var _window$wp3, _window$wp3$data;
      if ((_window$wp3 = window.wp) !== null && _window$wp3 !== void 0 && (_window$wp3$data = _window$wp3.data) !== null && _window$wp3$data !== void 0 && _window$wp3$data.dispatch) {
        wp.data.dispatch('core/notices').createNotice('error', l10n.saveError || 'Error', {
          isDismissible: true
        });
      }
    });
  }
  function handleReset() {
    var activeIds = config[activeRole] || [];
    setActive(widgets.filter(function (w) {
      return activeIds.includes(w.id);
    }));
    setAvailable(widgets.filter(function (w) {
      return !activeIds.includes(w.id);
    }));
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
  }, /*#__PURE__*/_react["default"].createElement("h4", {
    id: "ap-available-label"
  }, l10n.availableWidgets || 'Available Widgets'), /*#__PURE__*/_react["default"].createElement("ul", {
    ref: availRef,
    role: "listbox",
    "aria-labelledby": "ap-available-label"
  }, available.map(function (w, i) {
    return /*#__PURE__*/_react["default"].createElement("li", {
      key: w.id,
      "data-id": w.id,
      tabIndex: 0,
      role: "option",
      onKeyDown: function onKeyDown(e) {
        return handleKeyDown(e, i, 'available');
      }
    }, w.name);
  }))), /*#__PURE__*/_react["default"].createElement("div", {
    className: "ap-widgets-active"
  }, /*#__PURE__*/_react["default"].createElement("h4", {
    id: "ap-active-label"
  }, l10n.activeWidgets || 'Active Widgets'), /*#__PURE__*/_react["default"].createElement("ul", {
    ref: activeRef,
    role: "listbox",
    "aria-labelledby": "ap-active-label"
  }, active.map(function (w, i) {
    return /*#__PURE__*/_react["default"].createElement("li", {
      key: w.id,
      "data-id": w.id,
      tabIndex: 0,
      role: "option",
      onKeyDown: function onKeyDown(e) {
        return handleKeyDown(e, i, 'active');
      }
    }, w.name);
  })))), /*#__PURE__*/_react["default"].createElement("div", {
    className: "ap-widgets-actions"
  }, /*#__PURE__*/_react["default"].createElement("button", {
    className: "ap-form-button",
    onClick: handleSave
  }, l10n.save || 'Save'), /*#__PURE__*/_react["default"].createElement("button", {
    className: "ap-form-button",
    onClick: function onClick() {
      return setShowPreview(!showPreview);
    }
  }, l10n.preview || 'Preview'), /*#__PURE__*/_react["default"].createElement("button", {
    className: "ap-form-button",
    onClick: handleReset
  }, l10n.resetDefault || 'Reset to Default')), showPreview && /*#__PURE__*/_react["default"].createElement("div", {
    className: "ap-widgets-preview"
  }, /*#__PURE__*/_react["default"].createElement("h4", null, l10n.preview || 'Preview'), /*#__PURE__*/_react["default"].createElement("ol", null, active.map(function (w) {
    return /*#__PURE__*/_react["default"].createElement("li", {
      key: w.id
    }, w.name);
  }))));
}
document.addEventListener('DOMContentLoaded', function () {
  var el = document.getElementById('ap-dashboard-widgets-canvas');
  if (el && window.APDashboardWidgetsEditor) {
    _reactDom["default"].render(/*#__PURE__*/_react["default"].createElement(WidgetsEditor, APDashboardWidgetsEditor), el);
  }
});
