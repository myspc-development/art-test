(function (React, client) {
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

  var __ = wp.i18n.__;

  // REST responses use `widget_roles` for the matrix object

  function AdminWidgetMatrix() {
    var _window$APWidgetMatri, _window$wpApiSettings, _window$APWidgetMatri2, _window$wpApiSettings2;
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      widgets = _useState2[0],
      setWidgets = _useState2[1];
    var _useState3 = React.useState([]),
      _useState4 = _slicedToArray(_useState3, 2),
      roles = _useState4[0],
      setRoles = _useState4[1];
    var _useState5 = React.useState({}),
      _useState6 = _slicedToArray(_useState5, 2),
      matrix = _useState6[0],
      setMatrix = _useState6[1];
    var restRoot = ((_window$APWidgetMatri = window.APWidgetMatrix) === null || _window$APWidgetMatri === void 0 ? void 0 : _window$APWidgetMatri.root) || ((_window$wpApiSettings = window.wpApiSettings) === null || _window$wpApiSettings === void 0 ? void 0 : _window$wpApiSettings.root) || '/wp-json/';
    var nonce = ((_window$APWidgetMatri2 = window.APWidgetMatrix) === null || _window$APWidgetMatri2 === void 0 ? void 0 : _window$APWidgetMatri2.nonce) || ((_window$wpApiSettings2 = window.wpApiSettings) === null || _window$wpApiSettings2 === void 0 ? void 0 : _window$wpApiSettings2.nonce) || '';
    React.useEffect(function () {
      fetch(restRoot + 'artpulse/v1/widgets').then(function (r) {
        return r.json();
      }).then(setWidgets);
      fetch(restRoot + 'artpulse/v1/roles').then(function (r) {
        return r.json();
      }).then(setRoles);
      fetch(restRoot + 'artpulse/v1/dashboard-config', {
        headers: {
          'X-WP-Nonce': nonce
        }
      }).then(function (r) {
        return r.json();
      }).then(function (data) {
        return setMatrix(data.widget_roles || {});
      });
    }, []);
    var toggle = function toggle(wid, role) {
      setMatrix(function (m) {
        var list = new Set(m[wid] || []);
        if (list.has(role)) list["delete"](role);else list.add(role);
        return _objectSpread2(_objectSpread2({}, m), {}, _defineProperty({}, wid, Array.from(list)));
      });
    };
    var save = function save() {
      fetch(restRoot + 'artpulse/v1/dashboard-config', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': nonce
        },
        body: JSON.stringify({
          widget_roles: matrix
        })
      }).then(function () {
        var _window$wp, _window$wp$data;
        if ((_window$wp = window.wp) !== null && _window$wp !== void 0 && (_window$wp$data = _window$wp.data) !== null && _window$wp$data !== void 0 && _window$wp$data.dispatch) {
          wp.data.dispatch('core/notices').createNotice('success', __('Saved', 'artpulse'), {
            isDismissible: true
          });
        }
      });
    };
    return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("table", {
      className: "widefat striped"
    }, /*#__PURE__*/React.createElement("thead", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, __('Widget', 'artpulse')), roles.map(function (r) {
      return /*#__PURE__*/React.createElement("th", {
        key: r
      }, r);
    }))), /*#__PURE__*/React.createElement("tbody", null, widgets.map(function (w) {
      return /*#__PURE__*/React.createElement("tr", {
        key: w.id
      }, /*#__PURE__*/React.createElement("td", null, w.name || w.id), roles.map(function (role) {
        return /*#__PURE__*/React.createElement("td", {
          key: role,
          style: {
            textAlign: 'center'
          }
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: (matrix[w.id] || w.roles || []).includes(role),
          onChange: function onChange() {
            return toggle(w.id, role);
          }
        }));
      }));
    }))), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("button", {
      type: "button",
      className: "button button-primary",
      onClick: save
    }, __('Save', 'artpulse'))));
  }

  document.addEventListener('DOMContentLoaded', function () {
    var rootEl = document.getElementById('ap-widget-matrix-root');
    if (rootEl) {
      var root = client.createRoot(rootEl);
      root.render(/*#__PURE__*/React.createElement(AdminWidgetMatrix, null));
    }
  });

})(React, ReactDOM);
