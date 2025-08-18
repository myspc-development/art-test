(function (React$1, client, Chart, reactGridLayout) {
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

  var __$n = wp.i18n.__;
  function DashboardNavbar(_ref) {
    var userRole = _ref.userRole,
      onLogout = _ref.onLogout;
    return /*#__PURE__*/React.createElement("nav", {
      className: "bg-white border-b shadow-sm px-4 py-2 flex justify-between items-center"
    }, /*#__PURE__*/React.createElement("div", {
      className: "text-lg font-bold"
    }, __$n('ArtPulse Dashboard', 'artpulse')), /*#__PURE__*/React.createElement("div", {
      className: "flex gap-4 items-center"
    }, userRole === 'artist' && /*#__PURE__*/React.createElement("a", {
      href: "#/artist",
      className: "text-blue-600"
    }, __$n('Artist', 'artpulse')), userRole === 'organization' && /*#__PURE__*/React.createElement("a", {
      href: "#/org",
      className: "text-blue-600"
    }, __$n('Organization', 'artpulse')), userRole === 'member' && /*#__PURE__*/React.createElement("a", {
      href: "#/member",
      className: "text-blue-600"
    }, __$n('Member', 'artpulse')), /*#__PURE__*/React.createElement("button", {
      onClick: onLogout,
      className: "bg-red-500 text-white px-3 py-1 rounded"
    }, __$n('Logout', 'artpulse'))));
  }

  var __$m = wp.i18n.__;
  function MessagesPanel() {
    var _window$ArtPulseDashb, _window$ArtPulseDashb2;
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      messages = _useState2[0],
      setMessages = _useState2[1];
    var apiRoot = ((_window$ArtPulseDashb = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb === void 0 ? void 0 : _window$ArtPulseDashb.root) || '/wp-json/';
    var nonce = window.apNonce || ((_window$ArtPulseDashb2 = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb2 === void 0 ? void 0 : _window$ArtPulseDashb2.nonce) || '';
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/dashboard/messages"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (res) {
        if (res.status === 401 || res.status === 403) {
          setMessages([{
            id: 0,
            content: __$m('Please log in to view messages.', 'artpulse')
          }]);
          return Promise.reject('unauthorized');
        }
        return res.json();
      }).then(setMessages)["catch"](function () {});
    }, []);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-widget bg-white p-4 rounded shadow mb-4"
    }, messages.map(function (msg) {
      return /*#__PURE__*/React$1.createElement("p", {
        key: msg.id
      }, msg.content);
    }));
  }

  function AnalyticsCard(_ref) {
    var label = _ref.label,
      value = _ref.value;
    return /*#__PURE__*/React$1.createElement("div", {
      className: "border rounded p-4 text-center"
    }, /*#__PURE__*/React$1.createElement("div", {
      className: "text-2xl font-bold"
    }, value !== null && value !== void 0 ? value : 0), /*#__PURE__*/React$1.createElement("div", {
      className: "text-sm text-gray-500"
    }, label));
  }

  var __$l = wp.i18n.__;
  function TopUsersTable(_ref) {
    var _ref$users = _ref.users,
      users = _ref$users === void 0 ? [] : _ref$users;
    return /*#__PURE__*/React$1.createElement("table", {
      className: "min-w-full text-sm"
    }, /*#__PURE__*/React$1.createElement("thead", null, /*#__PURE__*/React$1.createElement("tr", null, /*#__PURE__*/React$1.createElement("th", {
      className: "text-left"
    }, __$l('User', 'artpulse')), /*#__PURE__*/React$1.createElement("th", {
      className: "text-right"
    }, __$l('Count', 'artpulse')))), /*#__PURE__*/React$1.createElement("tbody", null, users.map(function (u) {
      return /*#__PURE__*/React$1.createElement("tr", {
        key: u.user_id,
        className: "border-b"
      }, /*#__PURE__*/React$1.createElement("td", null, u.user_id), /*#__PURE__*/React$1.createElement("td", {
        className: "text-right"
      }, u.c));
    })));
  }

  var __$k = wp.i18n.__;
  function ActivityGraph(_ref) {
    var _ref$data = _ref.data,
      data = _ref$data === void 0 ? [] : _ref$data;
    var canvasRef = React$1.useRef(null);
    React$1.useEffect(function () {
      if (!canvasRef.current || !data.length) return;
      var chart = new Chart(canvasRef.current.getContext('2d'), {
        type: 'line',
        data: {
          labels: data.map(function (d) {
            return d.day;
          }),
          datasets: [{
            label: __$k('Count', 'artpulse'),
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
    return /*#__PURE__*/React$1.createElement("canvas", {
      ref: canvasRef
    });
  }

  var __$j = wp.i18n.__;
  function FlaggedActivityLog(_ref) {
    var _ref$items = _ref.items,
      items = _ref$items === void 0 ? [] : _ref$items;
    return /*#__PURE__*/React$1.createElement("ul", {
      className: "space-y-1 text-sm"
    }, items.map(function (i) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: i.post_id || i.thread_id,
        className: "border-b pb-1"
      }, i.post_id || i.thread_id, " - ", i.c, " ", __$j('flags', 'artpulse'));
    }));
  }

  var __$i = wp.i18n.__;
  function CommunityAnalyticsPanel() {
    var _window$ArtPulseDashb, _window$ArtPulseDashb2;
    var _useState = React$1.useState('messaging'),
      _useState2 = _slicedToArray(_useState, 2),
      tab = _useState2[0],
      setTab = _useState2[1];
    var _useState3 = React$1.useState({}),
      _useState4 = _slicedToArray(_useState3, 2),
      data = _useState4[0],
      setData = _useState4[1];
    var apiRoot = ((_window$ArtPulseDashb = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb === void 0 ? void 0 : _window$ArtPulseDashb.root) || '/wp-json/';
    var nonce = window.apNonce || ((_window$ArtPulseDashb2 = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb2 === void 0 ? void 0 : _window$ArtPulseDashb2.nonce) || '';
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/analytics/community/").concat(tab), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (res) {
        return res.ok ? res.json() : {};
      }).then(setData);
    }, [tab]);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-widget bg-white p-4 rounded shadow mb-4"
    }, /*#__PURE__*/React$1.createElement("div", {
      className: "flex gap-4 mb-4"
    }, ['messaging', 'comments', 'forums'].map(function (t) {
      return /*#__PURE__*/React$1.createElement("button", {
        key: t,
        onClick: function onClick() {
          return setTab(t);
        },
        className: tab === t ? 'font-semibold' : ''
      }, __$i('' + t.charAt(0).toUpperCase() + t.slice(1), 'artpulse'));
    })), /*#__PURE__*/React$1.createElement("div", {
      className: "grid gap-4 md:grid-cols-2 mb-4"
    }, /*#__PURE__*/React$1.createElement(AnalyticsCard, {
      label: __$i('Total', 'artpulse'),
      value: data.total
    }), data.flagged_count !== undefined && /*#__PURE__*/React$1.createElement(AnalyticsCard, {
      label: __$i('Flagged', 'artpulse'),
      value: data.flagged_count
    })), data.per_day && /*#__PURE__*/React$1.createElement(ActivityGraph, {
      data: data.per_day
    }), tab === 'messaging' && data.top_users && /*#__PURE__*/React$1.createElement(TopUsersTable, {
      users: data.top_users
    }), tab !== 'messaging' && data.top_posts && /*#__PURE__*/React$1.createElement(FlaggedActivityLog, {
      items: data.top_posts
    }), tab === 'forums' && data.top_threads && /*#__PURE__*/React$1.createElement(FlaggedActivityLog, {
      items: data.top_threads
    }));
  }

  function NearbyEventsMapWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      lat = _ref.lat,
      lng = _ref.lng;
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      events = _useState2[0],
      setEvents = _useState2[1];
    React$1.useEffect(function () {
      if (!lat || !lng) {
        setEvents([]);
        return;
      }
      fetch("".concat(apiRoot, "artpulse/v1/events/nearby?lat=").concat(lat, "&lng=").concat(lng), {
        headers: {
          'X-WP-Nonce': nonce
        }
      }).then(function (r) {
        return r.ok ? r.json() : Promise.resolve([]);
      }).then(function (data) {
        return Array.isArray(data) ? data : [];
      }).then(setEvents)["catch"](function () {
        return setEvents([]);
      });
    }, [lat, lng, apiRoot, nonce]);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-nearby-events-widget",
      "data-widget-id": "nearby_events_map"
    }, /*#__PURE__*/React$1.createElement("ul", null, events.map(function (ev) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: ev.id
      }, /*#__PURE__*/React$1.createElement("a", {
        href: ev.link
      }, ev.title), " (", ev.distance, " km)");
    })));
  }

  function MyFavoritesWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      items = _useState2[0],
      setItems = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/follows?post_type=artpulse_event"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(setItems);
    }, []);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-favorites-widget",
      "data-widget-id": "my_favorites"
    }, /*#__PURE__*/React$1.createElement("ul", null, items.map(function (i) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: i.post_id
      }, /*#__PURE__*/React$1.createElement("a", {
        href: i.link
      }, i.title));
    })));
  }

  var __$h = wp.i18n.__;

  /**
   * Button widget for RSVP actions.
   *
   * Props:
   * - eventId: Event post ID.
   * - apiRoot: REST API root URL.
   * - nonce: WP nonce for authentication.
   */
  function RsvpButtonWidget(_ref) {
    var eventId = _ref.eventId,
      apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState(false),
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
    return /*#__PURE__*/React$1.createElement("button", {
      className: "ap-rsvp-btn".concat(rsvped ? ' is-rsvped' : ''),
      onClick: toggle
    }, rsvped ? __$h('Cancel RSVP', 'artpulse') : __$h('RSVP', 'artpulse'));
  }

  var __$g = wp.i18n.__;

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
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      messages = _useState2[0],
      setMessages = _useState2[1];
    var _useState3 = React$1.useState(''),
      _useState4 = _slicedToArray(_useState3, 2),
      text = _useState4[0],
      setText = _useState4[1];
    React$1.useEffect(function () {
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
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-event-chat-widget"
    }, /*#__PURE__*/React$1.createElement("ul", {
      className: "chat-thread"
    }, messages.map(function (m, i) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: i
      }, /*#__PURE__*/React$1.createElement("strong", null, m.author, ":"), " ", m.content);
    })), /*#__PURE__*/React$1.createElement("div", {
      className: "chat-form"
    }, /*#__PURE__*/React$1.createElement("input", {
      value: text,
      onChange: function onChange(e) {
        return setText(e.target.value);
      },
      placeholder: __$g('Write a message...', 'artpulse')
    }), /*#__PURE__*/React$1.createElement("button", {
      onClick: send
    }, __$g('Send', 'artpulse'))));
  }

  var __$f = wp.i18n.__;

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
        alert(__$f('Link copied', 'artpulse'));
      });
    };
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-share-event-widget"
    }, /*#__PURE__*/React$1.createElement("button", {
      onClick: function onClick() {
        return share('https://twitter.com/share?url=');
      }
    }, "X"), /*#__PURE__*/React$1.createElement("button", {
      onClick: function onClick() {
        return share('https://www.facebook.com/sharer/sharer.php?u=');
      }
    }, __$f('Facebook', 'artpulse')), /*#__PURE__*/React$1.createElement("button", {
      onClick: function onClick() {
        return share('https://www.linkedin.com/sharing/share-offsite/?url=');
      }
    }, __$f('LinkedIn', 'artpulse')), /*#__PURE__*/React$1.createElement("button", {
      onClick: copy
    }, __$f('Copy Link', 'artpulse')));
  }

  var __$e = wp.i18n.__;
  function ArtistInboxPreviewWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      threads = _useState2[0],
      setThreads = _useState2[1];
    React$1.useEffect(function () {
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
    var content;
    if (threads === null) {
      content = /*#__PURE__*/React$1.createElement("p", null, __$e('Loading...', 'artpulse'));
    } else if (!threads.length) {
      content = /*#__PURE__*/React$1.createElement("p", null, __$e('No new messages.', 'artpulse'));
    } else {
      content = /*#__PURE__*/React$1.createElement("ul", {
        className: "ap-inbox-preview-list"
      }, threads.slice(0, 3).map(function (t) {
        return /*#__PURE__*/React$1.createElement("li", {
          key: t.user_id
        }, /*#__PURE__*/React$1.createElement("strong", null, t.display_name), t.preview && /*#__PURE__*/React$1.createElement("span", null, ": ", t.preview), t.date && /*#__PURE__*/React$1.createElement("em", null, " ", new Date(t.date).toLocaleDateString()));
      }));
    }
    return /*#__PURE__*/React$1.createElement("div", {
      "data-widget-id": "artist_inbox_preview"
    }, content);
  }

  function ActivityFeedWidget() {
    return /*#__PURE__*/React$1.createElement("div", null, "ActivityFeedWidget Component Placeholder");
  }

  function MyUpcomingEventsWidget() {
    return /*#__PURE__*/React$1.createElement("div", null, "MyUpcomingEventsWidget Component Placeholder");
  }

  function ArtPulseNewsFeedWidget() {
    return /*#__PURE__*/React$1.createElement("div", null, "ArtPulseNewsFeedWidget Component Placeholder");
  }

  function QAChecklistWidget() {
    return /*#__PURE__*/React$1.createElement("div", null, "QAChecklistWidget Component Placeholder");
  }

  function EventsWidget() {
    return /*#__PURE__*/React$1.createElement("div", null, "EventsWidget Component Placeholder");
  }

  function WelcomeBoxWidget() {
    return /*#__PURE__*/React$1.createElement("div", null, "WelcomeBoxWidget Component Placeholder");
  }

  function WidgetEventsWidget() {
    return /*#__PURE__*/React$1.createElement("div", null, "WidgetEventsWidget Component Placeholder");
  }

  function FavoritesOverviewWidget() {
    return /*#__PURE__*/React$1.createElement("div", null, "FavoritesOverviewWidget Component Placeholder");
  }

  var __$d = wp.i18n.__;
  function ArtistRevenueSummaryWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      data = _useState2[0],
      setData = _useState2[1];
    React$1.useEffect(function () {
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
      return /*#__PURE__*/React$1.createElement("div", {
        "data-widget-id": "revenue_summary"
      }, /*#__PURE__*/React$1.createElement("p", null, __$d('Loading...', 'artpulse')));
    }
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-revenue-summary",
      "data-widget-id": "revenue_summary"
    }, /*#__PURE__*/React$1.createElement("p", null, /*#__PURE__*/React$1.createElement("strong", null, data.total_revenue), " ", __$d('total revenue this month', 'artpulse')), /*#__PURE__*/React$1.createElement("p", null, data.tickets_sold, " ", __$d('tickets sold', 'artpulse')));
  }

  var __$c = wp.i18n.__;
  function ArtistSpotlightWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      items = _useState2[0],
      setItems = _useState2[1];
    React$1.useEffect(function () {
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
      return /*#__PURE__*/React$1.createElement("p", null, __$c('Loading...', 'artpulse'));
    }
    if (!items.length) {
      return /*#__PURE__*/React$1.createElement("p", null, __$c('No mentions yet.', 'artpulse'));
    }
    return /*#__PURE__*/React$1.createElement("ul", {
      className: "ap-spotlight-list"
    }, items.slice(0, 3).map(function (it) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: it.id
      }, /*#__PURE__*/React$1.createElement("a", {
        href: it.link
      }, it.title));
    }));
  }

  var __$b = wp.i18n.__;
  function ArtistArtworkManagerWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      items = _useState2[0],
      setItems = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "wp/v2/artpulse_artwork?per_page=5&_embed"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(function (data) {
        return setItems(Array.isArray(data) ? data : []);
      })["catch"](function () {
        return setItems([]);
      });
    }, []);
    var content;
    if (items === null) {
      content = /*#__PURE__*/React$1.createElement("p", null, __$b('Loading...', 'artpulse'));
    } else if (!items.length) {
      content = /*#__PURE__*/React$1.createElement("p", null, __$b('No artworks found.', 'artpulse'));
    } else {
      content = /*#__PURE__*/React$1.createElement("ul", {
        className: "ap-artwork-manager"
      }, items.map(function (a) {
        var _a$title;
        return /*#__PURE__*/React$1.createElement("li", {
          key: a.id
        }, /*#__PURE__*/React$1.createElement("a", {
          href: a.link
        }, ((_a$title = a.title) === null || _a$title === void 0 ? void 0 : _a$title.rendered) || a.slug));
      }));
    }
    return /*#__PURE__*/React$1.createElement("div", {
      "data-widget-id": "artist_artwork_manager"
    }, content);
  }

  var __$a = wp.i18n.__;
  function ArtistAudienceInsightsWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      stats = _useState2[0],
      setStats = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/artist"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(setStats)["catch"](function () {
        return setStats({
          followers: 0,
          sales: 0,
          artworks: 0
        });
      });
    }, []);
    var content;
    if (!stats) {
      content = /*#__PURE__*/React$1.createElement("p", null, __$a('Loading...', 'artpulse'));
    } else {
      content = /*#__PURE__*/React$1.createElement("ul", {
        className: "ap-audience-insights"
      }, /*#__PURE__*/React$1.createElement("li", null, /*#__PURE__*/React$1.createElement("strong", null, stats.followers), " ", __$a('followers', 'artpulse')), /*#__PURE__*/React$1.createElement("li", null, /*#__PURE__*/React$1.createElement("strong", null, stats.sales), " ", __$a('total sales', 'artpulse')), /*#__PURE__*/React$1.createElement("li", null, /*#__PURE__*/React$1.createElement("strong", null, stats.artworks), " ", __$a('artworks uploaded', 'artpulse')));
    }
    return /*#__PURE__*/React$1.createElement("div", {
      "data-widget-id": "artist_audience_insights"
    }, content);
  }

  var __$9 = wp.i18n.__;
  function ArtistEarningsWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      data = _useState2[0],
      setData = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/user/payouts"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(setData)["catch"](function () {
        return setData({
          payouts: [],
          balance: 0
        });
      });
    }, []);
    if (!data) {
      return /*#__PURE__*/React$1.createElement("div", {
        "data-widget-id": "artist_earnings_summary"
      }, /*#__PURE__*/React$1.createElement("p", null, __$9('Loading...', 'artpulse')));
    }
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-earnings-summary",
      "data-widget-id": "artist_earnings_summary"
    }, /*#__PURE__*/React$1.createElement("p", null, /*#__PURE__*/React$1.createElement("strong", null, data.balance), " ", __$9('current balance', 'artpulse')), data.payouts.length ? /*#__PURE__*/React$1.createElement("ul", null, data.payouts.slice(0, 5).map(function (p) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: p.id
      }, new Date(p.payout_date).toLocaleDateString(), " \u2013 ", p.amount, " (", p.status, ")");
    })) : /*#__PURE__*/React$1.createElement("p", null, __$9('No payouts yet.', 'artpulse')));
  }

  var __$8 = wp.i18n.__;
  function ArtistFeedPublisherWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState(''),
      _useState2 = _slicedToArray(_useState, 2),
      text = _useState2[0],
      setText = _useState2[1];
    var _useState3 = React$1.useState(''),
      _useState4 = _slicedToArray(_useState3, 2),
      msg = _useState4[0],
      setMsg = _useState4[1];
    var submit = /*#__PURE__*/function () {
      var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(e) {
        var resp;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              e.preventDefault();
              setMsg('');
              _context.n = 1;
              return fetch("".concat(apiRoot, "wp/v2/posts"), {
                method: 'POST',
                headers: {
                  'X-WP-Nonce': nonce,
                  'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                  title: text,
                  status: 'publish'
                })
              });
            case 1:
              resp = _context.v;
              if (resp.ok) {
                setText('');
                setMsg(__$8('Posted!', 'artpulse'));
              } else {
                setMsg(__$8('Error posting.', 'artpulse'));
              }
            case 2:
              return _context.a(2);
          }
        }, _callee);
      }));
      return function submit(_x) {
        return _ref2.apply(this, arguments);
      };
    }();
    return /*#__PURE__*/React$1.createElement("div", {
      "data-widget-id": "artist_feed_publisher"
    }, /*#__PURE__*/React$1.createElement("form", {
      className: "ap-feed-publisher",
      onSubmit: submit
    }, /*#__PURE__*/React$1.createElement("textarea", {
      value: text,
      onChange: function onChange(e) {
        return setText(e.target.value);
      },
      placeholder: __$8('Share an update...', 'artpulse')
    }), /*#__PURE__*/React$1.createElement("button", {
      type: "submit"
    }, __$8('Publish', 'artpulse')), msg && /*#__PURE__*/React$1.createElement("p", {
      className: "ap-post-status"
    }, msg)));
  }

  var __$7 = wp.i18n.__;
  function ArtistCollaborationWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      invites = _useState2[0],
      setInvites = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/users/me/orgs"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(/*#__PURE__*/function () {
        var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(list) {
          var pending, detailed;
          return _regenerator().w(function (_context2) {
            while (1) switch (_context2.n) {
              case 0:
                pending = list.filter(function (i) {
                  return i.status && i.status !== 'active';
                });
                _context2.n = 1;
                return Promise.all(pending.map(/*#__PURE__*/function () {
                  var _ref3 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(inv) {
                    var _org$title, res, org;
                    return _regenerator().w(function (_context) {
                      while (1) switch (_context.p = _context.n) {
                        case 0:
                          _context.p = 0;
                          _context.n = 1;
                          return fetch("".concat(apiRoot, "wp/v2/artpulse_org/").concat(inv.org_id));
                        case 1:
                          res = _context.v;
                          _context.n = 2;
                          return res.json();
                        case 2:
                          org = _context.v;
                          return _context.a(2, _objectSpread2(_objectSpread2({}, inv), {}, {
                            name: ((_org$title = org.title) === null || _org$title === void 0 ? void 0 : _org$title.rendered) || inv.org_id
                          }));
                        case 3:
                          _context.p = 3;
                          _context.v;
                          return _context.a(2, _objectSpread2(_objectSpread2({}, inv), {}, {
                            name: inv.org_id
                          }));
                      }
                    }, _callee, null, [[0, 3]]);
                  }));
                  return function (_x2) {
                    return _ref3.apply(this, arguments);
                  };
                }()));
              case 1:
                detailed = _context2.v;
                setInvites(detailed);
              case 2:
                return _context2.a(2);
            }
          }, _callee2);
        }));
        return function (_x) {
          return _ref2.apply(this, arguments);
        };
      }())["catch"](function () {
        return setInvites([]);
      });
    }, []);
    var content;
    if (invites === null) {
      content = /*#__PURE__*/React$1.createElement("p", null, __$7('Loading...', 'artpulse'));
    } else if (!invites.length) {
      content = /*#__PURE__*/React$1.createElement("p", null, __$7('No pending invites.', 'artpulse'));
    } else {
      content = /*#__PURE__*/React$1.createElement("ul", {
        className: "ap-collab-requests"
      }, invites.map(function (inv) {
        return /*#__PURE__*/React$1.createElement("li", {
          key: inv.org_id
        }, inv.name, " \u2013 ", inv.role, " (", inv.status, ")");
      }));
    }
    return /*#__PURE__*/React$1.createElement("div", {
      "data-widget-id": "collab_requests"
    }, content);
  }

  var __$6 = wp.i18n.__;
  function OnboardingTrackerWidget() {
    var items = [__$6('Complete Profile', 'artpulse'), __$6('Upload First Artwork', 'artpulse'), __$6('Host an Event', 'artpulse'), __$6('Get 10 Followers', 'artpulse')];
    return /*#__PURE__*/React$1.createElement("ul", {
      className: "ap-onboarding-tracker"
    }, items.map(function (i, idx) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: idx
      }, i);
    }));
  }

  function AudienceCRMWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      contacts = _useState2[0],
      setContacts = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/org/").concat(orgId, "/audience"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(setContacts);
    }, [orgId]);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-audience-crm-widget"
    }, /*#__PURE__*/React$1.createElement("ul", null, contacts.map(function (c) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: c.email
      }, c.name || c.email);
    })));
  }

  var __$5 = wp.i18n.__;
  function SponsoredEventConfigWidget(_ref) {
    var postId = _ref.postId,
      apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React$1.useState({
        sponsor_name: '',
        sponsor_link: '',
        sponsor_logo: ''
      }),
      _useState2 = _slicedToArray(_useState, 2),
      data = _useState2[0],
      setData = _useState2[1];
    React$1.useEffect(function () {
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
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-sponsored-config",
      "data-widget-id": "sponsored_event_config"
    }, /*#__PURE__*/React$1.createElement("p", null, /*#__PURE__*/React$1.createElement("label", null, __$5('Sponsored By', 'artpulse'), /*#__PURE__*/React$1.createElement("input", {
      type: "text",
      value: data.sponsor_name,
      onChange: function onChange(e) {
        return setData(_objectSpread2(_objectSpread2({}, data), {}, {
          sponsor_name: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$1.createElement("p", null, /*#__PURE__*/React$1.createElement("label", null, __$5('Sponsor Link', 'artpulse'), /*#__PURE__*/React$1.createElement("input", {
      type: "url",
      value: data.sponsor_link,
      onChange: function onChange(e) {
        return setData(_objectSpread2(_objectSpread2({}, data), {}, {
          sponsor_link: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$1.createElement("p", null, /*#__PURE__*/React$1.createElement("label", null, __$5('Logo URL', 'artpulse'), /*#__PURE__*/React$1.createElement("input", {
      type: "text",
      value: data.sponsor_logo,
      onChange: function onChange(e) {
        return setData(_objectSpread2(_objectSpread2({}, data), {}, {
          sponsor_logo: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$1.createElement("button", {
      type: "button",
      onClick: save
    }, __$5('Save Sponsor', 'artpulse')));
  }

  var __$4 = wp.i18n.__;
  function EmbedToolWidget(_ref) {
    var widgetId = _ref.widgetId,
      siteUrl = _ref.siteUrl;
    var _useState = React$1.useState('light'),
      _useState2 = _slicedToArray(_useState, 2),
      theme = _useState2[0],
      setTheme = _useState2[1];
    var code = "<script src=\"".concat(siteUrl, "/wp-json/widgets/embed.js?id=").concat(widgetId, "&theme=").concat(theme, "\"></script>");
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-embed-tool-widget",
      "data-widget-id": "embed_tool"
    }, /*#__PURE__*/React$1.createElement("p", null, /*#__PURE__*/React$1.createElement("label", null, __$4('Theme', 'artpulse'), /*#__PURE__*/React$1.createElement("select", {
      value: theme,
      onChange: function onChange(e) {
        return setTheme(e.target.value);
      }
    }, /*#__PURE__*/React$1.createElement("option", {
      value: "light"
    }, __$4('Light', 'artpulse')), /*#__PURE__*/React$1.createElement("option", {
      value: "dark"
    }, __$4('Dark', 'artpulse'))))), /*#__PURE__*/React$1.createElement("textarea", {
      readOnly: true,
      rows: "3",
      style: {
        width: '100%'
      },
      value: code
    }));
  }

  var __$3 = wp.i18n.__;
  function OrgBrandingSettingsPanel(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React$1.useState({
        logo: '',
        color: '#000000',
        footer: ''
      }),
      _useState2 = _slicedToArray(_useState, 2),
      settings = _useState2[0],
      setSettings = _useState2[1];
    React$1.useEffect(function () {
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
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-org-branding-settings",
      "data-widget-id": "branding_settings_panel"
    }, /*#__PURE__*/React$1.createElement("p", null, /*#__PURE__*/React$1.createElement("label", null, __$3('Logo URL', 'artpulse'), /*#__PURE__*/React$1.createElement("input", {
      type: "text",
      value: settings.logo,
      onChange: function onChange(e) {
        return setSettings(_objectSpread2(_objectSpread2({}, settings), {}, {
          logo: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$1.createElement("p", null, /*#__PURE__*/React$1.createElement("label", null, __$3('Brand Color', 'artpulse'), /*#__PURE__*/React$1.createElement("input", {
      type: "color",
      value: settings.color,
      onChange: function onChange(e) {
        return setSettings(_objectSpread2(_objectSpread2({}, settings), {}, {
          color: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$1.createElement("p", null, /*#__PURE__*/React$1.createElement("label", null, __$3('Footer Text', 'artpulse'), /*#__PURE__*/React$1.createElement("input", {
      type: "text",
      value: settings.footer,
      onChange: function onChange(e) {
        return setSettings(_objectSpread2(_objectSpread2({}, settings), {}, {
          footer: e.target.value
        }));
      }
    }))), /*#__PURE__*/React$1.createElement("button", {
      type: "button",
      onClick: save
    }, __$3('Save Branding', 'artpulse')));
  }

  wp.i18n.__;
  function OrgEventOverviewWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      events = _useState2[0],
      setEvents = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/org/").concat(orgId, "/events/summary"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(function (data) {
        return setEvents(data.events || []);
      });
    }, [orgId]);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-org-event-overview-widget",
      "data-widget-id": "org_event_overview"
    }, /*#__PURE__*/React$1.createElement("ul", null, events.map(function (ev) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: ev.id
      }, ev.title, " (", ev.status, ")");
    })));
  }

  wp.i18n.__;
  function OrgTeamRosterWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      team = _useState2[0],
      setTeam = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/orgs/").concat(orgId, "/roles"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(function (data) {
        return setTeam(Array.isArray(data) ? data : []);
      });
    }, [orgId]);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-org-team-roster-widget",
      "data-widget-id": "org_team_roster"
    }, /*#__PURE__*/React$1.createElement("ul", null, team.map(function (user) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: user.user_id
      }, user.user_id, " - ", user.role);
    })));
  }

  wp.i18n.__;
  function OrgApprovalCenterWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      items = _useState2[0],
      setItems = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/org/").concat(orgId, "/submissions"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(function (data) {
        return setItems(Array.isArray(data) ? data : []);
      });
    }, [orgId]);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-org-approval-center-widget",
      "data-widget-id": "org_approval_center"
    }, /*#__PURE__*/React$1.createElement("ul", null, items.map(function (item) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: item.id
      }, item.title, " - ", item.status);
    })));
  }

  var __$2 = wp.i18n.__;
  function OrgTicketInsightsWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React$1.useState({
        sales: 0,
        revenue: 0
      }),
      _useState2 = _slicedToArray(_useState, 2),
      metrics = _useState2[0],
      setMetrics = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/org/").concat(orgId, "/tickets/metrics"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(function (data) {
        return setMetrics(data);
      });
    }, [orgId]);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-org-ticket-insights-widget",
      "data-widget-id": "org_ticket_insights"
    }, /*#__PURE__*/React$1.createElement("p", null, __$2('Tickets Sold', 'artpulse'), ": ", metrics.sales), /*#__PURE__*/React$1.createElement("p", null, __$2('Revenue', 'artpulse'), ": ", metrics.revenue));
  }

  var __$1 = wp.i18n.__;
  function OrgBroadcastBoxWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React$1.useState(''),
      _useState2 = _slicedToArray(_useState, 2),
      text = _useState2[0],
      setText = _useState2[1];
    var send = /*#__PURE__*/function () {
      var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              _context.n = 1;
              return fetch("".concat(apiRoot, "artpulse/v1/org/").concat(orgId, "/message/broadcast"), {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-WP-Nonce': nonce
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                  message: text
                })
              });
            case 1:
              setText('');
            case 2:
              return _context.a(2);
          }
        }, _callee);
      }));
      return function send() {
        return _ref2.apply(this, arguments);
      };
    }();
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-org-broadcast-box-widget",
      "data-widget-id": "org_broadcast_box"
    }, /*#__PURE__*/React$1.createElement("textarea", {
      value: text,
      onChange: function onChange(e) {
        return setText(e.target.value);
      }
    }), /*#__PURE__*/React$1.createElement("button", {
      onClick: send
    }, __$1('Send Broadcast', 'artpulse')));
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
    id: 'activity_feed',
    title: __('Activity Feed', 'artpulse'),
    component: ActivityFeedWidget,
    roles: ['member', 'artist', 'organization']
  }, {
    id: 'my_upcoming_events',
    title: __('My Upcoming Events', 'artpulse'),
    component: MyUpcomingEventsWidget,
    roles: ['member', 'artist']
  }, {
    id: 'news_feed',
    title: __('News Feed', 'artpulse'),
    component: ArtPulseNewsFeedWidget,
    roles: ['member']
  }, {
    id: 'rsvp_button',
    title: __('RSVP Button', 'artpulse'),
    component: RsvpButtonWidget,
    roles: ['member']
  }, {
    id: 'event_chat',
    title: __('Event Chat', 'artpulse'),
    component: EventChatWidget,
    roles: ['member']
  }, {
    id: 'share_this_event',
    title: __('Share This Event', 'artpulse'),
    component: ShareThisEventWidget,
    roles: ['member']
  }, {
    id: 'qa_checklist',
    title: __('QA Checklist', 'artpulse'),
    component: QAChecklistWidget,
    roles: ['member']
  }, {
    id: 'sample_events',
    title: __('Sample Events', 'artpulse'),
    component: EventsWidget,
    roles: ['member', 'artist', 'organization']
  }, {
    id: 'welcome_box',
    title: __('Welcome Box', 'artpulse'),
    component: WelcomeBoxWidget,
    roles: ['member']
  }, {
    id: 'widget_events',
    title: __('Upcoming Events', 'artpulse'),
    component: WidgetEventsWidget,
    roles: ['member', 'organization']
  }, {
    id: 'widget_favorites',
    title: __('Favorites Overview', 'artpulse'),
    component: FavoritesOverviewWidget,
    roles: ['member']
  }, {
    id: 'artist_inbox_preview',
    title: __('Artist Inbox Preview', 'artpulse'),
    component: ArtistInboxPreviewWidget,
    roles: ['member', 'artist']
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
    id: 'artist_artwork_manager',
    title: __('Artwork Manager', 'artpulse'),
    component: ArtistArtworkManagerWidget,
    roles: ['artist'],
    "default": true
  }, {
    id: 'artist_audience_insights',
    title: __('Audience Insights', 'artpulse'),
    component: ArtistAudienceInsightsWidget,
    roles: ['artist']
  }, {
    id: 'artist_earnings_summary',
    title: __('Earnings Summary', 'artpulse'),
    component: ArtistEarningsWidget,
    roles: ['artist']
  }, {
    id: 'artist_feed_publisher',
    title: __('Post & Engage', 'artpulse'),
    component: ArtistFeedPublisherWidget,
    roles: ['artist']
  }, {
    id: 'collab_requests',
    title: __('Collab Requests', 'artpulse'),
    component: ArtistCollaborationWidget,
    roles: ['artist']
  }, {
    id: 'onboarding_tracker',
    title: __('Onboarding Checklist', 'artpulse'),
    component: OnboardingTrackerWidget,
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
  }, {
    id: 'org_event_overview',
    title: __('Event Overview', 'artpulse'),
    component: OrgEventOverviewWidget,
    roles: ['organization']
  }, {
    id: 'org_team_roster',
    title: __('Team Management', 'artpulse'),
    component: OrgTeamRosterWidget,
    roles: ['organization']
  }, {
    id: 'org_approval_center',
    title: __('Approval Center', 'artpulse'),
    component: OrgApprovalCenterWidget,
    roles: ['organization']
  }, {
    id: 'org_ticket_insights',
    title: __('Ticket Analytics', 'artpulse'),
    component: OrgTicketInsightsWidget,
    roles: ['organization']
  }, {
    id: 'org_broadcast_box',
    title: __('Announcement Tool', 'artpulse'),
    component: OrgBroadcastBoxWidget,
    roles: ['organization']
  }];

  var GridLayout = reactGridLayout.WidthProvider(reactGridLayout.Responsive);
  function DashboardContainer(_ref) {
    var _window$ArtPulseDashb, _window$ArtPulseDashb2;
    var _ref$role = _ref.role,
      role = _ref$role === void 0 ? 'member' : _ref$role;
    var apiRoot = ((_window$ArtPulseDashb = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb === void 0 ? void 0 : _window$ArtPulseDashb.root) || '/wp-json/';
    var nonce = window.apNonce || ((_window$ArtPulseDashb2 = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb2 === void 0 ? void 0 : _window$ArtPulseDashb2.nonce) || '';
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      layout = _useState2[0],
      setLayout = _useState2[1];
    var widgets = registry.filter(function (w) {
      return !w.roles || w.roles.includes(role);
    });
    var widgetTitles = Object.fromEntries(widgets.map(function (w) {
      return [w.id, w.title];
    }));
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/ap_dashboard_layout"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
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
        credentials: 'same-origin',
        body: JSON.stringify({
          layout: ids
        })
      });
    };
    var widgetMap = Object.fromEntries(widgets.map(function (w) {
      return [w.id, w.component];
    }));
    var handleKeyDown = function handleKeyDown(e, item) {
      var key = e.key;
      var changes = null;
      if (e.shiftKey) {
        switch (key) {
          case 'ArrowLeft':
            changes = {
              w: Math.max(1, item.w - 1)
            };
            break;
          case 'ArrowRight':
            changes = {
              w: item.w + 1
            };
            break;
          case 'ArrowUp':
            changes = {
              h: Math.max(1, item.h - 1)
            };
            break;
          case 'ArrowDown':
            changes = {
              h: item.h + 1
            };
            break;
        }
      } else {
        switch (key) {
          case 'ArrowLeft':
            changes = {
              x: Math.max(0, item.x - 1)
            };
            break;
          case 'ArrowRight':
            changes = {
              x: item.x + 1
            };
            break;
          case 'ArrowUp':
            changes = {
              y: Math.max(0, item.y - 1)
            };
            break;
          case 'ArrowDown':
            changes = {
              y: item.y + 1
            };
            break;
        }
      }
      if (changes) {
        e.preventDefault();
        var updated = layout.map(function (it) {
          return it.i === item.i ? _objectSpread2(_objectSpread2({}, it), changes) : it;
        });
        handleLayoutChange(updated);
      }
    };
    var breakpoints = {
      lg: 1200,
      md: 996,
      sm: 768,
      xs: 480,
      xxs: 0
    };
    var cols = {
      lg: 12,
      md: 10,
      sm: 6,
      xs: 4,
      xxs: 2
    };
    return /*#__PURE__*/React$1.createElement(GridLayout, {
      className: "layout",
      role: "grid",
      "aria-label": "Dashboard widgets",
      breakpoints: breakpoints,
      cols: cols,
      layouts: {
        lg: layout,
        md: layout,
        sm: layout,
        xs: layout,
        xxs: layout
      },
      rowHeight: 30,
      onLayoutChange: function onLayoutChange(l) {
        return handleLayoutChange(l);
      }
    }, layout.map(function (item) {
      var Comp = widgetMap[item.i];
      return /*#__PURE__*/React$1.createElement("div", {
        key: item.i,
        "data-grid": item,
        role: "gridcell",
        tabIndex: 0,
        "aria-label": widgetTitles[item.i],
        onKeyDown: function onKeyDown(e) {
          return handleKeyDown(e, item);
        }
      }, Comp ? /*#__PURE__*/React$1.createElement(Comp, null) : /*#__PURE__*/React$1.createElement("div", {
        role: "region",
        "aria-label": "Unavailable Widget"
      }, /*#__PURE__*/React$1.createElement("p", null, "")));
    }));
  }

  function AppDashboard() {
    var _window$ArtPulseDashb, _window$ArtPulseDashb2;
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      role = _useState2[0],
      setRole = _useState2[1];
    var apiRoot = ((_window$ArtPulseDashb = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb === void 0 ? void 0 : _window$ArtPulseDashb.root) || '/wp-json/';
    var nonce = window.apNonce || ((_window$ArtPulseDashb2 = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb2 === void 0 ? void 0 : _window$ArtPulseDashb2.nonce) || '';
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/me"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (res) {
        return res.json();
      }).then(function (data) {
        return setRole(data.role);
      });
    }, []);
    var logout = function logout() {
      return window.location.href = '/wp-login.php?action=logout';
    };
    return /*#__PURE__*/React$1.createElement("div", {
      className: "min-h-screen bg-gray-100"
    }, /*#__PURE__*/React$1.createElement(DashboardNavbar, {
      userRole: role,
      onLogout: logout
    }), /*#__PURE__*/React$1.createElement("main", {
      className: "p-4"
    }, /*#__PURE__*/React$1.createElement(DashboardContainer, {
      role: role
    }), /*#__PURE__*/React$1.createElement(MessagesPanel, null), /*#__PURE__*/React$1.createElement(CommunityAnalyticsPanel, null)));
  }
  document.addEventListener('DOMContentLoaded', function () {
    var rootEl = document.getElementById('ap-dashboard-root');
    if (rootEl) {
      var root = client.createRoot(rootEl);
      root.render(/*#__PURE__*/React$1.createElement(AppDashboard, null));
    }
  });

  // Expose for debugging when loaded directly
  window.APDashboardApp = AppDashboard;

})(React, ReactDOM, Chart, ReactGridLayout);
