(function (require$$0, client) {
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
        if (i = (t = t.call(r)).next, 0 === l) {
          if (Object(t) !== t) return;
          f = !1;
        } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0);
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

  var jsxRuntime = {exports: {}};

  var reactJsxRuntime_production_min = {};

  /**
   * @license React
   * react-jsx-runtime.production.min.js
   *
   * Copyright (c) Facebook, Inc. and its affiliates.
   *
   * This source code is licensed under the MIT license found in the
   * LICENSE file in the root directory of this source tree.
   */

  var hasRequiredReactJsxRuntime_production_min;

  function requireReactJsxRuntime_production_min () {
  	if (hasRequiredReactJsxRuntime_production_min) return reactJsxRuntime_production_min;
  	hasRequiredReactJsxRuntime_production_min = 1;

  	var f = require$$0,
  	  k = Symbol["for"]("react.element"),
  	  l = Symbol["for"]("react.fragment"),
  	  m = Object.prototype.hasOwnProperty,
  	  n = f.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED.ReactCurrentOwner,
  	  p = {
  	    key: true,
  	    ref: true,
  	    __self: true,
  	    __source: true
  	  };
  	function q(c, a, g) {
  	  var b,
  	    d = {},
  	    e = null,
  	    h = null;
  	  void 0 !== g && (e = "" + g);
  	  void 0 !== a.key && (e = "" + a.key);
  	  void 0 !== a.ref && (h = a.ref);
  	  for (b in a) m.call(a, b) && !p.hasOwnProperty(b) && (d[b] = a[b]);
  	  if (c && c.defaultProps) for (b in a = c.defaultProps, a) void 0 === d[b] && (d[b] = a[b]);
  	  return {
  	    $$typeof: k,
  	    type: c,
  	    key: e,
  	    ref: h,
  	    props: d,
  	    _owner: n.current
  	  };
  	}
  	reactJsxRuntime_production_min.Fragment = l;
  	reactJsxRuntime_production_min.jsx = q;
  	reactJsxRuntime_production_min.jsxs = q;
  	return reactJsxRuntime_production_min;
  }

  {
    jsxRuntime.exports = requireReactJsxRuntime_production_min();
  }

  var jsxRuntimeExports = jsxRuntime.exports;

  var __ = wp.i18n.__;

  // REST responses use `widget_roles` for the matrix object

  function AdminWidgetMatrix() {
    var _window$APWidgetMatri, _window$wpApiSettings, _window$APWidgetMatri2, _window$wpApiSettings2;
    var _useState = require$$0.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      widgets = _useState2[0],
      setWidgets = _useState2[1];
    var _useState3 = require$$0.useState([]),
      _useState4 = _slicedToArray(_useState3, 2),
      roles = _useState4[0],
      setRoles = _useState4[1];
    var _useState5 = require$$0.useState({}),
      _useState6 = _slicedToArray(_useState5, 2),
      matrix = _useState6[0],
      setMatrix = _useState6[1];
    var _useState7 = require$$0.useState('all'),
      _useState8 = _slicedToArray(_useState7, 2),
      filterRole = _useState8[0],
      setFilterRole = _useState8[1];
    var _useState9 = require$$0.useState(''),
      _useState0 = _slicedToArray(_useState9, 2),
      search = _useState0[0],
      setSearch = _useState0[1];
    var _useState1 = require$$0.useState(''),
      _useState10 = _slicedToArray(_useState1, 2),
      error = _useState10[0],
      setError = _useState10[1];
    var restRoot = ((_window$APWidgetMatri = window.APWidgetMatrix) === null || _window$APWidgetMatri === void 0 ? void 0 : _window$APWidgetMatri.root) || ((_window$wpApiSettings = window.wpApiSettings) === null || _window$wpApiSettings === void 0 ? void 0 : _window$wpApiSettings.root) || '/wp-json/';
    var nonce = ((_window$APWidgetMatri2 = window.APWidgetMatrix) === null || _window$APWidgetMatri2 === void 0 ? void 0 : _window$APWidgetMatri2.nonce) || ((_window$wpApiSettings2 = window.wpApiSettings) === null || _window$wpApiSettings2 === void 0 ? void 0 : _window$wpApiSettings2.nonce) || '';
    var load = function load() {
      setError('');
      Promise.all([fetch(restRoot + 'artpulse/v1/widgets').then(function (r) {
        return r.json();
      }), fetch(restRoot + 'artpulse/v1/roles').then(function (r) {
        return r.json();
      }), fetch(restRoot + 'artpulse/v1/dashboard-config', {
        headers: {
          'X-WP-Nonce': nonce
        }
      }).then(function (r) {
        return r.json();
      })]).then(function (_ref) {
        var _ref2 = _slicedToArray(_ref, 3),
          widgetsData = _ref2[0],
          rolesData = _ref2[1],
          config = _ref2[2];
        setWidgets(widgetsData);
        setRoles(config.role_widgets ? Object.keys(config.role_widgets) : rolesData);
        setMatrix(config.widget_roles || {});
      })["catch"](function () {
        setError(__('Unable to load data. Please try again.', 'artpulse'));
      });
    };
    require$$0.useEffect(load, []);
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
    if (error) {
      return /*#__PURE__*/jsxRuntimeExports.jsxs("div", {
        className: "notice notice-error inline",
        children: [/*#__PURE__*/jsxRuntimeExports.jsx("p", {
          children: error
        }), /*#__PURE__*/jsxRuntimeExports.jsx("p", {
          children: /*#__PURE__*/jsxRuntimeExports.jsx("button", {
            type: "button",
            className: "button",
            onClick: load,
            children: __('Retry', 'artpulse')
          })
        })]
      });
    }
    if (widgets.length === 0) {
      return /*#__PURE__*/jsxRuntimeExports.jsx("div", {
        className: "notice notice-info inline",
        children: /*#__PURE__*/jsxRuntimeExports.jsx("p", {
          children: __('No widgets available.', 'artpulse')
        })
      });
    }
    var filtered = widgets.filter(function (w) {
      var q = search.toLowerCase();
      var matchesSearch = !q || (w.name || '').toLowerCase().includes(q) || w.id.includes(q);
      var matchesRole = filterRole === 'all' || (matrix[w.id] || w.roles || []).includes(filterRole);
      return matchesSearch && matchesRole;
    });
    return /*#__PURE__*/jsxRuntimeExports.jsxs("div", {
      children: [/*#__PURE__*/jsxRuntimeExports.jsxs("div", {
        style: {
          marginBottom: '1em',
          display: 'flex',
          gap: '1em'
        },
        children: [/*#__PURE__*/jsxRuntimeExports.jsxs("select", {
          value: filterRole,
          onChange: function onChange(e) {
            return setFilterRole(e.target.value);
          },
          children: [/*#__PURE__*/jsxRuntimeExports.jsx("option", {
            value: "all",
            children: __('All Roles', 'artpulse')
          }), roles.map(function (r) {
            return /*#__PURE__*/jsxRuntimeExports.jsx("option", {
              value: r,
              children: r
            }, r);
          })]
        }), /*#__PURE__*/jsxRuntimeExports.jsx("input", {
          type: "search",
          placeholder: __('Search widgets…', 'artpulse'),
          value: search,
          onChange: function onChange(e) {
            return setSearch(e.target.value);
          }
        })]
      }), /*#__PURE__*/jsxRuntimeExports.jsxs("table", {
        className: "widefat striped",
        children: [/*#__PURE__*/jsxRuntimeExports.jsx("thead", {
          children: /*#__PURE__*/jsxRuntimeExports.jsxs("tr", {
            children: [/*#__PURE__*/jsxRuntimeExports.jsx("th", {
              children: __('Widget', 'artpulse')
            }), roles.map(function (r) {
              return /*#__PURE__*/jsxRuntimeExports.jsx("th", {
                children: r
              }, r);
            })]
          })
        }), /*#__PURE__*/jsxRuntimeExports.jsx("tbody", {
          children: filtered.map(function (w) {
            return /*#__PURE__*/jsxRuntimeExports.jsxs("tr", {
              children: [/*#__PURE__*/jsxRuntimeExports.jsx("td", {
                children: w.name || w.id
              }), roles.map(function (role) {
                return /*#__PURE__*/jsxRuntimeExports.jsx("td", {
                  style: {
                    textAlign: 'center'
                  },
                  children: /*#__PURE__*/jsxRuntimeExports.jsx("input", {
                    type: "checkbox",
                    checked: (matrix[w.id] || w.roles || []).includes(role),
                    onChange: function onChange() {
                      return toggle(w.id, role);
                    }
                  })
                }, role);
              })]
            }, w.id);
          })
        })]
      }), /*#__PURE__*/jsxRuntimeExports.jsx("p", {
        children: /*#__PURE__*/jsxRuntimeExports.jsx("button", {
          type: "button",
          className: "button button-primary",
          onClick: save,
          children: __('Save', 'artpulse')
        })
      })]
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var rootEl = document.getElementById('ap-widget-matrix-root');
    if (rootEl) {
      var root = client.createRoot(rootEl);
      root.render(/*#__PURE__*/jsxRuntimeExports.jsx(AdminWidgetMatrix, {}));
    }
  });

})(React, ReactDOM);
