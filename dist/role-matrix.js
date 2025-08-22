var APRoleMatrix = (function (require$$0) {
  'use strict';

  function _arrayLikeToArray(r, a) {
    (null == a || a > r.length) && (a = r.length);
    for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
    return n;
  }
  function _arrayWithHoles(r) {
    if (Array.isArray(r)) return r;
  }
  function asyncGeneratorStep(n, t, e, r, o, a, c) {
    try {
      var i = n[a](c),
        u = i.value;
    } catch (n) {
      return void e(n);
    }
    i.done ? t(u) : Promise.resolve(u).then(r, o);
  }
  function _asyncToGenerator(n) {
    return function () {
      var t = this,
        e = arguments;
      return new Promise(function (r, o) {
        var a = n.apply(t, e);
        function _next(n) {
          asyncGeneratorStep(a, r, o, _next, _throw, "next", n);
        }
        function _throw(n) {
          asyncGeneratorStep(a, r, o, _next, _throw, "throw", n);
        }
        _next(void 0);
      });
    };
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
  function _regenerator() {
    /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */
    var e,
      t,
      r = "function" == typeof Symbol ? Symbol : {},
      n = r.iterator || "@@iterator",
      o = r.toStringTag || "@@toStringTag";
    function i(r, n, o, i) {
      var c = n && n.prototype instanceof Generator ? n : Generator,
        u = Object.create(c.prototype);
      return _regeneratorDefine(u, "_invoke", function (r, n, o) {
        var i,
          c,
          u,
          f = 0,
          p = o || [],
          y = false,
          G = {
            p: 0,
            n: 0,
            v: e,
            a: d,
            f: d.bind(e, 4),
            d: function (t, r) {
              return i = t, c = 0, u = e, G.n = r, a;
            }
          };
        function d(r, n) {
          for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) {
            var o,
              i = p[t],
              d = G.p,
              l = i[2];
            r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0));
          }
          if (o || r > 1) return a;
          throw y = true, n;
        }
        return function (o, p, l) {
          if (f > 1) throw TypeError("Generator is already running");
          for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) {
            i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u);
            try {
              if (f = 2, i) {
                if (c || (o = "next"), t = i[o]) {
                  if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object");
                  if (!t.done) return t;
                  u = t.value, c < 2 && (c = 0);
                } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1);
                i = e;
              } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break;
            } catch (t) {
              i = e, c = 1, u = t;
            } finally {
              f = 1;
            }
          }
          return {
            value: t,
            done: y
          };
        };
      }(r, o, i), true), u;
    }
    var a = {};
    function Generator() {}
    function GeneratorFunction() {}
    function GeneratorFunctionPrototype() {}
    t = Object.getPrototypeOf;
    var c = [][n] ? t(t([][n]())) : (_regeneratorDefine(t = {}, n, function () {
        return this;
      }), t),
      u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c);
    function f(e) {
      return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e;
    }
    return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine(u), _regeneratorDefine(u, o, "Generator"), _regeneratorDefine(u, n, function () {
      return this;
    }), _regeneratorDefine(u, "toString", function () {
      return "[object Generator]";
    }), (_regenerator = function () {
      return {
        w: i,
        m: f
      };
    })();
  }
  function _regeneratorDefine(e, r, n, t) {
    var i = Object.defineProperty;
    try {
      i({}, "", {});
    } catch (e) {
      i = 0;
    }
    _regeneratorDefine = function (e, r, n, t) {
      function o(r, n) {
        _regeneratorDefine(e, r, function (e) {
          return this._invoke(r, n, e);
        });
      }
      r ? i ? i(e, r, {
        value: n,
        enumerable: !t,
        configurable: !t,
        writable: !t
      }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2));
    }, _regeneratorDefine(e, r, n, t);
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

  function RoleMatrix(_ref) {
    var _ref$selectedOrg = _ref.selectedOrg,
      selectedOrg = _ref$selectedOrg === void 0 ? 0 : _ref$selectedOrg;
    var _useState = require$$0.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      roles = _useState2[0],
      setRoles = _useState2[1];
    var _useState3 = require$$0.useState([]),
      _useState4 = _slicedToArray(_useState3, 2),
      users = _useState4[0],
      setUsers = _useState4[1];
    var _useState5 = require$$0.useState({}),
      _useState6 = _slicedToArray(_useState5, 2),
      changes = _useState6[0],
      setChanges = _useState6[1];
    var _useState7 = require$$0.useState(null),
      _useState8 = _slicedToArray(_useState7, 2),
      status = _useState8[0],
      setStatus = _useState8[1];
    var _useState9 = require$$0.useState(false),
      _useState0 = _slicedToArray(_useState9, 2),
      isSaving = _useState0[0],
      setIsSaving = _useState0[1];
    require$$0.useEffect(function () {
      fetch("/wp-json/artpulse/v1/org-roles?org_id=".concat(selectedOrg), {
        headers: {
          'X-WP-Nonce': ArtPulseData.rest_nonce
        }
      }).then(function (res) {
        if (!res.ok) throw new Error("API Error: ".concat(res.status));
        return res.json();
      }).then(function (data) {
        if (!data || !Array.isArray(data.roles) || !Array.isArray(data.users)) {
          throw new Error('Invalid response format');
        }
        setRoles(data.roles);
        setUsers(data.users);
      })["catch"](function (err) {
        console.error('Failed to load org roles:', err);
        setRoles([]);
        setUsers([]);
      });
    }, [selectedOrg]);
    function updateMatrix(userId, roleSlug) {
      setChanges(function (prev) {
        return _objectSpread2(_objectSpread2({}, prev), {}, _defineProperty({}, userId, roleSlug));
      });
      setUsers(function (prev) {
        return prev.map(function (u) {
          return u.id === userId ? _objectSpread2(_objectSpread2({}, u), {}, {
            role: roleSlug
          }) : u;
        });
      });
    }
    var saveChanges = /*#__PURE__*/function () {
      var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var res, _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              setIsSaving(true);
              setStatus(null);
              _context.p = 1;
              _context.n = 2;
              return fetch('/wp-json/artpulse/v1/org-roles/update', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-WP-Nonce': ArtPulseData.rest_nonce
                },
                body: JSON.stringify({
                  org_id: selectedOrg,
                  roles: changes
                })
              });
            case 2:
              res = _context.v;
              if (res.ok) {
                _context.n = 3;
                break;
              }
              throw new Error("API Error: ".concat(res.status));
            case 3:
              setStatus('Changes saved successfully.');
              _context.n = 5;
              break;
            case 4:
              _context.p = 4;
              _t = _context.v;
              console.error('Failed to save org roles:', _t);
              setStatus('Failed to save changes.');
            case 5:
              _context.p = 5;
              setIsSaving(false);
              return _context.f(5);
            case 6:
              return _context.a(2);
          }
        }, _callee, null, [[1, 4, 5, 6]]);
      }));
      return function saveChanges() {
        return _ref2.apply(this, arguments);
      };
    }();
    if (!roles || !Array.isArray(roles) || roles.length === 0) {
      return /*#__PURE__*/jsxRuntimeExports.jsx("p", {
        children: "No roles found or unauthorized."
      });
    }
    return /*#__PURE__*/jsxRuntimeExports.jsxs("div", {
      children: [/*#__PURE__*/jsxRuntimeExports.jsxs("table", {
        children: [/*#__PURE__*/jsxRuntimeExports.jsx("thead", {
          children: /*#__PURE__*/jsxRuntimeExports.jsxs("tr", {
            children: [/*#__PURE__*/jsxRuntimeExports.jsx("th", {
              children: "User"
            }), roles.map(function (role) {
              return /*#__PURE__*/jsxRuntimeExports.jsx("th", {
                children: role.name
              }, role.slug);
            })]
          })
        }), /*#__PURE__*/jsxRuntimeExports.jsx("tbody", {
          children: users.map(function (user) {
            return /*#__PURE__*/jsxRuntimeExports.jsxs("tr", {
              children: [/*#__PURE__*/jsxRuntimeExports.jsx("td", {
                children: user.name
              }), roles.map(function (role) {
                return /*#__PURE__*/jsxRuntimeExports.jsx("td", {
                  children: /*#__PURE__*/jsxRuntimeExports.jsx("input", {
                    type: "radio",
                    checked: user.role === role.slug,
                    onChange: function onChange() {
                      return updateMatrix(user.id, role.slug);
                    }
                  })
                }, role.slug);
              })]
            }, user.id);
          })
        })]
      }), /*#__PURE__*/jsxRuntimeExports.jsx("button", {
        onClick: saveChanges,
        disabled: isSaving,
        children: isSaving ? 'Saving...' : 'Save'
      }), status && /*#__PURE__*/jsxRuntimeExports.jsx("p", {
        children: status
      })]
    });
  }

  return RoleMatrix;

})(React);
