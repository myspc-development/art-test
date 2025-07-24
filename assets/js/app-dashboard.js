var APDashboardApp = (function (React$4, require$$0$1, Chart) {
  'use strict';

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
  function _assertThisInitialized(e) {
    if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    return e;
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
  function _callSuper(t, o, e) {
    return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e));
  }
  function _classCallCheck(a, n) {
    if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function");
  }
  function _defineProperties(e, r) {
    for (var t = 0; t < r.length; t++) {
      var o = r[t];
      o.enumerable = o.enumerable || false, o.configurable = true, "value" in o && (o.writable = true), Object.defineProperty(e, _toPropertyKey$3(o.key), o);
    }
  }
  function _createClass(e, r, t) {
    return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", {
      writable: false
    }), e;
  }
  function _defineProperty$3(e, r, t) {
    return (r = _toPropertyKey$3(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: true,
      configurable: true,
      writable: true
    }) : e[r] = t, e;
  }
  function _getPrototypeOf(t) {
    return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) {
      return t.__proto__ || Object.getPrototypeOf(t);
    }, _getPrototypeOf(t);
  }
  function _inherits(t, e) {
    if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function");
    t.prototype = Object.create(e && e.prototype, {
      constructor: {
        value: t,
        writable: true,
        configurable: true
      }
    }), Object.defineProperty(t, "prototype", {
      writable: false
    }), e && _setPrototypeOf(t, e);
  }
  function _isNativeReflectConstruct() {
    try {
      var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {}));
    } catch (t) {}
    return (_isNativeReflectConstruct = function () {
      return !!t;
    })();
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
        _defineProperty$3(e, r, t[r]);
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
  function _possibleConstructorReturn(t, e) {
    if (e && ("object" == typeof e || "function" == typeof e)) return e;
    if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined");
    return _assertThisInitialized(t);
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
      if (r) i ? i(e, r, {
        value: n,
        enumerable: !t,
        configurable: !t,
        writable: !t
      }) : e[r] = n;else {
        function o(r, n) {
          _regeneratorDefine(e, r, function (e) {
            return this._invoke(r, n, e);
          });
        }
        o("next", 0), o("throw", 1), o("return", 2);
      }
    }, _regeneratorDefine(e, r, n, t);
  }
  function _setPrototypeOf(t, e) {
    return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) {
      return t.__proto__ = e, t;
    }, _setPrototypeOf(t, e);
  }
  function _slicedToArray(r, e) {
    return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest();
  }
  function _toConsumableArray(r) {
    return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread();
  }
  function _toPrimitive$3(t, r) {
    if ("object" != typeof t || !t) return t;
    var e = t[Symbol.toPrimitive];
    if (void 0 !== e) {
      var i = e.call(t, r);
      if ("object" != typeof i) return i;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return (String )(t);
  }
  function _toPropertyKey$3(t) {
    var i = _toPrimitive$3(t, "string");
    return "symbol" == typeof i ? i : i + "";
  }
  function _typeof(o) {
    "@babel/helpers - typeof";

    return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
      return typeof o;
    } : function (o) {
      return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
    }, _typeof(o);
  }
  function _unsupportedIterableToArray(r, a) {
    if (r) {
      if ("string" == typeof r) return _arrayLikeToArray(r, a);
      var t = {}.toString.call(r).slice(8, -1);
      return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
    }
  }

  var __$f = wp.i18n.__;
  function DashboardNavbar(_ref) {
    var userRole = _ref.userRole,
      onLogout = _ref.onLogout;
    return /*#__PURE__*/React.createElement("nav", {
      className: "bg-white border-b shadow-sm px-4 py-2 flex justify-between items-center"
    }, /*#__PURE__*/React.createElement("div", {
      className: "text-lg font-bold"
    }, __$f('ArtPulse Dashboard', 'artpulse')), /*#__PURE__*/React.createElement("div", {
      className: "flex gap-4 items-center"
    }, userRole === 'artist' && /*#__PURE__*/React.createElement("a", {
      href: "#/artist",
      className: "text-blue-600"
    }, __$f('Artist', 'artpulse')), userRole === 'organization' && /*#__PURE__*/React.createElement("a", {
      href: "#/org",
      className: "text-blue-600"
    }, __$f('Organization', 'artpulse')), userRole === 'member' && /*#__PURE__*/React.createElement("a", {
      href: "#/member",
      className: "text-blue-600"
    }, __$f('Member', 'artpulse')), /*#__PURE__*/React.createElement("button", {
      onClick: onLogout,
      className: "bg-red-500 text-white px-3 py-1 rounded"
    }, __$f('Logout', 'artpulse'))));
  }

  var __$e = wp.i18n.__;
  function MessagesPanel() {
    var _useState = React$4.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      messages = _useState2[0],
      setMessages = _useState2[1];
    React$4.useEffect(function () {
      fetch('/wp-json/artpulse/v1/dashboard/messages').then(function (res) {
        if (res.status === 401 || res.status === 403) {
          setMessages([{
            id: 0,
            content: __$e('Please log in to view messages.', 'artpulse')
          }]);
          return Promise.reject('unauthorized');
        }
        return res.json();
      }).then(setMessages)["catch"](function () {});
    }, []);
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-widget bg-white p-4 rounded shadow mb-4"
    }, messages.map(function (msg) {
      return /*#__PURE__*/React$4.createElement("p", {
        key: msg.id
      }, msg.content);
    }));
  }

  function AnalyticsCard(_ref) {
    var label = _ref.label,
      value = _ref.value;
    return /*#__PURE__*/React$4.createElement("div", {
      className: "border rounded p-4 text-center"
    }, /*#__PURE__*/React$4.createElement("div", {
      className: "text-2xl font-bold"
    }, value !== null && value !== void 0 ? value : 0), /*#__PURE__*/React$4.createElement("div", {
      className: "text-sm text-gray-500"
    }, label));
  }

  var __$d = wp.i18n.__;
  function TopUsersTable(_ref) {
    var _ref$users = _ref.users,
      users = _ref$users === void 0 ? [] : _ref$users;
    return /*#__PURE__*/React$4.createElement("table", {
      className: "min-w-full text-sm"
    }, /*#__PURE__*/React$4.createElement("thead", null, /*#__PURE__*/React$4.createElement("tr", null, /*#__PURE__*/React$4.createElement("th", {
      className: "text-left"
    }, __$d('User', 'artpulse')), /*#__PURE__*/React$4.createElement("th", {
      className: "text-right"
    }, __$d('Count', 'artpulse')))), /*#__PURE__*/React$4.createElement("tbody", null, users.map(function (u) {
      return /*#__PURE__*/React$4.createElement("tr", {
        key: u.user_id,
        className: "border-b"
      }, /*#__PURE__*/React$4.createElement("td", null, u.user_id), /*#__PURE__*/React$4.createElement("td", {
        className: "text-right"
      }, u.c));
    })));
  }

  var __$c = wp.i18n.__;
  function ActivityGraph(_ref) {
    var _ref$data = _ref.data,
      data = _ref$data === void 0 ? [] : _ref$data;
    var canvasRef = React$4.useRef(null);
    React$4.useEffect(function () {
      if (!canvasRef.current || !data.length) return;
      var chart = new Chart(canvasRef.current.getContext('2d'), {
        type: 'line',
        data: {
          labels: data.map(function (d) {
            return d.day;
          }),
          datasets: [{
            label: __$c('Count', 'artpulse'),
            data: data.map(function (d) {
              return d.c;
            }),
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.2)',
            fill: false
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          }
        }
      });
      return function () {
        return chart.destroy();
      };
    }, [data]);
    return /*#__PURE__*/React$4.createElement("canvas", {
      ref: canvasRef
    });
  }

  var __$b = wp.i18n.__;
  function FlaggedActivityLog(_ref) {
    var _ref$items = _ref.items,
      items = _ref$items === void 0 ? [] : _ref$items;
    return /*#__PURE__*/React$4.createElement("ul", {
      className: "space-y-1 text-sm"
    }, items.map(function (i) {
      return /*#__PURE__*/React$4.createElement("li", {
        key: i.post_id || i.thread_id,
        className: "border-b pb-1"
      }, i.post_id || i.thread_id, " - ", i.c, " ", __$b('flags', 'artpulse'));
    }));
  }

  var __$a = wp.i18n.__;
  function CommunityAnalyticsPanel() {
    var _useState = React$4.useState('messaging'),
      _useState2 = _slicedToArray(_useState, 2),
      tab = _useState2[0],
      setTab = _useState2[1];
    var _useState3 = React$4.useState({}),
      _useState4 = _slicedToArray(_useState3, 2),
      data = _useState4[0],
      setData = _useState4[1];
    React$4.useEffect(function () {
      fetch("/wp-json/artpulse/v1/analytics/community/".concat(tab)).then(function (res) {
        return res.ok ? res.json() : {};
      }).then(setData);
    }, [tab]);
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-widget bg-white p-4 rounded shadow mb-4"
    }, /*#__PURE__*/React$4.createElement("div", {
      className: "flex gap-4 mb-4"
    }, ['messaging', 'comments', 'forums'].map(function (t) {
      return /*#__PURE__*/React$4.createElement("button", {
        key: t,
        onClick: function onClick() {
          return setTab(t);
        },
        className: tab === t ? 'font-semibold' : ''
      }, __$a('' + t.charAt(0).toUpperCase() + t.slice(1), 'artpulse'));
    })), /*#__PURE__*/React$4.createElement("div", {
      className: "grid gap-4 md:grid-cols-2 mb-4"
    }, /*#__PURE__*/React$4.createElement(AnalyticsCard, {
      label: __$a('Total', 'artpulse'),
      value: data.total
    }), data.flagged_count !== undefined && /*#__PURE__*/React$4.createElement(AnalyticsCard, {
      label: __$a('Flagged', 'artpulse'),
      value: data.flagged_count
    })), data.per_day && /*#__PURE__*/React$4.createElement(ActivityGraph, {
      data: data.per_day
    }), tab === 'messaging' && data.top_users && /*#__PURE__*/React$4.createElement(TopUsersTable, {
      users: data.top_users
    }), tab !== 'messaging' && data.top_posts && /*#__PURE__*/React$4.createElement(FlaggedActivityLog, {
      items: data.top_posts
    }), tab === 'forums' && data.top_threads && /*#__PURE__*/React$4.createElement(FlaggedActivityLog, {
      items: data.top_threads
    }));
  }

  function getAugmentedNamespace(n) {
    if (n.__esModule) return n;
    var f = n.default;
  	if (typeof f == "function") {
  		var a = function a () {
  			if (this instanceof a) {
          return Reflect.construct(f, arguments, this.constructor);
  			}
  			return f.apply(this, arguments);
  		};
  		a.prototype = f.prototype;
    } else a = {};
    Object.defineProperty(a, '__esModule', {value: true});
  	Object.keys(n).forEach(function (k) {
  		var d = Object.getOwnPropertyDescriptor(n, k);
  		Object.defineProperty(a, k, d.get ? d : {
  			enumerable: true,
  			get: function () {
  				return n[k];
  			}
  		});
  	});
  	return a;
  }

  var reactGridLayout = {exports: {}};

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports["default"] = void 0;
  var React$3 = _interopRequireWildcard$2(require("react"));
  var _fastEquals$2 = require("fast-equals");
  var _clsx$1 = _interopRequireDefault$3(require("clsx"));
  var _utils$2 = require("./utils");
  var _calculateUtils = require("./calculateUtils");
  var _GridItem = _interopRequireDefault$3(require("./GridItem"));
  var _ReactGridLayoutPropTypes = _interopRequireDefault$3(require("./ReactGridLayoutPropTypes"));
  function _interopRequireDefault$3(e) {
    return e && e.__esModule ? e : {
      "default": e
    };
  }
  function _interopRequireWildcard$2(e, t) {
    if ("function" == typeof WeakMap) var r = new WeakMap(),
      n = new WeakMap();
    return (_interopRequireWildcard$2 = function _interopRequireWildcard(e, t) {
      if (!t && e && e.__esModule) return e;
      var o,
        i,
        f = {
          __proto__: null,
          "default": e
        };
      if (null === e || "object" != _typeof(e) && "function" != typeof e) return f;
      if (o = t ? n : r) {
        if (o.has(e)) return o.get(e);
        o.set(e, f);
      }
      for (var _t in e) "default" !== _t && {}.hasOwnProperty.call(e, _t) && ((i = (o = Object.defineProperty) && Object.getOwnPropertyDescriptor(e, _t)) && (i.get || i.set) ? o(f, _t, i) : f[_t] = e[_t]);
      return f;
    })(e, t);
  }
  function _defineProperty$2(e, r, t) {
    return (r = _toPropertyKey$2(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: true,
      configurable: true,
      writable: true
    }) : e[r] = t, e;
  }
  function _toPropertyKey$2(t) {
    var i = _toPrimitive$2(t, "string");
    return "symbol" == _typeof(i) ? i : i + "";
  }
  function _toPrimitive$2(t, r) {
    if ("object" != _typeof(t) || !t) return t;
    var e = t[Symbol.toPrimitive];
    if (void 0 !== e) {
      var i = e.call(t, r);
      if ("object" != _typeof(i)) return i;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return ("string" === r ? String : Number)(t);
  }
  /*:: import type {
    ChildrenArray as ReactChildrenArray,
    Element as ReactElement
  } from "react";*/
  /*:: import type {
    CompactType,
    GridResizeEvent,
    GridDragEvent,
    DragOverEvent,
    Layout,
    DroppingPosition,
    LayoutItem
  } from "./utils";*/
  // Types
  /*:: import type { PositionParams } from "./calculateUtils";*/
  /*:: type State = {
    activeDrag: ?LayoutItem,
    layout: Layout,
    mounted: boolean,
    oldDragItem: ?LayoutItem,
    oldLayout: ?Layout,
    oldResizeItem: ?LayoutItem,
    resizing: boolean,
    droppingDOMNode: ?ReactElement<any>,
    droppingPosition?: DroppingPosition,
    // Mirrored props
    children: ReactChildrenArray<ReactElement<any>>,
    compactType?: CompactType,
    propsLayout?: Layout
  };*/
  /*:: import type { Props, DefaultProps } from "./ReactGridLayoutPropTypes";*/
  // End Types
  var layoutClassName$1 = "react-grid-layout";
  var isFirefox = false;
  // Try...catch will protect from navigator not existing (e.g. node) or a bad implementation of navigator
  try {
    isFirefox = /firefox/i.test(navigator.userAgent);
  } catch (e) {
    /* Ignore */
  }

  /**
   * A reactive, fluid grid layout with draggable, resizable components.
   */
  var ReactGridLayout = /*#__PURE__*/function (_React$Component) {
    function ReactGridLayout() {
      var _this;
      _classCallCheck(this, ReactGridLayout);
      _this = _callSuper(this, ReactGridLayout, arguments);
      _defineProperty$2(_this, "state", {
        activeDrag: null,
        layout: (0, _utils$2.synchronizeLayoutWithChildren)(_this.props.layout, _this.props.children, _this.props.cols,
        // Legacy support for verticalCompact: false
        (0, _utils$2.compactType)(_this.props), _this.props.allowOverlap),
        mounted: false,
        oldDragItem: null,
        oldLayout: null,
        oldResizeItem: null,
        resizing: false,
        droppingDOMNode: null,
        children: []
      });
      _defineProperty$2(_this, "dragEnterCounter", 0);
      /**
       * When dragging starts
       * @param {String} i Id of the child
       * @param {Number} x X position of the move
       * @param {Number} y Y position of the move
       * @param {Event} e The mousedown event
       * @param {Element} node The current dragging DOM element
       */
      _defineProperty$2(_this, "onDragStart", function (i /*: string*/, x /*: number*/, y /*: number*/, _ref /*:: */) {
        var e = _ref.e,
          node = _ref.node;
        var layout = _this.state.layout;
        var l = (0, _utils$2.getLayoutItem)(layout, i);
        if (!l) return;

        // Create placeholder (display only)
        var placeholder = {
          w: l.w,
          h: l.h,
          x: l.x,
          y: l.y,
          placeholder: true,
          i: i
        };
        _this.setState({
          oldDragItem: (0, _utils$2.cloneLayoutItem)(l),
          oldLayout: layout,
          activeDrag: placeholder
        });
        return _this.props.onDragStart(layout, l, l, null, e, node);
      });
      /**
       * Each drag movement create a new dragelement and move the element to the dragged location
       * @param {String} i Id of the child
       * @param {Number} x X position of the move
       * @param {Number} y Y position of the move
       * @param {Event} e The mousedown event
       * @param {Element} node The current dragging DOM element
       */
      _defineProperty$2(_this, "onDrag", function (i, x, y, _ref2) {
        var e = _ref2.e,
          node = _ref2.node;
        var oldDragItem = _this.state.oldDragItem;
        var layout = _this.state.layout;
        var _this$props = _this.props,
          cols = _this$props.cols,
          allowOverlap = _this$props.allowOverlap,
          preventCollision = _this$props.preventCollision;
        var l = (0, _utils$2.getLayoutItem)(layout, i);
        if (!l) return;

        // Create placeholder (display only)
        var placeholder = {
          w: l.w,
          h: l.h,
          x: l.x,
          y: l.y,
          placeholder: true,
          i: i
        };

        // Move the element to the dragged location.
        var isUserAction = true;
        layout = (0, _utils$2.moveElement)(layout, l, x, y, isUserAction, preventCollision, (0, _utils$2.compactType)(_this.props), cols, allowOverlap);
        _this.props.onDrag(layout, oldDragItem, l, placeholder, e, node);
        _this.setState({
          layout: allowOverlap ? layout : (0, _utils$2.compact)(layout, (0, _utils$2.compactType)(_this.props), cols),
          activeDrag: placeholder
        });
      });
      /**
       * When dragging stops, figure out which position the element is closest to and update its x and y.
       * @param  {String} i Index of the child.
       * @param {Number} x X position of the move
       * @param {Number} y Y position of the move
       * @param {Event} e The mousedown event
       * @param {Element} node The current dragging DOM element
       */
      _defineProperty$2(_this, "onDragStop", function (i, x, y, _ref3) {
        var e = _ref3.e,
          node = _ref3.node;
        if (!_this.state.activeDrag) return;
        var oldDragItem = _this.state.oldDragItem;
        var layout = _this.state.layout;
        var _this$props2 = _this.props,
          cols = _this$props2.cols,
          preventCollision = _this$props2.preventCollision,
          allowOverlap = _this$props2.allowOverlap;
        var l = (0, _utils$2.getLayoutItem)(layout, i);
        if (!l) return;

        // Move the element here
        var isUserAction = true;
        layout = (0, _utils$2.moveElement)(layout, l, x, y, isUserAction, preventCollision, (0, _utils$2.compactType)(_this.props), cols, allowOverlap);

        // Set state
        var newLayout = allowOverlap ? layout : (0, _utils$2.compact)(layout, (0, _utils$2.compactType)(_this.props), cols);
        _this.props.onDragStop(newLayout, oldDragItem, l, null, e, node);
        var oldLayout = _this.state.oldLayout;
        _this.setState({
          activeDrag: null,
          layout: newLayout,
          oldDragItem: null,
          oldLayout: null
        });
        _this.onLayoutMaybeChanged(newLayout, oldLayout);
      });
      _defineProperty$2(_this, "onResizeStart", function (i, w, h, _ref4) {
        var e = _ref4.e,
          node = _ref4.node;
        var layout = _this.state.layout;
        var l = (0, _utils$2.getLayoutItem)(layout, i);
        if (!l) return;
        _this.setState({
          oldResizeItem: (0, _utils$2.cloneLayoutItem)(l),
          oldLayout: _this.state.layout,
          resizing: true
        });
        _this.props.onResizeStart(layout, l, l, null, e, node);
      });
      _defineProperty$2(_this, "onResize", function (i, w, h, _ref5) {
        var e = _ref5.e,
          node = _ref5.node;
          _ref5.size;
          var handle = _ref5.handle;
        var oldResizeItem = _this.state.oldResizeItem;
        var layout = _this.state.layout;
        var _this$props3 = _this.props,
          cols = _this$props3.cols,
          preventCollision = _this$props3.preventCollision,
          allowOverlap = _this$props3.allowOverlap;
        var shouldMoveItem = false;
        var finalLayout;
        var x;
        var y;
        var _ref7 = (0, _utils$2.withLayoutItem)(layout, i, function (l) {
            var hasCollisions;
            x = l.x;
            y = l.y;
            if (["sw", "w", "nw", "n", "ne"].indexOf(handle) !== -1) {
              if (["sw", "nw", "w"].indexOf(handle) !== -1) {
                x = l.x + (l.w - w);
                w = l.x !== x && x < 0 ? l.w : w;
                x = x < 0 ? 0 : x;
              }
              if (["ne", "n", "nw"].indexOf(handle) !== -1) {
                y = l.y + (l.h - h);
                h = l.y !== y && y < 0 ? l.h : h;
                y = y < 0 ? 0 : y;
              }
              shouldMoveItem = true;
            }

            // Something like quad tree should be used
            // to find collisions faster
            if (preventCollision && !allowOverlap) {
              var collisions = (0, _utils$2.getAllCollisions)(layout, _objectSpread2(_objectSpread2({}, l), {}, {
                w: w,
                h: h,
                x: x,
                y: y
              })).filter(function (layoutItem) {
                return layoutItem.i !== l.i;
              });
              hasCollisions = collisions.length > 0;

              // If we're colliding, we need adjust the placeholder.
              if (hasCollisions) {
                // Reset layoutItem dimensions if there were collisions
                y = l.y;
                h = l.h;
                x = l.x;
                w = l.w;
                shouldMoveItem = false;
              }
            }
            l.w = w;
            l.h = h;
            return l;
          }),
          _ref8 = _slicedToArray(_ref7, 2),
          newLayout = _ref8[0],
          l = _ref8[1];

        // Shouldn't ever happen, but typechecking makes it necessary
        if (!l) return;
        finalLayout = newLayout;
        if (shouldMoveItem) {
          // Move the element to the new position.
          var isUserAction = true;
          finalLayout = (0, _utils$2.moveElement)(newLayout, l, x, y, isUserAction, _this.props.preventCollision, (0, _utils$2.compactType)(_this.props), cols, allowOverlap);
        }

        // Create placeholder element (display only)
        var placeholder = {
          w: l.w,
          h: l.h,
          x: l.x,
          y: l.y,
          "static": true,
          i: i
        };
        _this.props.onResize(finalLayout, oldResizeItem, l, placeholder, e, node);

        // Re-compact the newLayout and set the drag placeholder.
        _this.setState({
          layout: allowOverlap ? finalLayout : (0, _utils$2.compact)(finalLayout, (0, _utils$2.compactType)(_this.props), cols),
          activeDrag: placeholder
        });
      });
      _defineProperty$2(_this, "onResizeStop", function (i, w, h, _ref6) {
        var e = _ref6.e,
          node = _ref6.node;
        var _this$state = _this.state,
          layout = _this$state.layout,
          oldResizeItem = _this$state.oldResizeItem;
        var _this$props4 = _this.props,
          cols = _this$props4.cols,
          allowOverlap = _this$props4.allowOverlap;
        var l = (0, _utils$2.getLayoutItem)(layout, i);

        // Set state
        var newLayout = allowOverlap ? layout : (0, _utils$2.compact)(layout, (0, _utils$2.compactType)(_this.props), cols);
        _this.props.onResizeStop(newLayout, oldResizeItem, l, null, e, node);
        var oldLayout = _this.state.oldLayout;
        _this.setState({
          activeDrag: null,
          layout: newLayout,
          oldResizeItem: null,
          oldLayout: null,
          resizing: false
        });
        _this.onLayoutMaybeChanged(newLayout, oldLayout);
      });
      // Called while dragging an element. Part of browser native drag/drop API.
      // Native event target might be the layout itself, or an element within the layout.
      _defineProperty$2(_this, "onDragOver", function (e) {
        var _e$nativeEvent$target;
        e.preventDefault(); // Prevent any browser native action
        e.stopPropagation();

        // we should ignore events from layout's children in Firefox
        // to avoid unpredictable jumping of a dropping placeholder
        // FIXME remove this hack
        if (isFirefox &&
        // $FlowIgnore can't figure this out
        !((_e$nativeEvent$target = e.nativeEvent.target) !== null && _e$nativeEvent$target !== void 0 && _e$nativeEvent$target.classList.contains(layoutClassName$1))) {
          return false;
        }
        var _this$props5 = _this.props,
          droppingItem = _this$props5.droppingItem,
          onDropDragOver = _this$props5.onDropDragOver,
          margin = _this$props5.margin,
          cols = _this$props5.cols,
          rowHeight = _this$props5.rowHeight,
          maxRows = _this$props5.maxRows,
          width = _this$props5.width,
          containerPadding = _this$props5.containerPadding,
          transformScale = _this$props5.transformScale;
        // Allow user to customize the dropping item or short-circuit the drop based on the results
        // of the `onDragOver(e: Event)` callback.
        var onDragOverResult = onDropDragOver === null || onDropDragOver === void 0 ? void 0 : onDropDragOver(e);
        if (onDragOverResult === false) {
          if (_this.state.droppingDOMNode) {
            _this.removeDroppingPlaceholder();
          }
          return false;
        }
        var finalDroppingItem = _objectSpread2(_objectSpread2({}, droppingItem), onDragOverResult);
        var layout = _this.state.layout;

        // $FlowIgnore missing def
        var gridRect = e.currentTarget.getBoundingClientRect(); // The grid's position in the viewport

        // Calculate the mouse position relative to the grid
        var layerX = e.clientX - gridRect.left;
        var layerY = e.clientY - gridRect.top;
        var droppingPosition = {
          left: layerX / transformScale,
          top: layerY / transformScale,
          e: e
        };
        if (!_this.state.droppingDOMNode) {
          var positionParams /*: PositionParams*/ = {
            cols: cols,
            margin: margin,
            maxRows: maxRows,
            rowHeight: rowHeight,
            containerWidth: width,
            containerPadding: containerPadding || margin
          };
          var calculatedPosition = (0, _calculateUtils.calcXY)(positionParams, layerY, layerX, finalDroppingItem.w, finalDroppingItem.h);
          _this.setState({
            droppingDOMNode: /*#__PURE__*/React$3.createElement("div", {
              key: finalDroppingItem.i
            }),
            droppingPosition: droppingPosition,
            layout: [].concat(_toConsumableArray(layout), [_objectSpread2(_objectSpread2({}, finalDroppingItem), {}, {
              x: calculatedPosition.x,
              y: calculatedPosition.y,
              "static": false,
              isDraggable: true
            })])
          });
        } else if (_this.state.droppingPosition) {
          var _this$state$droppingP = _this.state.droppingPosition,
            left = _this$state$droppingP.left,
            top = _this$state$droppingP.top;
          var shouldUpdatePosition = left != layerX || top != layerY;
          if (shouldUpdatePosition) {
            _this.setState({
              droppingPosition: droppingPosition
            });
          }
        }
      });
      _defineProperty$2(_this, "removeDroppingPlaceholder", function () {
        var _this$props6 = _this.props,
          droppingItem = _this$props6.droppingItem,
          cols = _this$props6.cols;
        var layout = _this.state.layout;
        var newLayout = (0, _utils$2.compact)(layout.filter(function (l) {
          return l.i !== droppingItem.i;
        }), (0, _utils$2.compactType)(_this.props), cols, _this.props.allowOverlap);
        _this.setState({
          layout: newLayout,
          droppingDOMNode: null,
          activeDrag: null,
          droppingPosition: undefined
        });
      });
      _defineProperty$2(_this, "onDragLeave", function (e) {
        e.preventDefault(); // Prevent any browser native action
        e.stopPropagation();
        _this.dragEnterCounter--;

        // onDragLeave can be triggered on each layout's child.
        // But we know that count of dragEnter and dragLeave events
        // will be balanced after leaving the layout's container
        // so we can increase and decrease count of dragEnter and
        // when it'll be equal to 0 we'll remove the placeholder
        if (_this.dragEnterCounter === 0) {
          _this.removeDroppingPlaceholder();
        }
      });
      _defineProperty$2(_this, "onDragEnter", function (e) {
        e.preventDefault(); // Prevent any browser native action
        e.stopPropagation();
        _this.dragEnterCounter++;
      });
      _defineProperty$2(_this, "onDrop", function (e /*: Event*/) {
        e.preventDefault(); // Prevent any browser native action
        e.stopPropagation();
        var droppingItem = _this.props.droppingItem;
        var layout = _this.state.layout;
        var item = layout.find(function (l) {
          return l.i === droppingItem.i;
        });

        // reset dragEnter counter on drop
        _this.dragEnterCounter = 0;
        _this.removeDroppingPlaceholder();
        _this.props.onDrop(layout, item, e);
      });
      return _this;
    }
    _inherits(ReactGridLayout, _React$Component);
    return _createClass(ReactGridLayout, [{
      key: "componentDidMount",
      value: function componentDidMount() {
        this.setState({
          mounted: true
        });
        // Possibly call back with layout on mount. This should be done after correcting the layout width
        // to ensure we don't rerender with the wrong width.
        this.onLayoutMaybeChanged(this.state.layout, this.props.layout);
      }
    }, {
      key: "shouldComponentUpdate",
      value: function shouldComponentUpdate(nextProps /*: Props*/, nextState /*: State*/) /*: boolean*/{
        return (
          // NOTE: this is almost always unequal. Therefore the only way to get better performance
          // from SCU is if the user intentionally memoizes children. If they do, and they can
          // handle changes properly, performance will increase.
          this.props.children !== nextProps.children || !(0, _utils$2.fastRGLPropsEqual)(this.props, nextProps, _fastEquals$2.deepEqual) || this.state.activeDrag !== nextState.activeDrag || this.state.mounted !== nextState.mounted || this.state.droppingPosition !== nextState.droppingPosition
        );
      }
    }, {
      key: "componentDidUpdate",
      value: function componentDidUpdate(prevProps /*: Props*/, prevState /*: State*/) {
        if (!this.state.activeDrag) {
          var newLayout = this.state.layout;
          var oldLayout = prevState.layout;
          this.onLayoutMaybeChanged(newLayout, oldLayout);
        }
      }

      /**
       * Calculates a pixel value for the container.
       * @return {String} Container height in pixels.
       */
    }, {
      key: "containerHeight",
      value: function containerHeight() /*: ?string*/{
        if (!this.props.autoSize) return;
        var nbRow = (0, _utils$2.bottom)(this.state.layout);
        var containerPaddingY = this.props.containerPadding ? this.props.containerPadding[1] : this.props.margin[1];
        return nbRow * this.props.rowHeight + (nbRow - 1) * this.props.margin[1] + containerPaddingY * 2 + "px";
      }
    }, {
      key: "onLayoutMaybeChanged",
      value: function onLayoutMaybeChanged(newLayout /*: Layout*/, oldLayout /*: ?Layout*/) {
        if (!oldLayout) oldLayout = this.state.layout;
        if (!(0, _fastEquals$2.deepEqual)(oldLayout, newLayout)) {
          this.props.onLayoutChange(newLayout);
        }
      }
      /**
       * Create a placeholder object.
       * @return {Element} Placeholder div.
       */
    }, {
      key: "placeholder",
      value: function placeholder() /*: ?ReactElement<any>*/{
        var activeDrag = this.state.activeDrag;
        if (!activeDrag) return null;
        var _this$props7 = this.props,
          width = _this$props7.width,
          cols = _this$props7.cols,
          margin = _this$props7.margin,
          containerPadding = _this$props7.containerPadding,
          rowHeight = _this$props7.rowHeight,
          maxRows = _this$props7.maxRows,
          useCSSTransforms = _this$props7.useCSSTransforms,
          transformScale = _this$props7.transformScale;

        // {...this.state.activeDrag} is pretty slow, actually
        return /*#__PURE__*/React$3.createElement(_GridItem["default"], {
          w: activeDrag.w,
          h: activeDrag.h,
          x: activeDrag.x,
          y: activeDrag.y,
          i: activeDrag.i,
          className: "react-grid-placeholder ".concat(this.state.resizing ? "placeholder-resizing" : ""),
          containerWidth: width,
          cols: cols,
          margin: margin,
          containerPadding: containerPadding || margin,
          maxRows: maxRows,
          rowHeight: rowHeight,
          isDraggable: false,
          isResizable: false,
          isBounded: false,
          useCSSTransforms: useCSSTransforms,
          transformScale: transformScale
        }, /*#__PURE__*/React$3.createElement("div", null));
      }

      /**
       * Given a grid item, set its style attributes & surround in a <Draggable>.
       * @param  {Element} child React element.
       * @return {Element}       Element wrapped in draggable and properly placed.
       */
    }, {
      key: "processGridItem",
      value: function processGridItem(child /*: ReactElement<any>*/, isDroppingItem /*: boolean*/) /*: ?ReactElement<any>*/{
        if (!child || !child.key) return;
        var l = (0, _utils$2.getLayoutItem)(this.state.layout, String(child.key));
        if (!l) return null;
        var _this$props8 = this.props,
          width = _this$props8.width,
          cols = _this$props8.cols,
          margin = _this$props8.margin,
          containerPadding = _this$props8.containerPadding,
          rowHeight = _this$props8.rowHeight,
          maxRows = _this$props8.maxRows,
          isDraggable = _this$props8.isDraggable,
          isResizable = _this$props8.isResizable,
          isBounded = _this$props8.isBounded,
          useCSSTransforms = _this$props8.useCSSTransforms,
          transformScale = _this$props8.transformScale,
          draggableCancel = _this$props8.draggableCancel,
          draggableHandle = _this$props8.draggableHandle,
          resizeHandles = _this$props8.resizeHandles,
          resizeHandle = _this$props8.resizeHandle;
        var _this$state2 = this.state,
          mounted = _this$state2.mounted,
          droppingPosition = _this$state2.droppingPosition;

        // Determine user manipulations possible.
        // If an item is static, it can't be manipulated by default.
        // Any properties defined directly on the grid item will take precedence.
        var draggable = typeof l.isDraggable === "boolean" ? l.isDraggable : !l["static"] && isDraggable;
        var resizable = typeof l.isResizable === "boolean" ? l.isResizable : !l["static"] && isResizable;
        var resizeHandlesOptions = l.resizeHandles || resizeHandles;

        // isBounded set on child if set on parent, and child is not explicitly false
        var bounded = draggable && isBounded && l.isBounded !== false;
        return /*#__PURE__*/React$3.createElement(_GridItem["default"], {
          containerWidth: width,
          cols: cols,
          margin: margin,
          containerPadding: containerPadding || margin,
          maxRows: maxRows,
          rowHeight: rowHeight,
          cancel: draggableCancel,
          handle: draggableHandle,
          onDragStop: this.onDragStop,
          onDragStart: this.onDragStart,
          onDrag: this.onDrag,
          onResizeStart: this.onResizeStart,
          onResize: this.onResize,
          onResizeStop: this.onResizeStop,
          isDraggable: draggable,
          isResizable: resizable,
          isBounded: bounded,
          useCSSTransforms: useCSSTransforms && mounted,
          usePercentages: !mounted,
          transformScale: transformScale,
          w: l.w,
          h: l.h,
          x: l.x,
          y: l.y,
          i: l.i,
          minH: l.minH,
          minW: l.minW,
          maxH: l.maxH,
          maxW: l.maxW,
          "static": l["static"],
          droppingPosition: isDroppingItem ? droppingPosition : undefined,
          resizeHandles: resizeHandlesOptions,
          resizeHandle: resizeHandle
        }, child);
      }
    }, {
      key: "render",
      value: function render() /*: React.Element<"div">*/{
        var _this2 = this;
        var _this$props9 = this.props,
          className = _this$props9.className,
          style = _this$props9.style,
          isDroppable = _this$props9.isDroppable,
          innerRef = _this$props9.innerRef;
        var mergedClassName = (0, _clsx$1["default"])(layoutClassName$1, className);
        var mergedStyle = _objectSpread2({
          height: this.containerHeight()
        }, style);
        return /*#__PURE__*/React$3.createElement("div", {
          ref: innerRef,
          className: mergedClassName,
          style: mergedStyle,
          onDrop: isDroppable ? this.onDrop : _utils$2.noop,
          onDragLeave: isDroppable ? this.onDragLeave : _utils$2.noop,
          onDragEnter: isDroppable ? this.onDragEnter : _utils$2.noop,
          onDragOver: isDroppable ? this.onDragOver : _utils$2.noop
        }, React$3.Children.map(this.props.children, function (child) {
          return _this2.processGridItem(child);
        }), isDroppable && this.state.droppingDOMNode && this.processGridItem(this.state.droppingDOMNode, true), this.placeholder());
      }
    }], [{
      key: "getDerivedStateFromProps",
      value: function getDerivedStateFromProps(nextProps /*: Props*/, prevState /*: State*/) /*: $Shape<State> | null*/{
        var newLayoutBase;
        if (prevState.activeDrag) {
          return null;
        }

        // Legacy support for compactType
        // Allow parent to set layout directly.
        if (!(0, _fastEquals$2.deepEqual)(nextProps.layout, prevState.propsLayout) || nextProps.compactType !== prevState.compactType) {
          newLayoutBase = nextProps.layout;
        } else if (!(0, _utils$2.childrenEqual)(nextProps.children, prevState.children)) {
          // If children change, also regenerate the layout. Use our state
          // as the base in case because it may be more up to date than
          // what is in props.
          newLayoutBase = prevState.layout;
        }

        // We need to regenerate the layout.
        if (newLayoutBase) {
          var newLayout = (0, _utils$2.synchronizeLayoutWithChildren)(newLayoutBase, nextProps.children, nextProps.cols, (0, _utils$2.compactType)(nextProps), nextProps.allowOverlap);
          return {
            layout: newLayout,
            // We need to save these props to state for using
            // getDerivedStateFromProps instead of componentDidMount (in which we would get extra rerender)
            compactType: nextProps.compactType,
            children: nextProps.children,
            propsLayout: nextProps.layout
          };
        }
        return null;
      }
    }]);
  }(React$3.Component /*:: <Props, State>*/);
  exports["default"] = ReactGridLayout;
  // TODO publish internal ReactClass displayName transform
  _defineProperty$2(ReactGridLayout, "displayName", "ReactGridLayout");
  // Refactored to another module to make way for preval
  _defineProperty$2(ReactGridLayout, "propTypes", _ReactGridLayoutPropTypes["default"]);
  _defineProperty$2(ReactGridLayout, "defaultProps", {
    autoSize: true,
    cols: 12,
    className: "",
    style: {},
    draggableHandle: "",
    draggableCancel: "",
    containerPadding: null,
    rowHeight: 150,
    maxRows: Infinity,
    // infinite vertical growth
    layout: [],
    margin: [10, 10],
    isBounded: false,
    isDraggable: true,
    isResizable: true,
    allowOverlap: false,
    isDroppable: false,
    useCSSTransforms: true,
    transformScale: 1,
    verticalCompact: true,
    compactType: "vertical",
    preventCollision: false,
    droppingItem: {
      i: "__dropping-elem__",
      h: 1,
      w: 1
    },
    resizeHandles: ["se"],
    onLayoutChange: _utils$2.noop,
    onDragStart: _utils$2.noop,
    onDrag: _utils$2.noop,
    onDragStop: _utils$2.noop,
    onResizeStart: _utils$2.noop,
    onResize: _utils$2.noop,
    onResizeStop: _utils$2.noop,
    onDrop: _utils$2.noop,
    onDropDragOver: _utils$2.noop
  });

  var ReactGridLayout$1 = /*#__PURE__*/Object.freeze({
    __proto__: null
  });

  var require$$0 = /*@__PURE__*/getAugmentedNamespace(ReactGridLayout$1);

  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports.bottom = bottom;
  exports.childrenEqual = childrenEqual;
  exports.cloneLayout = cloneLayout;
  exports.cloneLayoutItem = cloneLayoutItem;
  exports.collides = collides;
  exports.compact = compact;
  exports.compactItem = compactItem;
  exports.compactType = compactType;
  exports.correctBounds = correctBounds;
  exports.fastPositionEqual = fastPositionEqual;
  exports.fastRGLPropsEqual = void 0;
  exports.getAllCollisions = getAllCollisions;
  exports.getFirstCollision = getFirstCollision;
  exports.getLayoutItem = getLayoutItem;
  exports.getStatics = getStatics;
  exports.modifyLayout = modifyLayout;
  exports.moveElement = moveElement;
  exports.moveElementAwayFromCollision = moveElementAwayFromCollision;
  exports.noop = void 0;
  exports.perc = perc;
  exports.resizeItemInDirection = resizeItemInDirection;
  exports.setTopLeft = setTopLeft;
  exports.setTransform = setTransform;
  exports.sortLayoutItems = sortLayoutItems;
  exports.sortLayoutItemsByColRow = sortLayoutItemsByColRow;
  exports.sortLayoutItemsByRowCol = sortLayoutItemsByRowCol;
  exports.synchronizeLayoutWithChildren = synchronizeLayoutWithChildren;
  exports.validateLayout = validateLayout;
  exports.withLayoutItem = withLayoutItem;
  var _fastEquals$1 = require("fast-equals");
  var _react = _interopRequireDefault$2(require("react"));
  function _interopRequireDefault$2(e) {
    return e && e.__esModule ? e : {
      "default": e
    };
  }

  /**
   * Return the bottom coordinate of the layout.
   *
   * @param  {Array} layout Layout array.
   * @return {Number}       Bottom coordinate.
   */
  function bottom(layout /*: Layout*/) /*: number*/{
    var max = 0,
      bottomY;
    for (var i = 0, len = layout.length; i < len; i++) {
      bottomY = layout[i].y + layout[i].h;
      if (bottomY > max) max = bottomY;
    }
    return max;
  }
  function cloneLayout(layout /*: Layout*/) /*: Layout*/{
    var newLayout = Array(layout.length);
    for (var i = 0, len = layout.length; i < len; i++) {
      newLayout[i] = cloneLayoutItem(layout[i]);
    }
    return newLayout;
  }

  // Modify a layoutItem inside a layout. Returns a new Layout,
  // does not mutate. Carries over all other LayoutItems unmodified.
  function modifyLayout(layout /*: Layout*/, layoutItem /*: LayoutItem*/) /*: Layout*/{
    var newLayout = Array(layout.length);
    for (var i = 0, len = layout.length; i < len; i++) {
      if (layoutItem.i === layout[i].i) {
        newLayout[i] = layoutItem;
      } else {
        newLayout[i] = layout[i];
      }
    }
    return newLayout;
  }

  // Function to be called to modify a layout item.
  // Does defensive clones to ensure the layout is not modified.
  function withLayoutItem(layout /*: Layout*/, itemKey /*: string*/, cb /*: LayoutItem => LayoutItem*/) /*: [Layout, ?LayoutItem]*/{
    var item = getLayoutItem(layout, itemKey);
    if (!item) return [layout, null];
    item = cb(cloneLayoutItem(item)); // defensive clone then modify
    // FIXME could do this faster if we already knew the index
    layout = modifyLayout(layout, item);
    return [layout, item];
  }

  // Fast path to cloning, since this is monomorphic
  function cloneLayoutItem(layoutItem /*: LayoutItem*/) /*: LayoutItem*/{
    return {
      w: layoutItem.w,
      h: layoutItem.h,
      x: layoutItem.x,
      y: layoutItem.y,
      i: layoutItem.i,
      minW: layoutItem.minW,
      maxW: layoutItem.maxW,
      minH: layoutItem.minH,
      maxH: layoutItem.maxH,
      moved: Boolean(layoutItem.moved),
      "static": Boolean(layoutItem["static"]),
      // These can be null/undefined
      isDraggable: layoutItem.isDraggable,
      isResizable: layoutItem.isResizable,
      resizeHandles: layoutItem.resizeHandles,
      isBounded: layoutItem.isBounded
    };
  }

  /**
   * Comparing React `children` is a bit difficult. This is a good way to compare them.
   * This will catch differences in keys, order, and length.
   */
  function childrenEqual(a /*: ReactChildren*/, b /*: ReactChildren*/) /*: boolean*/{
    return (0, _fastEquals$1.deepEqual)(_react["default"].Children.map(a, function (c) {
      return c === null || c === void 0 ? void 0 : c.key;
    }), _react["default"].Children.map(b, function (c) {
      return c === null || c === void 0 ? void 0 : c.key;
    })) && (0, _fastEquals$1.deepEqual)(_react["default"].Children.map(a, function (c) {
      return c === null || c === void 0 ? void 0 : c.props["data-grid"];
    }), _react["default"].Children.map(b, function (c) {
      return c === null || c === void 0 ? void 0 : c.props["data-grid"];
    }));
  }

  /**
   * See `fastRGLPropsEqual.js`.
   * We want this to run as fast as possible - it is called often - and to be
   * resilient to new props that we add. So rather than call lodash.isEqual,
   * which isn't suited to comparing props very well, we use this specialized
   * function in conjunction with preval to generate the fastest possible comparison
   * function, tuned for exactly our props.
   */
  /*:: type FastRGLPropsEqual = (Object, Object, Function) => boolean;*/
  exports.fastRGLPropsEqual = require("./fastRGLPropsEqual");

  // Like the above, but a lot simpler.
  function fastPositionEqual(a /*: Position*/, b /*: Position*/) /*: boolean*/{
    return a.left === b.left && a.top === b.top && a.width === b.width && a.height === b.height;
  }

  /**
   * Given two layoutitems, check if they collide.
   */
  function collides(l1 /*: LayoutItem*/, l2 /*: LayoutItem*/) /*: boolean*/{
    if (l1.i === l2.i) return false; // same element
    if (l1.x + l1.w <= l2.x) return false; // l1 is left of l2
    if (l1.x >= l2.x + l2.w) return false; // l1 is right of l2
    if (l1.y + l1.h <= l2.y) return false; // l1 is above l2
    if (l1.y >= l2.y + l2.h) return false; // l1 is below l2
    return true; // boxes overlap
  }

  /**
   * Given a layout, compact it. This involves going down each y coordinate and removing gaps
   * between items.
   *
   * Does not modify layout items (clones). Creates a new layout array.
   *
   * @param  {Array} layout Layout.
   * @param  {Boolean} verticalCompact Whether or not to compact the layout
   *   vertically.
   * @param  {Boolean} allowOverlap When `true`, allows overlapping grid items.
   * @return {Array}       Compacted Layout.
   */
  function compact(layout /*: Layout*/, compactType /*: CompactType*/, cols /*: number*/, allowOverlap /*: ?boolean*/) /*: Layout*/{
    // Statics go in the compareWith array right away so items flow around them.
    var compareWith = getStatics(layout);
    // We go through the items by row and column.
    var sorted = sortLayoutItems(layout, compactType);
    // Holding for new items.
    var out = Array(layout.length);
    for (var i = 0, len = sorted.length; i < len; i++) {
      var l = cloneLayoutItem(sorted[i]);

      // Don't move static elements
      if (!l["static"]) {
        l = compactItem(compareWith, l, compactType, cols, sorted, allowOverlap);

        // Add to comparison array. We only collide with items before this one.
        // Statics are already in this array.
        compareWith.push(l);
      }

      // Add to output array to make sure they still come out in the right order.
      out[layout.indexOf(sorted[i])] = l;

      // Clear moved flag, if it exists.
      l.moved = false;
    }
    return out;
  }
  var heightWidth = {
    x: "w",
    y: "h"
  };
  /**
   * Before moving item down, it will check if the movement will cause collisions and move those items down before.
   */
  function resolveCompactionCollision(layout /*: Layout*/, item /*: LayoutItem*/, moveToCoord /*: number*/, axis /*: "x" | "y"*/) {
    var sizeProp = heightWidth[axis];
    item[axis] += 1;
    var itemIndex = layout.map(function (layoutItem) {
      return layoutItem.i;
    }).indexOf(item.i);

    // Go through each item we collide with.
    for (var i = itemIndex + 1; i < layout.length; i++) {
      var otherItem = layout[i];
      // Ignore static items
      if (otherItem["static"]) continue;

      // Optimization: we can break early if we know we're past this el
      // We can do this b/c it's a sorted layout
      if (otherItem.y > item.y + item.h) break;
      if (collides(item, otherItem)) {
        resolveCompactionCollision(layout, otherItem, moveToCoord + item[sizeProp], axis);
      }
    }
    item[axis] = moveToCoord;
  }

  /**
   * Compact an item in the layout.
   *
   * Modifies item.
   *
   */
  function compactItem(compareWith /*: Layout*/, l /*: LayoutItem*/, compactType /*: CompactType*/, cols /*: number*/, fullLayout /*: Layout*/, allowOverlap /*: ?boolean*/) /*: LayoutItem*/{
    var compactV = compactType === "vertical";
    var compactH = compactType === "horizontal";
    if (compactV) {
      // Bottom 'y' possible is the bottom of the layout.
      // This allows you to do nice stuff like specify {y: Infinity}
      // This is here because the layout must be sorted in order to get the correct bottom `y`.
      l.y = Math.min(bottom(compareWith), l.y);
      // Move the element up as far as it can go without colliding.
      while (l.y > 0 && !getFirstCollision(compareWith, l)) {
        l.y--;
      }
    } else if (compactH) {
      // Move the element left as far as it can go without colliding.
      while (l.x > 0 && !getFirstCollision(compareWith, l)) {
        l.x--;
      }
    }

    // Move it down, and keep moving it down if it's colliding.
    var collides;
    // Checking the compactType null value to avoid breaking the layout when overlapping is allowed.
    while ((collides = getFirstCollision(compareWith, l)) && !(compactType === null && allowOverlap)) {
      if (compactH) {
        resolveCompactionCollision(fullLayout, l, collides.x + collides.w, "x");
      } else {
        resolveCompactionCollision(fullLayout, l, collides.y + collides.h, "y");
      }
      // Since we can't grow without bounds horizontally, if we've overflown, let's move it down and try again.
      if (compactH && l.x + l.w > cols) {
        l.x = cols - l.w;
        l.y++;
        // ALso move element as left as we can
        while (l.x > 0 && !getFirstCollision(compareWith, l)) {
          l.x--;
        }
      }
    }

    // Ensure that there are no negative positions
    l.y = Math.max(l.y, 0);
    l.x = Math.max(l.x, 0);
    return l;
  }

  /**
   * Given a layout, make sure all elements fit within its bounds.
   *
   * Modifies layout items.
   *
   * @param  {Array} layout Layout array.
   * @param  {Number} bounds Number of columns.
   */
  function correctBounds(layout /*: Layout*/, bounds /*: { cols: number }*/) /*: Layout*/{
    var collidesWith = getStatics(layout);
    for (var i = 0, len = layout.length; i < len; i++) {
      var l = layout[i];
      // Overflows right
      if (l.x + l.w > bounds.cols) l.x = bounds.cols - l.w;
      // Overflows left
      if (l.x < 0) {
        l.x = 0;
        l.w = bounds.cols;
      }
      if (!l["static"]) collidesWith.push(l);else {
        // If this is static and collides with other statics, we must move it down.
        // We have to do something nicer than just letting them overlap.
        while (getFirstCollision(collidesWith, l)) {
          l.y++;
        }
      }
    }
    return layout;
  }

  /**
   * Get a layout item by ID. Used so we can override later on if necessary.
   *
   * @param  {Array}  layout Layout array.
   * @param  {String} id     ID
   * @return {LayoutItem}    Item at ID.
   */
  function getLayoutItem(layout /*: Layout*/, id /*: string*/) /*: ?LayoutItem*/{
    for (var i = 0, len = layout.length; i < len; i++) {
      if (layout[i].i === id) return layout[i];
    }
  }

  /**
   * Returns the first item this layout collides with.
   * It doesn't appear to matter which order we approach this from, although
   * perhaps that is the wrong thing to do.
   *
   * @param  {Object} layoutItem Layout item.
   * @return {Object|undefined}  A colliding layout item, or undefined.
   */
  function getFirstCollision(layout /*: Layout*/, layoutItem /*: LayoutItem*/) /*: ?LayoutItem*/{
    for (var i = 0, len = layout.length; i < len; i++) {
      if (collides(layout[i], layoutItem)) return layout[i];
    }
  }
  function getAllCollisions(layout /*: Layout*/, layoutItem /*: LayoutItem*/) /*: Array<LayoutItem>*/{
    return layout.filter(function (l) {
      return collides(l, layoutItem);
    });
  }

  /**
   * Get all static elements.
   * @param  {Array} layout Array of layout objects.
   * @return {Array}        Array of static layout items..
   */
  function getStatics(layout /*: Layout*/) /*: Array<LayoutItem>*/{
    return layout.filter(function (l) {
      return l["static"];
    });
  }

  /**
   * Move an element. Responsible for doing cascading movements of other elements.
   *
   * Modifies layout items.
   *
   * @param  {Array}      layout            Full layout to modify.
   * @param  {LayoutItem} l                 element to move.
   * @param  {Number}     [x]               X position in grid units.
   * @param  {Number}     [y]               Y position in grid units.
   */
  function moveElement(layout /*: Layout*/, l /*: LayoutItem*/, x /*: ?number*/, y /*: ?number*/, isUserAction /*: ?boolean*/, preventCollision /*: ?boolean*/, compactType /*: CompactType*/, cols /*: number*/, allowOverlap /*: ?boolean*/) /*: Layout*/{
    // If this is static and not explicitly enabled as draggable,
    // no move is possible, so we can short-circuit this immediately.
    if (l["static"] && l.isDraggable !== true) return layout;

    // Short-circuit if nothing to do.
    if (l.y === y && l.x === x) return layout;
    log("Moving element ".concat(l.i, " to [").concat(String(x), ",").concat(String(y), "] from [").concat(l.x, ",").concat(l.y, "]"));
    var oldX = l.x;
    var oldY = l.y;

    // This is quite a bit faster than extending the object
    if (typeof x === "number") l.x = x;
    if (typeof y === "number") l.y = y;
    l.moved = true;

    // If this collides with anything, move it.
    // When doing this comparison, we have to sort the items we compare with
    // to ensure, in the case of multiple collisions, that we're getting the
    // nearest collision.
    var sorted = sortLayoutItems(layout, compactType);
    var movingUp = compactType === "vertical" && typeof y === "number" ? oldY >= y : compactType === "horizontal" && typeof x === "number" ? oldX >= x : false;
    // $FlowIgnore acceptable modification of read-only array as it was recently cloned
    if (movingUp) sorted = sorted.reverse();
    var collisions = getAllCollisions(sorted, l);
    var hasCollisions = collisions.length > 0;

    // We may have collisions. We can short-circuit if we've turned off collisions or
    // allowed overlap.
    if (hasCollisions && allowOverlap) {
      // Easy, we don't need to resolve collisions. But we *did* change the layout,
      // so clone it on the way out.
      return cloneLayout(layout);
    } else if (hasCollisions && preventCollision) {
      // If we are preventing collision but not allowing overlap, we need to
      // revert the position of this element so it goes to where it came from, rather
      // than the user's desired location.
      log("Collision prevented on ".concat(l.i, ", reverting."));
      l.x = oldX;
      l.y = oldY;
      l.moved = false;
      return layout; // did not change so don't clone
    }

    // Move each item that collides away from this element.
    for (var i = 0, len = collisions.length; i < len; i++) {
      var collision = collisions[i];
      log("Resolving collision between ".concat(l.i, " at [").concat(l.x, ",").concat(l.y, "] and ").concat(collision.i, " at [").concat(collision.x, ",").concat(collision.y, "]"));

      // Short circuit so we can't infinite loop
      if (collision.moved) continue;

      // Don't move static items - we have to move *this* element away
      if (collision["static"]) {
        layout = moveElementAwayFromCollision(layout, collision, l, isUserAction, compactType);
      } else {
        layout = moveElementAwayFromCollision(layout, l, collision, isUserAction, compactType);
      }
    }
    return layout;
  }

  /**
   * This is where the magic needs to happen - given a collision, move an element away from the collision.
   * We attempt to move it up if there's room, otherwise it goes below.
   *
   * @param  {Array} layout            Full layout to modify.
   * @param  {LayoutItem} collidesWith Layout item we're colliding with.
   * @param  {LayoutItem} itemToMove   Layout item we're moving.
   */
  function moveElementAwayFromCollision(layout /*: Layout*/, collidesWith /*: LayoutItem*/, itemToMove /*: LayoutItem*/, isUserAction /*: ?boolean*/, compactType /*: CompactType*/, cols /*: number*/) /*: Layout*/{
    var compactH = compactType === "horizontal";
    // Compact vertically if not set to horizontal
    var compactV = compactType === "vertical";
    var preventCollision = collidesWith["static"]; // we're already colliding (not for static items)

    // If there is enough space above the collision to put this element, move it there.
    // We only do this on the main collision as this can get funky in cascades and cause
    // unwanted swapping behavior.
    if (isUserAction) {
      // Reset isUserAction flag because we're not in the main collision anymore.
      isUserAction = false;

      // Make a mock item so we don't modify the item here, only modify in moveElement.
      var fakeItem /*: LayoutItem*/ = {
        x: compactH ? Math.max(collidesWith.x - itemToMove.w, 0) : itemToMove.x,
        y: compactV ? Math.max(collidesWith.y - itemToMove.h, 0) : itemToMove.y,
        w: itemToMove.w,
        h: itemToMove.h,
        i: "-1"
      };
      var firstCollision = getFirstCollision(layout, fakeItem);
      var collisionNorth = firstCollision && firstCollision.y + firstCollision.h > collidesWith.y;
      var collisionWest = firstCollision && collidesWith.x + collidesWith.w > firstCollision.x;

      // No collision? If so, we can go up there; otherwise, we'll end up moving down as normal
      if (!firstCollision) {
        log("Doing reverse collision on ".concat(itemToMove.i, " up to [").concat(fakeItem.x, ",").concat(fakeItem.y, "]."));
        return moveElement(layout, itemToMove, compactH ? fakeItem.x : undefined, compactV ? fakeItem.y : undefined, isUserAction, preventCollision, compactType);
      } else if (collisionNorth && compactV) {
        return moveElement(layout, itemToMove, undefined, collidesWith.y + 1, isUserAction, preventCollision, compactType);
      } else if (collisionNorth && compactType == null) {
        collidesWith.y = itemToMove.y;
        itemToMove.y = itemToMove.y + itemToMove.h;
        return layout;
      } else if (collisionWest && compactH) {
        return moveElement(layout, collidesWith, itemToMove.x, undefined, isUserAction, preventCollision, compactType);
      }
    }
    var newX = compactH ? itemToMove.x + 1 : undefined;
    var newY = compactV ? itemToMove.y + 1 : undefined;
    if (newX == null && newY == null) {
      return layout;
    }
    return moveElement(layout, itemToMove, compactH ? itemToMove.x + 1 : undefined, compactV ? itemToMove.y + 1 : undefined, isUserAction, preventCollision, compactType);
  }

  /**
   * Helper to convert a number to a percentage string.
   *
   * @param  {Number} num Any number
   * @return {String}     That number as a percentage.
   */
  function perc(num /*: number*/) /*: string*/{
    return num * 100 + "%";
  }

  /**
   * Helper functions to constrain dimensions of a GridItem
   */
  var constrainWidth = function constrainWidth(left /*: number*/, currentWidth /*: number*/, newWidth /*: number*/, containerWidth /*: number*/) {
    return left + newWidth > containerWidth ? currentWidth : newWidth;
  };
  var constrainHeight = function constrainHeight(top /*: number*/, currentHeight /*: number*/, newHeight /*: number*/) {
    return top < 0 ? currentHeight : newHeight;
  };
  var constrainLeft = function constrainLeft(left /*: number*/) {
    return Math.max(0, left);
  };
  var constrainTop = function constrainTop(top /*: number*/) {
    return Math.max(0, top);
  };
  var resizeNorth = function resizeNorth(currentSize, _ref, _containerWidth) {
    var left = _ref.left,
      height = _ref.height,
      width = _ref.width;
    var top = currentSize.top - (height - currentSize.height);
    return {
      left: left,
      width: width,
      height: constrainHeight(top, currentSize.height, height),
      top: constrainTop(top)
    };
  };
  var resizeEast = function resizeEast(currentSize, _ref2, containerWidth) {
    var top = _ref2.top,
      left = _ref2.left,
      height = _ref2.height,
      width = _ref2.width;
    return {
      top: top,
      height: height,
      width: constrainWidth(currentSize.left, currentSize.width, width, containerWidth),
      left: constrainLeft(left)
    };
  };
  var resizeWest = function resizeWest(currentSize, _ref3, containerWidth) {
    var top = _ref3.top,
      height = _ref3.height,
      width = _ref3.width;
    var left = currentSize.left - (width - currentSize.width);
    return {
      height: height,
      width: left < 0 ? currentSize.width : constrainWidth(currentSize.left, currentSize.width, width, containerWidth),
      top: constrainTop(top),
      left: constrainLeft(left)
    };
  };
  var resizeSouth = function resizeSouth(currentSize, _ref4, containerWidth) {
    var top = _ref4.top,
      left = _ref4.left,
      height = _ref4.height,
      width = _ref4.width;
    return {
      width: width,
      left: left,
      height: constrainHeight(top, currentSize.height, height),
      top: constrainTop(top)
    };
  };
  var resizeNorthEast = function resizeNorthEast() {
    return resizeNorth(arguments.length <= 0 ? undefined : arguments[0], resizeEast.apply(void 0, arguments));
  };
  var resizeNorthWest = function resizeNorthWest() {
    return resizeNorth(arguments.length <= 0 ? undefined : arguments[0], resizeWest.apply(void 0, arguments));
  };
  var resizeSouthEast = function resizeSouthEast() {
    return resizeSouth(arguments.length <= 0 ? undefined : arguments[0], resizeEast.apply(void 0, arguments));
  };
  var resizeSouthWest = function resizeSouthWest() {
    return resizeSouth(arguments.length <= 0 ? undefined : arguments[0], resizeWest.apply(void 0, arguments));
  };
  var ordinalResizeHandlerMap = {
    n: resizeNorth,
    ne: resizeNorthEast,
    e: resizeEast,
    se: resizeSouthEast,
    s: resizeSouth,
    sw: resizeSouthWest,
    w: resizeWest,
    nw: resizeNorthWest
  };

  /**
   * Helper for clamping width and position when resizing an item.
   */
  function resizeItemInDirection(direction /*: ResizeHandleAxis*/, currentSize /*: Position*/, newSize /*: Position*/, containerWidth /*: number*/) /*: Position*/{
    var ordinalHandler = ordinalResizeHandlerMap[direction];
    // Shouldn't be possible given types; that said, don't fail hard
    if (!ordinalHandler) return newSize;
    return ordinalHandler(currentSize, _objectSpread2(_objectSpread2({}, currentSize), newSize), containerWidth);
  }
  function setTransform(_ref5 /*:: */) /*: Object*/{
    var top = _ref5.top,
      left = _ref5.left,
      width = _ref5.width,
      height = _ref5.height;
    // Replace unitless items with px
    var translate = "translate(".concat(left, "px,").concat(top, "px)");
    return {
      transform: translate,
      WebkitTransform: translate,
      MozTransform: translate,
      msTransform: translate,
      OTransform: translate,
      width: "".concat(width, "px"),
      height: "".concat(height, "px"),
      position: "absolute"
    };
  }
  function setTopLeft(_ref6 /*:: */) /*: Object*/{
    var top = _ref6.top,
      left = _ref6.left,
      width = _ref6.width,
      height = _ref6.height;
    return {
      top: "".concat(top, "px"),
      left: "".concat(left, "px"),
      width: "".concat(width, "px"),
      height: "".concat(height, "px"),
      position: "absolute"
    };
  }

  /**
   * Get layout items sorted from top left to right and down.
   *
   * @return {Array} Array of layout objects.
   * @return {Array}        Layout, sorted static items first.
   */
  function sortLayoutItems(layout /*: Layout*/, compactType /*: CompactType*/) /*: Layout*/{
    if (compactType === "horizontal") return sortLayoutItemsByColRow(layout);
    if (compactType === "vertical") return sortLayoutItemsByRowCol(layout);else return layout;
  }

  /**
   * Sort layout items by row ascending and column ascending.
   *
   * Does not modify Layout.
   */
  function sortLayoutItemsByRowCol(layout /*: Layout*/) /*: Layout*/{
    // Slice to clone array as sort modifies
    return layout.slice(0).sort(function (a, b) {
      if (a.y > b.y || a.y === b.y && a.x > b.x) {
        return 1;
      } else if (a.y === b.y && a.x === b.x) {
        // Without this, we can get different sort results in IE vs. Chrome/FF
        return 0;
      }
      return -1;
    });
  }

  /**
   * Sort layout items by column ascending then row ascending.
   *
   * Does not modify Layout.
   */
  function sortLayoutItemsByColRow(layout /*: Layout*/) /*: Layout*/{
    return layout.slice(0).sort(function (a, b) {
      if (a.x > b.x || a.x === b.x && a.y > b.y) {
        return 1;
      }
      return -1;
    });
  }

  /**
   * Generate a layout using the initialLayout and children as a template.
   * Missing entries will be added, extraneous ones will be truncated.
   *
   * Does not modify initialLayout.
   *
   * @param  {Array}  initialLayout Layout passed in through props.
   * @param  {String} breakpoint    Current responsive breakpoint.
   * @param  {?String} compact      Compaction option.
   * @return {Array}                Working layout.
   */
  function synchronizeLayoutWithChildren(initialLayout /*: Layout*/, children /*: ReactChildren*/, cols /*: number*/, compactType /*: CompactType*/, allowOverlap /*: ?boolean*/) /*: Layout*/{
    initialLayout = initialLayout || [];

    // Generate one layout item per child.
    var layout /*: LayoutItem[]*/ = [];
    _react["default"].Children.forEach(children, function (child /*: ReactElement<any>*/) {
      // Child may not exist
      if ((child === null || child === void 0 ? void 0 : child.key) == null) return;
      var exists = getLayoutItem(initialLayout, String(child.key));
      var g = child.props["data-grid"];
      // Don't overwrite the layout item if it's already in the initial layout.
      // If it has a `data-grid` property, prefer that over what's in the layout.
      if (exists && g == null) {
        layout.push(cloneLayoutItem(exists));
      } else {
        // Hey, this item has a data-grid property, use it.
        if (g) {
          // FIXME clone not really necessary here
          layout.push(cloneLayoutItem(_objectSpread2(_objectSpread2({}, g), {}, {
            i: child.key
          })));
        } else {
          // Nothing provided: ensure this is added to the bottom
          // FIXME clone not really necessary here
          layout.push(cloneLayoutItem({
            w: 1,
            h: 1,
            x: 0,
            y: bottom(layout),
            i: String(child.key)
          }));
        }
      }
    });

    // Correct the layout.
    var correctedLayout = correctBounds(layout, {
      cols: cols
    });
    return allowOverlap ? correctedLayout : compact(correctedLayout, compactType, cols);
  }

  /**
   * Validate a layout. Throws errors.
   *
   * @param  {Array}  layout        Array of layout items.
   * @param  {String} [contextName] Context name for errors.
   * @throw  {Error}                Validation error.
   */
  function validateLayout(layout /*: Layout*/) /*: void*/{
    var contextName /*: string*/ = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "Layout";
    var subProps = ["x", "y", "w", "h"];
    if (!Array.isArray(layout)) throw new Error(contextName + " must be an array!");
    for (var i = 0, len = layout.length; i < len; i++) {
      var item = layout[i];
      for (var j = 0; j < subProps.length; j++) {
        var key = subProps[j];
        var value = item[key];
        if (typeof value !== "number" || Number.isNaN(value)) {
          throw new Error("ReactGridLayout: ".concat(contextName, "[").concat(i, "].").concat(key, " must be a number! Received: ").concat(value, " (").concat(_typeof(value), ")"));
        }
      }
      if (typeof item.i !== "undefined" && typeof item.i !== "string") {
        throw new Error("ReactGridLayout: ".concat(contextName, "[").concat(i, "].i must be a string! Received: ").concat(item.i, " (").concat(_typeof(item.i), ")"));
      }
    }
  }

  // Legacy support for verticalCompact: false
  function compactType(props /*: ?{ verticalCompact: boolean, compactType: CompactType }*/) /*: CompactType*/{
    var _ref7 = props || {},
      verticalCompact = _ref7.verticalCompact,
      compactType = _ref7.compactType;
    return verticalCompact === false ? null : compactType;
  }
  function log() {
    return;
  }
  var noop = function noop() {};
  exports.noop = noop;

  var utils = /*#__PURE__*/Object.freeze({
    __proto__: null
  });

  var require$$1 = /*@__PURE__*/getAugmentedNamespace(utils);

  var calculateUtils = {};

  Object.defineProperty(calculateUtils, "__esModule", {
    value: true
  });
  calculateUtils.calcGridColWidth = calcGridColWidth;
  calculateUtils.calcGridItemPosition = calcGridItemPosition;
  calculateUtils.calcGridItemWHPx = calcGridItemWHPx;
  calculateUtils.calcWH = calcWH;
  calculateUtils.calcXY = calcXY;
  calculateUtils.clamp = clamp;
  /*:: import type { Position } from "./utils";*/
  /*:: export type PositionParams = {
    margin: [number, number],
    containerPadding: [number, number],
    containerWidth: number,
    cols: number,
    rowHeight: number,
    maxRows: number
  };*/
  // Helper for generating column width
  function calcGridColWidth(positionParams /*: PositionParams*/) /*: number*/{
    var margin = positionParams.margin,
      containerPadding = positionParams.containerPadding,
      containerWidth = positionParams.containerWidth,
      cols = positionParams.cols;
    return (containerWidth - margin[0] * (cols - 1) - containerPadding[0] * 2) / cols;
  }

  // This can either be called:
  // calcGridItemWHPx(w, colWidth, margin[0])
  // or
  // calcGridItemWHPx(h, rowHeight, margin[1])
  function calcGridItemWHPx(gridUnits /*: number*/, colOrRowSize /*: number*/, marginPx /*: number*/) /*: number*/{
    // 0 * Infinity === NaN, which causes problems with resize contraints
    if (!Number.isFinite(gridUnits)) return gridUnits;
    return Math.round(colOrRowSize * gridUnits + Math.max(0, gridUnits - 1) * marginPx);
  }

  /**
   * Return position on the page given an x, y, w, h.
   * left, top, width, height are all in pixels.
   * @param  {PositionParams} positionParams  Parameters of grid needed for coordinates calculations.
   * @param  {Number}  x                      X coordinate in grid units.
   * @param  {Number}  y                      Y coordinate in grid units.
   * @param  {Number}  w                      W coordinate in grid units.
   * @param  {Number}  h                      H coordinate in grid units.
   * @return {Position}                       Object containing coords.
   */
  function calcGridItemPosition(positionParams /*: PositionParams*/, x /*: number*/, y /*: number*/, w /*: number*/, h /*: number*/, state /*: ?Object*/) /*: Position*/{
    var margin = positionParams.margin,
      containerPadding = positionParams.containerPadding,
      rowHeight = positionParams.rowHeight;
    var colWidth = calcGridColWidth(positionParams);
    var out = {};

    // If resizing, use the exact width and height as returned from resizing callbacks.
    if (state && state.resizing) {
      out.width = Math.round(state.resizing.width);
      out.height = Math.round(state.resizing.height);
    }
    // Otherwise, calculate from grid units.
    else {
      out.width = calcGridItemWHPx(w, colWidth, margin[0]);
      out.height = calcGridItemWHPx(h, rowHeight, margin[1]);
    }

    // If dragging, use the exact width and height as returned from dragging callbacks.
    if (state && state.dragging) {
      out.top = Math.round(state.dragging.top);
      out.left = Math.round(state.dragging.left);
    } else if (state && state.resizing && typeof state.resizing.top === "number" && typeof state.resizing.left === "number") {
      out.top = Math.round(state.resizing.top);
      out.left = Math.round(state.resizing.left);
    }
    // Otherwise, calculate from grid units.
    else {
      out.top = Math.round((rowHeight + margin[1]) * y + containerPadding[1]);
      out.left = Math.round((colWidth + margin[0]) * x + containerPadding[0]);
    }
    return out;
  }

  /**
   * Translate x and y coordinates from pixels to grid units.
   * @param  {PositionParams} positionParams  Parameters of grid needed for coordinates calculations.
   * @param  {Number} top                     Top position (relative to parent) in pixels.
   * @param  {Number} left                    Left position (relative to parent) in pixels.
   * @param  {Number} w                       W coordinate in grid units.
   * @param  {Number} h                       H coordinate in grid units.
   * @return {Object}                         x and y in grid units.
   */
  function calcXY(positionParams /*: PositionParams*/, top /*: number*/, left /*: number*/, w /*: number*/, h /*: number*/) /*: { x: number, y: number }*/{
    var margin = positionParams.margin,
      containerPadding = positionParams.containerPadding,
      cols = positionParams.cols,
      rowHeight = positionParams.rowHeight,
      maxRows = positionParams.maxRows;
    var colWidth = calcGridColWidth(positionParams);

    // left = containerPaddingX + x * (colWidth + marginX)
    // x * (colWidth + marginX) = left - containerPaddingX
    // x = (left - containerPaddingX) / (colWidth + marginX)
    var x = Math.round((left - containerPadding[0]) / (colWidth + margin[0]));
    var y = Math.round((top - containerPadding[1]) / (rowHeight + margin[1]));

    // Capping
    x = clamp(x, 0, cols - w);
    y = clamp(y, 0, maxRows - h);
    return {
      x: x,
      y: y
    };
  }

  /**
   * Given a height and width in pixel values, calculate grid units.
   * @param  {PositionParams} positionParams  Parameters of grid needed for coordinates calcluations.
   * @param  {Number} height                  Height in pixels.
   * @param  {Number} width                   Width in pixels.
   * @param  {Number} x                       X coordinate in grid units.
   * @param  {Number} y                       Y coordinate in grid units.
   * @param {String} handle Resize Handle.
   * @return {Object}                         w, h as grid units.
   */
  function calcWH(positionParams /*: PositionParams*/, width /*: number*/, height /*: number*/, x /*: number*/, y /*: number*/, handle /*: string*/) /*: { w: number, h: number }*/{
    var margin = positionParams.margin,
      maxRows = positionParams.maxRows,
      cols = positionParams.cols,
      rowHeight = positionParams.rowHeight;
    var colWidth = calcGridColWidth(positionParams);

    // width = colWidth * w - (margin * (w - 1))
    // ...
    // w = (width + margin) / (colWidth + margin)
    var w = Math.round((width + margin[0]) / (colWidth + margin[0]));
    var h = Math.round((height + margin[1]) / (rowHeight + margin[1]));

    // Capping
    var _w = clamp(w, 0, cols - x);
    var _h = clamp(h, 0, maxRows - y);
    if (["sw", "w", "nw"].indexOf(handle) !== -1) {
      _w = clamp(w, 0, cols);
    }
    if (["nw", "n", "ne"].indexOf(handle) !== -1) {
      _h = clamp(h, 0, maxRows);
    }
    return {
      w: _w,
      h: _h
    };
  }

  // Similar to _.clamp
  function clamp(num /*: number*/, lowerBound /*: number*/, upperBound /*: number*/) /*: number*/{
    return Math.max(Math.min(num, upperBound), lowerBound);
  }

  var _excluded$1 = ["breakpoint", "breakpoints", "cols", "layouts", "margin", "containerPadding", "onBreakpointChange", "onLayoutChange", "onWidthChange"];
  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports["default"] = void 0;
  var React$2 = _interopRequireWildcard$1(require("react"));
  var _propTypes$1 = _interopRequireDefault$1(require("prop-types"));
  var _fastEquals = require("fast-equals");
  var _utils$1 = require("./utils");
  var _responsiveUtils = require("./responsiveUtils");
  var _ReactGridLayout = _interopRequireDefault$1(require("./ReactGridLayout"));
  function _interopRequireDefault$1(e) {
    return e && e.__esModule ? e : {
      "default": e
    };
  }
  function _interopRequireWildcard$1(e, t) {
    if ("function" == typeof WeakMap) var r = new WeakMap(),
      n = new WeakMap();
    return (_interopRequireWildcard$1 = function _interopRequireWildcard(e, t) {
      if (!t && e && e.__esModule) return e;
      var o,
        i,
        f = {
          __proto__: null,
          "default": e
        };
      if (null === e || "object" != _typeof(e) && "function" != typeof e) return f;
      if (o = t ? n : r) {
        if (o.has(e)) return o.get(e);
        o.set(e, f);
      }
      for (var _t in e) "default" !== _t && {}.hasOwnProperty.call(e, _t) && ((i = (o = Object.defineProperty) && Object.getOwnPropertyDescriptor(e, _t)) && (i.get || i.set) ? o(f, _t, i) : f[_t] = e[_t]);
      return f;
    })(e, t);
  }
  function _extends$1() {
    return _extends$1 = Object.assign ? Object.assign.bind() : function (n) {
      for (var e = 1; e < arguments.length; e++) {
        var t = arguments[e];
        for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]);
      }
      return n;
    }, _extends$1.apply(null, arguments);
  }
  function _defineProperty$1(e, r, t) {
    return (r = _toPropertyKey$1(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: true,
      configurable: true,
      writable: true
    }) : e[r] = t, e;
  }
  function _toPropertyKey$1(t) {
    var i = _toPrimitive$1(t, "string");
    return "symbol" == _typeof(i) ? i : i + "";
  }
  function _toPrimitive$1(t, r) {
    if ("object" != _typeof(t) || !t) return t;
    var e = t[Symbol.toPrimitive];
    if (void 0 !== e) {
      var i = e.call(t, r);
      if ("object" != _typeof(i)) return i;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return ("string" === r ? String : Number)(t);
  } /*:: import { type Layout, type Pick } from "./utils";*/ /*:: import { type ResponsiveLayout, type OnLayoutChangeCallback, type Breakpoints } from "./responsiveUtils";*/
  // $FlowFixMe[method-unbinding]
  var type = function type(obj) {
    return Object.prototype.toString.call(obj);
  };

  /**
   * Get a value of margin or containerPadding.
   *
   * @param  {Array | Object} param Margin | containerPadding, e.g. [10, 10] | {lg: [10, 10], ...}.
   * @param  {String} breakpoint   Breakpoint: lg, md, sm, xs and etc.
   * @return {Array}
   */
  function getIndentationValue /*:: <T: ?[number, number]>*/(param /*: { [key: string]: T } | T*/, breakpoint /*: string*/) /*: T*/{
    // $FlowIgnore TODO fix this typedef
    if (param == null) return null;
    // $FlowIgnore TODO fix this typedef
    return Array.isArray(param) ? param : param[breakpoint];
  }
  /*:: type State = {
    layout: Layout,
    breakpoint: string,
    cols: number,
    layouts?: ResponsiveLayout<string>
  };*/
  /*:: type Props<Breakpoint: string = string> = {|
    ...React.ElementConfig<typeof ReactGridLayout>,

    // Responsive config
    breakpoint?: ?Breakpoint,
    breakpoints: Breakpoints<Breakpoint>,
    cols: { [key: Breakpoint]: number },
    layouts: ResponsiveLayout<Breakpoint>,
    width: number,
    margin: { [key: Breakpoint]: [number, number] } | [number, number],
    /* prettier-ignore *-/
    containerPadding: { [key: Breakpoint]: ?[number, number] } | ?[number, number],

    // Callbacks
    onBreakpointChange: (Breakpoint, cols: number) => void,
    onLayoutChange: OnLayoutChangeCallback,
    onWidthChange: (
      containerWidth: number,
      margin: [number, number],
      cols: number,
      containerPadding: ?[number, number]
    ) => void
  |};*/
  /*:: type DefaultProps = Pick<
    Props<>,
    {|
      allowOverlap: 0,
      breakpoints: 0,
      cols: 0,
      containerPadding: 0,
      layouts: 0,
      margin: 0,
      onBreakpointChange: 0,
      onLayoutChange: 0,
      onWidthChange: 0
    |}
  >;*/
  var ResponsiveReactGridLayout = /*#__PURE__*/function (_React$Component) {
    function ResponsiveReactGridLayout() {
      var _this;
      _classCallCheck(this, ResponsiveReactGridLayout);
      _this = _callSuper(this, ResponsiveReactGridLayout, arguments);
      _defineProperty$1(_this, "state", _this.generateInitialState());
      // wrap layouts so we do not need to pass layouts to child
      _defineProperty$1(_this, "onLayoutChange", function (layout /*: Layout*/) {
        _this.props.onLayoutChange(layout, _objectSpread2(_objectSpread2({}, _this.props.layouts), {}, _defineProperty$3({}, _this.state.breakpoint, layout)));
      });
      return _this;
    }
    _inherits(ResponsiveReactGridLayout, _React$Component);
    return _createClass(ResponsiveReactGridLayout, [{
      key: "generateInitialState",
      value: function generateInitialState() /*: State*/{
        var _this$props = this.props,
          width = _this$props.width,
          breakpoints = _this$props.breakpoints,
          layouts = _this$props.layouts,
          cols = _this$props.cols;
        var breakpoint = (0, _responsiveUtils.getBreakpointFromWidth)(breakpoints, width);
        var colNo = (0, _responsiveUtils.getColsFromBreakpoint)(breakpoint, cols);
        // verticalCompact compatibility, now deprecated
        var compactType = this.props.verticalCompact === false ? null : this.props.compactType;
        // Get the initial layout. This can tricky; we try to generate one however possible if one doesn't exist
        // for this layout.
        var initialLayout = (0, _responsiveUtils.findOrGenerateResponsiveLayout)(layouts, breakpoints, breakpoint, breakpoint, colNo, compactType);
        return {
          layout: initialLayout,
          breakpoint: breakpoint,
          cols: colNo
        };
      }
    }, {
      key: "componentDidUpdate",
      value: function componentDidUpdate(prevProps /*: Props<*>*/) {
        // Allow parent to set width or breakpoint directly.
        if (this.props.width != prevProps.width || this.props.breakpoint !== prevProps.breakpoint || !(0, _fastEquals.deepEqual)(this.props.breakpoints, prevProps.breakpoints) || !(0, _fastEquals.deepEqual)(this.props.cols, prevProps.cols)) {
          this.onWidthChange(prevProps);
        }
      }
      /**
       * When the width changes work through breakpoints and reset state with the new width & breakpoint.
       * Width changes are necessary to figure out the widget widths.
       */
    }, {
      key: "onWidthChange",
      value: function onWidthChange(prevProps /*: Props<*>*/) {
        var _this$props2 = this.props,
          breakpoints = _this$props2.breakpoints,
          cols = _this$props2.cols,
          layouts = _this$props2.layouts,
          compactType = _this$props2.compactType;
        var newBreakpoint = this.props.breakpoint || (0, _responsiveUtils.getBreakpointFromWidth)(this.props.breakpoints, this.props.width);
        var lastBreakpoint = this.state.breakpoint;
        var newCols /*: number*/ = (0, _responsiveUtils.getColsFromBreakpoint)(newBreakpoint, cols);
        var newLayouts = _objectSpread2({}, layouts);

        // Breakpoint change
        if (lastBreakpoint !== newBreakpoint || prevProps.breakpoints !== breakpoints || prevProps.cols !== cols) {
          // Preserve the current layout if the current breakpoint is not present in the next layouts.
          if (!(lastBreakpoint in newLayouts)) newLayouts[lastBreakpoint] = (0, _utils$1.cloneLayout)(this.state.layout);

          // Find or generate a new layout.
          var layout = (0, _responsiveUtils.findOrGenerateResponsiveLayout)(newLayouts, breakpoints, newBreakpoint, lastBreakpoint, newCols, compactType);

          // This adds missing items.
          layout = (0, _utils$1.synchronizeLayoutWithChildren)(layout, this.props.children, newCols, compactType, this.props.allowOverlap);

          // Store the new layout.
          newLayouts[newBreakpoint] = layout;

          // callbacks
          this.props.onBreakpointChange(newBreakpoint, newCols);
          this.props.onLayoutChange(layout, newLayouts);
          this.setState({
            breakpoint: newBreakpoint,
            layout: layout,
            cols: newCols
          });
        }
        var margin = getIndentationValue(this.props.margin, newBreakpoint);
        var containerPadding = getIndentationValue(this.props.containerPadding, newBreakpoint);

        //call onWidthChange on every change of width, not only on breakpoint changes
        this.props.onWidthChange(this.props.width, margin, newCols, containerPadding);
      }
    }, {
      key: "render",
      value: function render() /*: React.Element<typeof ReactGridLayout>*/{
        /* eslint-disable no-unused-vars */
        var _this$props3 = this.props;
          _this$props3.breakpoint;
          _this$props3.breakpoints;
          _this$props3.cols;
          _this$props3.layouts;
          var margin = _this$props3.margin,
          containerPadding = _this$props3.containerPadding;
          _this$props3.onBreakpointChange;
          _this$props3.onLayoutChange;
          _this$props3.onWidthChange;
          var other = _objectWithoutProperties(_this$props3, _excluded$1);
        /* eslint-enable no-unused-vars */

        return /*#__PURE__*/React$2.createElement(_ReactGridLayout["default"], _extends$1({}, other, {
          // $FlowIgnore should allow nullable here due to DefaultProps
          margin: getIndentationValue(margin, this.state.breakpoint),
          containerPadding: getIndentationValue(containerPadding, this.state.breakpoint),
          onLayoutChange: this.onLayoutChange,
          layout: this.state.layout,
          cols: this.state.cols
        }));
      }
    }], [{
      key: "getDerivedStateFromProps",
      value: function getDerivedStateFromProps(nextProps /*: Props<*>*/, prevState /*: State*/) /*: ?$Shape<State>*/{
        if (!(0, _fastEquals.deepEqual)(nextProps.layouts, prevState.layouts)) {
          // Allow parent to set layouts directly.
          var breakpoint = prevState.breakpoint,
            cols = prevState.cols;

          // Since we're setting an entirely new layout object, we must generate a new responsive layout
          // if one does not exist.
          var newLayout = (0, _responsiveUtils.findOrGenerateResponsiveLayout)(nextProps.layouts, nextProps.breakpoints, breakpoint, breakpoint, cols, nextProps.compactType);
          return {
            layout: newLayout,
            layouts: nextProps.layouts
          };
        }
        return null;
      }
    }]);
  }(React$2.Component
  /*:: <
    Props<>,
    State
  >*/);
  exports["default"] = ResponsiveReactGridLayout;
  // This should only include propTypes needed in this code; RGL itself
  // will do validation of the rest props passed to it.
  _defineProperty$1(ResponsiveReactGridLayout, "propTypes", {
    //
    // Basic props
    //

    // Optional, but if you are managing width yourself you may want to set the breakpoint
    // yourself as well.
    breakpoint: _propTypes$1["default"].string,
    // {name: pxVal}, e.g. {lg: 1200, md: 996, sm: 768, xs: 480}
    breakpoints: _propTypes$1["default"].object,
    allowOverlap: _propTypes$1["default"].bool,
    // # of cols. This is a breakpoint -> cols map
    cols: _propTypes$1["default"].object,
    // # of margin. This is a breakpoint -> margin map
    // e.g. { lg: [5, 5], md: [10, 10], sm: [15, 15] }
    // Margin between items [x, y] in px
    // e.g. [10, 10]
    margin: _propTypes$1["default"].oneOfType([_propTypes$1["default"].array, _propTypes$1["default"].object]),
    // # of containerPadding. This is a breakpoint -> containerPadding map
    // e.g. { lg: [5, 5], md: [10, 10], sm: [15, 15] }
    // Padding inside the container [x, y] in px
    // e.g. [10, 10]
    containerPadding: _propTypes$1["default"].oneOfType([_propTypes$1["default"].array, _propTypes$1["default"].object]),
    // layouts is an object mapping breakpoints to layouts.
    // e.g. {lg: Layout, md: Layout, ...}
    layouts: function layouts(props /*: Props<>*/, propName /*: string*/) {
      if (type(props[propName]) !== "[object Object]") {
        throw new Error("Layout property must be an object. Received: " + type(props[propName]));
      }
      Object.keys(props[propName]).forEach(function (key) {
        if (!(key in props.breakpoints)) {
          throw new Error("Each key in layouts must align with a key in breakpoints.");
        }
        (0, _utils$1.validateLayout)(props.layouts[key], "layouts." + key);
      });
    },
    // The width of this component.
    // Required in this propTypes stanza because generateInitialState() will fail without it.
    width: _propTypes$1["default"].number.isRequired,
    //
    // Callbacks
    //

    // Calls back with breakpoint and new # cols
    onBreakpointChange: _propTypes$1["default"].func,
    // Callback so you can save the layout.
    // Calls back with (currentLayout, allLayouts). allLayouts are keyed by breakpoint.
    onLayoutChange: _propTypes$1["default"].func,
    // Calls back with (containerWidth, margin, cols, containerPadding)
    onWidthChange: _propTypes$1["default"].func
  });
  _defineProperty$1(ResponsiveReactGridLayout, "defaultProps", {
    breakpoints: {
      lg: 1200,
      md: 996,
      sm: 768,
      xs: 480,
      xxs: 0
    },
    cols: {
      lg: 12,
      md: 10,
      sm: 6,
      xs: 4,
      xxs: 2
    },
    containerPadding: {
      lg: null,
      md: null,
      sm: null,
      xs: null,
      xxs: null
    },
    layouts: {},
    margin: [10, 10],
    allowOverlap: false,
    onBreakpointChange: _utils$1.noop,
    onLayoutChange: _utils$1.noop,
    onWidthChange: _utils$1.noop
  });

  var ResponsiveReactGridLayout$1 = /*#__PURE__*/Object.freeze({
    __proto__: null
  });

  var require$$3 = /*@__PURE__*/getAugmentedNamespace(ResponsiveReactGridLayout$1);

  var responsiveUtils = {};

  Object.defineProperty(responsiveUtils, "__esModule", {
    value: true
  });
  responsiveUtils.findOrGenerateResponsiveLayout = findOrGenerateResponsiveLayout;
  responsiveUtils.getBreakpointFromWidth = getBreakpointFromWidth;
  responsiveUtils.getColsFromBreakpoint = getColsFromBreakpoint;
  responsiveUtils.sortBreakpoints = sortBreakpoints;
  var _utils = require$$1;
  /*:: import type { CompactType, Layout } from "./utils";*/
  /*:: export type Breakpoint = string;*/
  /*:: export type DefaultBreakpoints = "lg" | "md" | "sm" | "xs" | "xxs";*/
  /*:: export type ResponsiveLayout<T: Breakpoint> = {
    +[breakpoint: T]: Layout
  };*/
  // + indicates read-only
  /*:: export type Breakpoints<T: Breakpoint> = {
    +[breakpoint: T]: number
  };*/
  /*:: export type OnLayoutChangeCallback = (
    Layout,
    { [key: Breakpoint]: Layout }
  ) => void;*/
  /**
   * Given a width, find the highest breakpoint that matches is valid for it (width > breakpoint).
   *
   * @param  {Object} breakpoints Breakpoints object (e.g. {lg: 1200, md: 960, ...})
   * @param  {Number} width Screen width.
   * @return {String}       Highest breakpoint that is less than width.
   */
  function getBreakpointFromWidth(breakpoints /*: Breakpoints<Breakpoint>*/, width /*: number*/) /*: Breakpoint*/{
    var sorted = sortBreakpoints(breakpoints);
    var matching = sorted[0];
    for (var i = 1, len = sorted.length; i < len; i++) {
      var breakpointName = sorted[i];
      if (width > breakpoints[breakpointName]) matching = breakpointName;
    }
    return matching;
  }

  /**
   * Given a breakpoint, get the # of cols set for it.
   * @param  {String} breakpoint Breakpoint name.
   * @param  {Object} cols       Map of breakpoints to cols.
   * @return {Number}            Number of cols.
   */
  function getColsFromBreakpoint(breakpoint /*: Breakpoint*/, cols /*: Breakpoints<Breakpoint>*/) /*: number*/{
    if (!cols[breakpoint]) {
      throw new Error("ResponsiveReactGridLayout: `cols` entry for breakpoint " + breakpoint + " is missing!");
    }
    return cols[breakpoint];
  }

  /**
   * Given existing layouts and a new breakpoint, find or generate a new layout.
   *
   * This finds the layout above the new one and generates from it, if it exists.
   *
   * @param  {Object} layouts     Existing layouts.
   * @param  {Array} breakpoints All breakpoints.
   * @param  {String} breakpoint New breakpoint.
   * @param  {String} breakpoint Last breakpoint (for fallback).
   * @param  {Number} cols       Column count at new breakpoint.
   * @param  {Boolean} verticalCompact Whether or not to compact the layout
   *   vertically.
   * @return {Array}             New layout.
   */
  function findOrGenerateResponsiveLayout(layouts /*: ResponsiveLayout<Breakpoint>*/, breakpoints /*: Breakpoints<Breakpoint>*/, breakpoint /*: Breakpoint*/, lastBreakpoint /*: Breakpoint*/, cols /*: number*/, compactType /*: CompactType*/) /*: Layout*/{
    // If it already exists, just return it.
    if (layouts[breakpoint]) return (0, _utils.cloneLayout)(layouts[breakpoint]);
    // Find or generate the next layout
    var layout = layouts[lastBreakpoint];
    var breakpointsSorted = sortBreakpoints(breakpoints);
    var breakpointsAbove = breakpointsSorted.slice(breakpointsSorted.indexOf(breakpoint));
    for (var i = 0, len = breakpointsAbove.length; i < len; i++) {
      var b = breakpointsAbove[i];
      if (layouts[b]) {
        layout = layouts[b];
        break;
      }
    }
    layout = (0, _utils.cloneLayout)(layout || []); // clone layout so we don't modify existing items
    return (0, _utils.compact)((0, _utils.correctBounds)(layout, {
      cols: cols
    }), compactType, cols);
  }

  /**
   * Given breakpoints, return an array of breakpoints sorted by width. This is usually
   * e.g. ['xxs', 'xs', 'sm', ...]
   *
   * @param  {Object} breakpoints Key/value pair of breakpoint names to widths.
   * @return {Array}              Sorted breakpoints.
   */
  function sortBreakpoints(breakpoints /*: Breakpoints<Breakpoint>*/) /*: Array<Breakpoint>*/{
    var keys /*: Array<string>*/ = Object.keys(breakpoints);
    return keys.sort(function (a, b) {
      return breakpoints[a] - breakpoints[b];
    });
  }

  var _excluded = ["measureBeforeMount"];
  Object.defineProperty(exports, "__esModule", {
    value: true
  });
  exports["default"] = WidthProvideRGL;
  var React$1 = _interopRequireWildcard(require("react"));
  var _propTypes = _interopRequireDefault(require("prop-types"));
  var _resizeObserverPolyfill = _interopRequireDefault(require("resize-observer-polyfill"));
  var _clsx = _interopRequireDefault(require("clsx"));
  function _interopRequireDefault(e) {
    return e && e.__esModule ? e : {
      "default": e
    };
  }
  function _interopRequireWildcard(e, t) {
    if ("function" == typeof WeakMap) var r = new WeakMap(),
      n = new WeakMap();
    return (_interopRequireWildcard = function _interopRequireWildcard(e, t) {
      if (!t && e && e.__esModule) return e;
      var o,
        i,
        f = {
          __proto__: null,
          "default": e
        };
      if (null === e || "object" != _typeof(e) && "function" != typeof e) return f;
      if (o = t ? n : r) {
        if (o.has(e)) return o.get(e);
        o.set(e, f);
      }
      for (var _t in e) "default" !== _t && {}.hasOwnProperty.call(e, _t) && ((i = (o = Object.defineProperty) && Object.getOwnPropertyDescriptor(e, _t)) && (i.get || i.set) ? o(f, _t, i) : f[_t] = e[_t]);
      return f;
    })(e, t);
  }
  function _extends() {
    return _extends = Object.assign ? Object.assign.bind() : function (n) {
      for (var e = 1; e < arguments.length; e++) {
        var t = arguments[e];
        for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]);
      }
      return n;
    }, _extends.apply(null, arguments);
  }
  function _defineProperty(e, r, t) {
    return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: true,
      configurable: true,
      writable: true
    }) : e[r] = t, e;
  }
  function _toPropertyKey(t) {
    var i = _toPrimitive(t, "string");
    return "symbol" == _typeof(i) ? i : i + "";
  }
  function _toPrimitive(t, r) {
    if ("object" != _typeof(t) || !t) return t;
    var e = t[Symbol.toPrimitive];
    if (void 0 !== e) {
      var i = e.call(t, r);
      if ("object" != _typeof(i)) return i;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return ("string" === r ? String : Number)(t);
  }
  /*:: import type { ReactRef } from "../ReactGridLayoutPropTypes";*/
  /*:: type WPDefaultProps = {|
    measureBeforeMount: boolean
  |};*/
  /*:: type WPProps = {|
    className?: string,
    style?: Object,
    ...WPDefaultProps
  |};*/
  // eslint-disable-next-line no-unused-vars
  /*:: type WPState = {|
    width: number
  |};*/
  /*:: type ComposedProps<Config> = {|
    ...Config,
    measureBeforeMount?: boolean,
    className?: string,
    style?: Object,
    width?: number
  |};*/
  var layoutClassName = "react-grid-layout";

  /*
   * A simple HOC that provides facility for listening to container resizes.
   *
   * The Flow type is pretty janky here. I can't just spread `WPProps` into this returned object - I wish I could - but it triggers
   * a flow bug of some sort that causes it to stop typechecking.
   */
  function WidthProvideRGL /*:: <Config>*/(ComposedComponent /*: React.AbstractComponent<Config>*/) /*: React.AbstractComponent<ComposedProps<Config>>*/{
    var _WidthProvider;
    return _WidthProvider = /*#__PURE__*/function (_React$Component) {
      function WidthProvider() {
        var _this;
        _classCallCheck(this, WidthProvider);
        _this = _callSuper(this, WidthProvider, arguments);
        _defineProperty(_this, "state", {
          width: 1280
        });
        _defineProperty(_this, "elementRef", /*#__PURE__*/React$1.createRef());
        _defineProperty(_this, "mounted", false);
        _defineProperty(_this, "resizeObserver", void 0);
        return _this;
      }
      _inherits(WidthProvider, _React$Component);
      return _createClass(WidthProvider, [{
        key: "componentDidMount",
        value: function componentDidMount() {
          var _this2 = this;
          this.mounted = true;
          this.resizeObserver = new _resizeObserverPolyfill["default"](function (entries) {
            var node = _this2.elementRef.current;
            if (node instanceof HTMLElement) {
              var width = entries[0].contentRect.width;
              _this2.setState({
                width: width
              });
            }
          });
          var node = this.elementRef.current;
          if (node instanceof HTMLElement) {
            this.resizeObserver.observe(node);
          }
        }
      }, {
        key: "componentWillUnmount",
        value: function componentWillUnmount() {
          this.mounted = false;
          var node = this.elementRef.current;
          if (node instanceof HTMLElement) {
            this.resizeObserver.unobserve(node);
          }
          this.resizeObserver.disconnect();
        }
      }, {
        key: "render",
        value: function render() {
          var _this$props = this.props,
            measureBeforeMount = _this$props.measureBeforeMount,
            rest = _objectWithoutProperties(_this$props, _excluded);
          if (measureBeforeMount && !this.mounted) {
            return /*#__PURE__*/React$1.createElement("div", {
              className: (0, _clsx["default"])(this.props.className, layoutClassName),
              style: this.props.style
              // $FlowIgnore ref types
              ,

              ref: this.elementRef
            });
          }
          return /*#__PURE__*/React$1.createElement(ComposedComponent, _extends({
            innerRef: this.elementRef
          }, rest, this.state));
        }
      }]);
    }(React$1.Component
    /*:: <
        ComposedProps<Config>,
        WPState
      >*/), _defineProperty(_WidthProvider, "defaultProps", {
      measureBeforeMount: false
    }), _defineProperty(_WidthProvider, "propTypes", {
      // If true, will not render children until mounted. Useful for getting the exact width before
      // rendering, to prevent any unsightly resizing.
      measureBeforeMount: _propTypes["default"].bool
    }), _WidthProvider;
  }

  var WidthProvider = /*#__PURE__*/Object.freeze({
    __proto__: null
  });

  var require$$5 = /*@__PURE__*/getAugmentedNamespace(WidthProvider);

  (function (module) {
  	module.exports = require$$0["default"];
  	module.exports.utils = require$$1;
  	module.exports.calculateUtils = calculateUtils;
  	module.exports.Responsive = require$$3["default"];
  	module.exports.Responsive.utils = responsiveUtils;
  	module.exports.WidthProvider = require$$5["default"]; 
  } (reactGridLayout));

  var reactGridLayoutExports = reactGridLayout.exports;

  var m = require$$0$1;
  {
    m.createRoot;
    m.hydrateRoot;
  }

  function NearbyEventsMapWidget(_ref) {
    var apiRoot = _ref.apiRoot;
      _ref.nonce;
      var lat = _ref.lat,
      lng = _ref.lng;
    var _useState = React$4.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      events = _useState2[0],
      setEvents = _useState2[1];
    React$4.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/events/nearby?lat=").concat(lat, "&lng=").concat(lng)).then(function (r) {
        return r.json();
      }).then(setEvents);
    }, [lat, lng]);
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-nearby-events-widget"
    }, /*#__PURE__*/React$4.createElement("ul", null, events.map(function (ev) {
      return /*#__PURE__*/React$4.createElement("li", {
        key: ev.id
      }, /*#__PURE__*/React$4.createElement("a", {
        href: ev.link
      }, ev.title), " (", ev.distance, " km)");
    })));
  }

  function MyFavoritesWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$4.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      items = _useState2[0],
      setItems = _useState2[1];
    React$4.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/follows?post_type=artpulse_event"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(setItems);
    }, []);
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-favorites-widget"
    }, /*#__PURE__*/React$4.createElement("ul", null, items.map(function (i) {
      return /*#__PURE__*/React$4.createElement("li", {
        key: i.post_id
      }, /*#__PURE__*/React$4.createElement("a", {
        href: i.link
      }, i.title));
    })));
  }

  var __$9 = wp.i18n.__;

  /**
   * Button widget for RSVP actions.
   *
   * Props:
   * - eventId: Event post ID.
   * - apiRoot: REST API root URL.
   * - nonce: WP nonce for authentication.
   */
  function RSVPButton(_ref) {
    var eventId = _ref.eventId,
      apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$4.useState(false),
      _useState2 = _slicedToArray(_useState, 2),
      rsvped = _useState2[0],
      setRsvped = _useState2[1];
    var toggle = /*#__PURE__*/function () {
      var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var endpoint, url, resp;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              endpoint = rsvped ? 'rsvp/cancel' : 'rsvp';
              url = "".concat(apiRoot, "artpulse/v1/").concat(endpoint);
              _context.n = 1;
              return fetch(url, {
                method: 'POST',
                headers: {
                  'X-WP-Nonce': nonce,
                  'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                  event_id: eventId
                })
              });
            case 1:
              resp = _context.v;
              if (resp.ok) {
                setRsvped(!rsvped);
              }
            case 2:
              return _context.a(2);
          }
        }, _callee);
      }));
      return function toggle() {
        return _ref2.apply(this, arguments);
      };
    }();
    return /*#__PURE__*/React$4.createElement("button", {
      className: "ap-rsvp-btn".concat(rsvped ? ' is-rsvped' : ''),
      onClick: toggle
    }, rsvped ? __$9('Cancel RSVP', 'artpulse') : __$9('RSVP', 'artpulse'));
  }

  var __$8 = wp.i18n.__;

  /**
   * Simple event chat widget.
   *
   * Props:
   * - eventId: Event ID for the chat thread.
   * - apiRoot: REST API base.
   * - nonce: WP nonce.
   */
  function EventChatWidget(_ref) {
    var eventId = _ref.eventId,
      apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$4.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      messages = _useState2[0],
      setMessages = _useState2[1];
    var _useState3 = React$4.useState(''),
      _useState4 = _slicedToArray(_useState3, 2),
      text = _useState4[0],
      setText = _useState4[1];
    React$4.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/event/").concat(eventId, "/chat")).then(function (r) {
        return r.json();
      }).then(setMessages);
    }, [eventId]);
    var send = /*#__PURE__*/function () {
      var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var resp;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              _context.n = 1;
              return fetch("".concat(apiRoot, "artpulse/v1/event/").concat(eventId, "/message"), {
                method: 'POST',
                headers: {
                  'X-WP-Nonce': nonce,
                  'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                  content: text
                })
              });
            case 1:
              resp = _context.v;
              if (resp.ok) {
                setText('');
                fetch("".concat(apiRoot, "artpulse/v1/event/").concat(eventId, "/chat")).then(function (r) {
                  return r.json();
                }).then(setMessages);
              }
            case 2:
              return _context.a(2);
          }
        }, _callee);
      }));
      return function send() {
        return _ref2.apply(this, arguments);
      };
    }();
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-event-chat-widget"
    }, /*#__PURE__*/React$4.createElement("ul", {
      className: "chat-thread"
    }, messages.map(function (m, i) {
      return /*#__PURE__*/React$4.createElement("li", {
        key: i
      }, /*#__PURE__*/React$4.createElement("strong", null, m.author, ":"), " ", m.content);
    })), /*#__PURE__*/React$4.createElement("div", {
      className: "chat-form"
    }, /*#__PURE__*/React$4.createElement("input", {
      value: text,
      onChange: function onChange(e) {
        return setText(e.target.value);
      },
      placeholder: __$8('Write a message...', 'artpulse')
    }), /*#__PURE__*/React$4.createElement("button", {
      onClick: send
    }, __$8('Send', 'artpulse'))));
  }

  var __$7 = wp.i18n.__;

  /**
   * Social share widget for events.
   *
   * Props:
   * - eventUrl: URL of the event page.
   */
  function ShareThisEventWidget(_ref) {
    var eventUrl = _ref.eventUrl;
    var share = function share(prefix) {
      if (navigator.share) {
        navigator.share({
          url: eventUrl
        })["catch"](function () {});
        return;
      }
      window.open(prefix + encodeURIComponent(eventUrl), '_blank');
    };
    var copy = function copy() {
      navigator.clipboard.writeText(eventUrl).then(function () {
        alert(__$7('Link copied', 'artpulse'));
      });
    };
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-share-event-widget"
    }, /*#__PURE__*/React$4.createElement("button", {
      onClick: function onClick() {
        return share('https://twitter.com/share?url=');
      }
    }, "X"), /*#__PURE__*/React$4.createElement("button", {
      onClick: function onClick() {
        return share('https://www.facebook.com/sharer/sharer.php?u=');
      }
    }, __$7('Facebook', 'artpulse')), /*#__PURE__*/React$4.createElement("button", {
      onClick: function onClick() {
        return share('https://www.linkedin.com/sharing/share-offsite/?url=');
      }
    }, __$7('LinkedIn', 'artpulse')), /*#__PURE__*/React$4.createElement("button", {
      onClick: copy
    }, __$7('Copy Link', 'artpulse')));
  }

  var __$6 = wp.i18n.__;
  function ArtistInboxPreviewWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$4.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      threads = _useState2[0],
      setThreads = _useState2[1];
    React$4.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/conversations"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(/*#__PURE__*/function () {
        var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(list) {
          var withPreview;
          return _regenerator().w(function (_context2) {
            while (1) switch (_context2.n) {
              case 0:
                _context2.n = 1;
                return Promise.all(list.map(/*#__PURE__*/function () {
                  var _ref3 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(t) {
                    var res, msgs, last;
                    return _regenerator().w(function (_context) {
                      while (1) switch (_context.n) {
                        case 0:
                          _context.n = 1;
                          return fetch("".concat(apiRoot, "artpulse/v1/messages?with=").concat(t.user_id), {
                            headers: {
                              'X-WP-Nonce': nonce
                            },
                            credentials: 'same-origin'
                          });
                        case 1:
                          res = _context.v;
                          _context.n = 2;
                          return res.json();
                        case 2:
                          msgs = _context.v;
                          last = msgs[msgs.length - 1] || {};
                          return _context.a(2, _objectSpread2(_objectSpread2({}, t), {}, {
                            preview: last.content || '',
                            date: last.created_at
                          }));
                      }
                    }, _callee);
                  }));
                  return function (_x2) {
                    return _ref3.apply(this, arguments);
                  };
                }()));
              case 1:
                withPreview = _context2.v;
                setThreads(withPreview);
              case 2:
                return _context2.a(2);
            }
          }, _callee2);
        }));
        return function (_x) {
          return _ref2.apply(this, arguments);
        };
      }())["catch"](function () {
        return setThreads([]);
      });
    }, []);
    if (threads === null) {
      return /*#__PURE__*/React$4.createElement("p", null, __$6('Loading...', 'artpulse'));
    }
    if (!threads.length) {
      return /*#__PURE__*/React$4.createElement("p", null, __$6('No new messages.', 'artpulse'));
    }
    return /*#__PURE__*/React$4.createElement("ul", {
      className: "ap-inbox-preview-list"
    }, threads.slice(0, 3).map(function (t) {
      return /*#__PURE__*/React$4.createElement("li", {
        key: t.user_id
      }, /*#__PURE__*/React$4.createElement("strong", null, t.display_name), t.preview && /*#__PURE__*/React$4.createElement("span", null, ": ", t.preview), t.date && /*#__PURE__*/React$4.createElement("em", null, " ", new Date(t.date).toLocaleDateString()));
    }));
  }

  var __$5 = wp.i18n.__;
  function ArtistRevenueSummaryWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$4.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      data = _useState2[0],
      setData = _useState2[1];
    React$4.useEffect(function () {
      var now = new Date();
      var start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10);
      fetch("".concat(apiRoot, "artpulse/v1/user/sales?from=").concat(start), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(function (sales) {
        return setData(sales);
      })["catch"](function () {
        return setData({
          tickets_sold: 0,
          total_revenue: 0,
          trend: []
        });
      });
    }, []);
    if (!data) {
      return /*#__PURE__*/React$4.createElement("p", null, __$5('Loading...', 'artpulse'));
    }
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-revenue-summary"
    }, /*#__PURE__*/React$4.createElement("p", null, /*#__PURE__*/React$4.createElement("strong", null, data.total_revenue), " ", __$5('total revenue this month', 'artpulse')), /*#__PURE__*/React$4.createElement("p", null, data.tickets_sold, " ", __$5('tickets sold', 'artpulse')));
  }

  var __$4 = wp.i18n.__;
  function ArtistSpotlightWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$4.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      items = _useState2[0],
      setItems = _useState2[1];
    React$4.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/spotlights"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(setItems)["catch"](function () {
        return setItems([]);
      });
    }, []);
    if (items === null) {
      return /*#__PURE__*/React$4.createElement("p", null, __$4('Loading...', 'artpulse'));
    }
    if (!items.length) {
      return /*#__PURE__*/React$4.createElement("p", null, __$4('No mentions yet.', 'artpulse'));
    }
    return /*#__PURE__*/React$4.createElement("ul", {
      className: "ap-spotlight-list"
    }, items.slice(0, 3).map(function (it) {
      return /*#__PURE__*/React$4.createElement("li", {
        key: it.id
      }, /*#__PURE__*/React$4.createElement("a", {
        href: it.link
      }, it.title));
    }));
  }

  function AudienceCRMWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React$4.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      contacts = _useState2[0],
      setContacts = _useState2[1];
    React$4.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/org/").concat(orgId, "/audience"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(setContacts);
    }, [orgId]);
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-audience-crm-widget"
    }, /*#__PURE__*/React$4.createElement("ul", null, contacts.map(function (c) {
      return /*#__PURE__*/React$4.createElement("li", {
        key: c.email
      }, c.name || c.email);
    })));
  }

  var __$3 = wp.i18n.__;
  function SponsoredEventConfigWidget(_ref) {
    var postId = _ref.postId,
      apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$4.useState({
        sponsor_name: '',
        sponsor_link: '',
        sponsor_logo: ''
      }),
      _useState2 = _slicedToArray(_useState, 2),
      data = _useState2[0],
      setData = _useState2[1];
    React$4.useEffect(function () {
      fetch("".concat(apiRoot, "wp/v2/event/").concat(postId), {
        headers: {
          'X-WP-Nonce': nonce
        }
      }).then(function (r) {
        return r.json();
      }).then(function (post) {
        setData({
          sponsor_name: post.meta.sponsor_name || '',
          sponsor_link: post.meta.sponsor_link || '',
          sponsor_logo: post.meta.sponsor_logo || ''
        });
      });
    }, [postId]);
    var save = function save() {
      fetch("".concat(apiRoot, "wp/v2/event/").concat(postId), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': nonce
        },
        body: JSON.stringify({
          meta: data
        })
      });
    };
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-sponsored-config"
    }, /*#__PURE__*/React$4.createElement("p", null, /*#__PURE__*/React$4.createElement("label", null, __$3('Sponsored By', 'artpulse'), /*#__PURE__*/React$4.createElement("input", {
      type: "text",
      value: data.sponsor_name,
      onChange: function onChange(e) {
        return setData(_objectSpread2(_objectSpread2({}, data), {}, {
          sponsor_name: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$4.createElement("p", null, /*#__PURE__*/React$4.createElement("label", null, __$3('Sponsor Link', 'artpulse'), /*#__PURE__*/React$4.createElement("input", {
      type: "url",
      value: data.sponsor_link,
      onChange: function onChange(e) {
        return setData(_objectSpread2(_objectSpread2({}, data), {}, {
          sponsor_link: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$4.createElement("p", null, /*#__PURE__*/React$4.createElement("label", null, __$3('Logo URL', 'artpulse'), /*#__PURE__*/React$4.createElement("input", {
      type: "text",
      value: data.sponsor_logo,
      onChange: function onChange(e) {
        return setData(_objectSpread2(_objectSpread2({}, data), {}, {
          sponsor_logo: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$4.createElement("button", {
      type: "button",
      onClick: save
    }, __$3('Save Sponsor', 'artpulse')));
  }

  var __$2 = wp.i18n.__;
  function EmbedToolWidget(_ref) {
    var widgetId = _ref.widgetId,
      siteUrl = _ref.siteUrl;
    var _useState = React$4.useState('light'),
      _useState2 = _slicedToArray(_useState, 2),
      theme = _useState2[0],
      setTheme = _useState2[1];
    var code = "<script src=\"".concat(siteUrl, "/wp-json/widgets/embed.js?id=").concat(widgetId, "&theme=").concat(theme, "\"></script>");
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-embed-tool-widget"
    }, /*#__PURE__*/React$4.createElement("p", null, /*#__PURE__*/React$4.createElement("label", null, __$2('Theme', 'artpulse'), /*#__PURE__*/React$4.createElement("select", {
      value: theme,
      onChange: function onChange(e) {
        return setTheme(e.target.value);
      }
    }, /*#__PURE__*/React$4.createElement("option", {
      value: "light"
    }, __$2('Light', 'artpulse')), /*#__PURE__*/React$4.createElement("option", {
      value: "dark"
    }, __$2('Dark', 'artpulse'))))), /*#__PURE__*/React$4.createElement("textarea", {
      readOnly: true,
      rows: "3",
      style: {
        width: '100%'
      },
      value: code
    }));
  }

  var __$1 = wp.i18n.__;
  function OrgBrandingSettingsPanel(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React$4.useState({
        logo: '',
        color: '#000000',
        footer: ''
      }),
      _useState2 = _slicedToArray(_useState, 2),
      settings = _useState2[0],
      setSettings = _useState2[1];
    React$4.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/org/").concat(orgId, "/meta"), {
        headers: {
          'X-WP-Nonce': nonce
        }
      }).then(function (r) {
        return r.json();
      }).then(function (data) {
        return setSettings({
          logo: data.logo || '',
          color: data.color || '#000000',
          footer: data.footer || ''
        });
      });
    }, [orgId]);
    var save = function save() {
      fetch("".concat(apiRoot, "artpulse/v1/org/").concat(orgId, "/meta"), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': nonce
        },
        body: JSON.stringify(settings)
      });
    };
    return /*#__PURE__*/React$4.createElement("div", {
      className: "ap-org-branding-settings"
    }, /*#__PURE__*/React$4.createElement("p", null, /*#__PURE__*/React$4.createElement("label", null, __$1('Logo URL', 'artpulse'), /*#__PURE__*/React$4.createElement("input", {
      type: "text",
      value: settings.logo,
      onChange: function onChange(e) {
        return setSettings(_objectSpread2(_objectSpread2({}, settings), {}, {
          logo: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$4.createElement("p", null, /*#__PURE__*/React$4.createElement("label", null, __$1('Brand Color', 'artpulse'), /*#__PURE__*/React$4.createElement("input", {
      type: "color",
      value: settings.color,
      onChange: function onChange(e) {
        return setSettings(_objectSpread2(_objectSpread2({}, settings), {}, {
          color: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$4.createElement("p", null, /*#__PURE__*/React$4.createElement("label", null, __$1('Footer Text', 'artpulse'), /*#__PURE__*/React$4.createElement("input", {
      type: "text",
      value: settings.footer,
      onChange: function onChange(e) {
        return setSettings(_objectSpread2(_objectSpread2({}, settings), {}, {
          footer: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$4.createElement("button", {
      type: "button",
      onClick: save
    }, __$1('Save Branding', 'artpulse')));
  }

  var __ = wp.i18n.__;
  var registry = [{
    id: 'nearby_events_map',
    title: __('Nearby Events Map', 'artpulse'),
    component: NearbyEventsMapWidget,
    roles: ['member', 'artist']
  }, {
    id: 'my_favorites',
    title: __('My Favorites', 'artpulse'),
    component: MyFavoritesWidget,
    roles: ['member', 'artist']
  }, {
    id: 'rsvp_button',
    title: __('RSVP Button', 'artpulse'),
    component: RSVPButton,
    roles: ['member', 'artist']
  }, {
    id: 'event_chat',
    title: __('Event Chat', 'artpulse'),
    component: EventChatWidget,
    roles: ['member', 'artist']
  }, {
    id: 'share_this_event',
    title: __('Share This Event', 'artpulse'),
    component: ShareThisEventWidget,
    roles: ['member', 'artist']
  }, {
    id: 'artist_inbox_preview_widget',
    title: __('Artist Inbox Preview', 'artpulse'),
    component: ArtistInboxPreviewWidget,
    roles: ['artist']
  }, {
    id: 'artist_revenue_summary',
    title: __('Revenue Summary', 'artpulse'),
    component: ArtistRevenueSummaryWidget,
    roles: ['artist']
  }, {
    id: 'artist_spotlight',
    title: __('Artist Spotlight', 'artpulse'),
    component: ArtistSpotlightWidget,
    roles: ['artist']
  }, {
    id: 'audience_crm',
    title: __('Audience CRM', 'artpulse'),
    component: AudienceCRMWidget,
    roles: ['organization']
  }, {
    id: 'sponsored_event_config',
    title: __('Sponsored Event Config', 'artpulse'),
    component: SponsoredEventConfigWidget,
    roles: ['organization']
  }, {
    id: 'embed_tool',
    title: __('Embed Tool', 'artpulse'),
    component: EmbedToolWidget,
    roles: ['organization']
  }, {
    id: 'branding_settings_panel',
    title: __('Branding Settings', 'artpulse'),
    component: OrgBrandingSettingsPanel,
    roles: ['organization']
  }];

  var GridLayout = reactGridLayoutExports.WidthProvider(reactGridLayoutExports.Responsive);
  function DashboardContainer(_ref) {
    var _window$ArtPulseDashb, _window$ArtPulseDashb2;
    var _ref$role = _ref.role,
      role = _ref$role === void 0 ? 'member' : _ref$role;
    var apiRoot = ((_window$ArtPulseDashb = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb === void 0 ? void 0 : _window$ArtPulseDashb.root) || '/wp-json/';
    var nonce = ((_window$ArtPulseDashb2 = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb2 === void 0 ? void 0 : _window$ArtPulseDashb2.nonce) || '';
    var _useState = React$4.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      layout = _useState2[0],
      setLayout = _useState2[1];
    var widgets = registry.filter(function (w) {
      return !w.roles || w.roles.includes(role);
    });
    React$4.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/ap_dashboard_layout")).then(function (r) {
        return r.json();
      }).then(function (data) {
        var ids = Array.isArray(data.layout) ? data.layout : [];
        setLayout(ids.map(function (id, i) {
          return {
            i: id,
            x: 0,
            y: i,
            w: 4,
            h: 2
          };
        }));
      });
    }, [role]);
    var handleLayoutChange = function handleLayoutChange(l) {
      setLayout(l);
      var ids = l.map(function (it) {
        return it.i;
      });
      fetch("".concat(apiRoot, "artpulse/v1/ap_dashboard_layout"), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': nonce
        },
        body: JSON.stringify({
          layout: ids
        })
      });
    };
    var widgetMap = Object.fromEntries(widgets.map(function (w) {
      return [w.id, w.component];
    }));
    return /*#__PURE__*/React$4.createElement(GridLayout, {
      className: "layout",
      layouts: {
        lg: layout
      },
      cols: {
        lg: 12
      },
      rowHeight: 30,
      onLayoutChange: function onLayoutChange(l, allLayouts) {
        return handleLayoutChange(allLayouts.lg);
      }
    }, layout.map(function (item) {
      var Comp = widgetMap[item.i];
      return /*#__PURE__*/React$4.createElement("div", {
        key: item.i,
        "data-grid": item
      }, Comp ? /*#__PURE__*/React$4.createElement(Comp, null) : item.i);
    }));
  }

  function AppDashboard() {
    var _useState = React$4.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      role = _useState2[0],
      setRole = _useState2[1];
    React$4.useEffect(function () {
      fetch('/wp-json/artpulse/v1/me').then(function (res) {
        return res.json();
      }).then(function (data) {
        return setRole(data.role);
      });
    }, []);
    var logout = function logout() {
      return window.location.href = '/wp-login.php?action=logout';
    };
    return /*#__PURE__*/React$4.createElement("div", {
      className: "min-h-screen bg-gray-100"
    }, /*#__PURE__*/React$4.createElement(DashboardNavbar, {
      userRole: role,
      onLogout: logout
    }), /*#__PURE__*/React$4.createElement("main", {
      className: "p-4"
    }, /*#__PURE__*/React$4.createElement(DashboardContainer, {
      role: role
    }), /*#__PURE__*/React$4.createElement(MessagesPanel, null), /*#__PURE__*/React$4.createElement(CommunityAnalyticsPanel, null)));
  }
  document.addEventListener('DOMContentLoaded', function () {
    var rootEl = document.getElementById('ap-dashboard-root');
    if (rootEl && window.ReactDOM) {
      require$$0$1.render(/*#__PURE__*/React$4.createElement(AppDashboard, null), rootEl);
    }
  });

  return AppDashboard;

})(React, ReactDOM, Chart);
