var APDashboardContainer = (function (React, reactGridLayout, require$$0) {
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

  var m = require$$0;
  {
    m.createRoot;
    m.hydrateRoot;
  }

  function NearbyEventsMapWidget(_ref) {
    var apiRoot = _ref.apiRoot;
      _ref.nonce;
      var lat = _ref.lat,
      lng = _ref.lng;
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      events = _useState2[0],
      setEvents = _useState2[1];
    React.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/events/nearby?lat=").concat(lat, "&lng=").concat(lng)).then(function (r) {
        return r.json();
      }).then(setEvents);
    }, [lat, lng]);
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-nearby-events-widget"
    }, /*#__PURE__*/React.createElement("ul", null, events.map(function (ev) {
      return /*#__PURE__*/React.createElement("li", {
        key: ev.id
      }, /*#__PURE__*/React.createElement("a", {
        href: ev.link
      }, ev.title), " (", ev.distance, " km)");
    })));
  }

  function MyFavoritesWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      items = _useState2[0],
      setItems = _useState2[1];
    React.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/follows?post_type=artpulse_event"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(setItems);
    }, []);
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-favorites-widget"
    }, /*#__PURE__*/React.createElement("ul", null, items.map(function (i) {
      return /*#__PURE__*/React.createElement("li", {
        key: i.post_id
      }, /*#__PURE__*/React.createElement("a", {
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
    var _useState = React.useState(false),
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
    return /*#__PURE__*/React.createElement("button", {
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
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      messages = _useState2[0],
      setMessages = _useState2[1];
    var _useState3 = React.useState(''),
      _useState4 = _slicedToArray(_useState3, 2),
      text = _useState4[0],
      setText = _useState4[1];
    React.useEffect(function () {
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
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-event-chat-widget"
    }, /*#__PURE__*/React.createElement("ul", {
      className: "chat-thread"
    }, messages.map(function (m, i) {
      return /*#__PURE__*/React.createElement("li", {
        key: i
      }, /*#__PURE__*/React.createElement("strong", null, m.author, ":"), " ", m.content);
    })), /*#__PURE__*/React.createElement("div", {
      className: "chat-form"
    }, /*#__PURE__*/React.createElement("input", {
      value: text,
      onChange: function onChange(e) {
        return setText(e.target.value);
      },
      placeholder: __$8('Write a message...', 'artpulse')
    }), /*#__PURE__*/React.createElement("button", {
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
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-share-event-widget"
    }, /*#__PURE__*/React.createElement("button", {
      onClick: function onClick() {
        return share('https://twitter.com/share?url=');
      }
    }, "X"), /*#__PURE__*/React.createElement("button", {
      onClick: function onClick() {
        return share('https://www.facebook.com/sharer/sharer.php?u=');
      }
    }, __$7('Facebook', 'artpulse')), /*#__PURE__*/React.createElement("button", {
      onClick: function onClick() {
        return share('https://www.linkedin.com/sharing/share-offsite/?url=');
      }
    }, __$7('LinkedIn', 'artpulse')), /*#__PURE__*/React.createElement("button", {
      onClick: copy
    }, __$7('Copy Link', 'artpulse')));
  }

  var __$6 = wp.i18n.__;
  function ArtistInboxPreviewWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      threads = _useState2[0],
      setThreads = _useState2[1];
    React.useEffect(function () {
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
      return /*#__PURE__*/React.createElement("p", null, __$6('Loading...', 'artpulse'));
    }
    if (!threads.length) {
      return /*#__PURE__*/React.createElement("p", null, __$6('No new messages.', 'artpulse'));
    }
    return /*#__PURE__*/React.createElement("ul", {
      className: "ap-inbox-preview-list"
    }, threads.slice(0, 3).map(function (t) {
      return /*#__PURE__*/React.createElement("li", {
        key: t.user_id
      }, /*#__PURE__*/React.createElement("strong", null, t.display_name), t.preview && /*#__PURE__*/React.createElement("span", null, ": ", t.preview), t.date && /*#__PURE__*/React.createElement("em", null, " ", new Date(t.date).toLocaleDateString()));
    }));
  }

  var __$5 = wp.i18n.__;
  function ArtistRevenueSummaryWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      data = _useState2[0],
      setData = _useState2[1];
    React.useEffect(function () {
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
      return /*#__PURE__*/React.createElement("p", null, __$5('Loading...', 'artpulse'));
    }
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-revenue-summary"
    }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, data.total_revenue), " ", __$5('total revenue this month', 'artpulse')), /*#__PURE__*/React.createElement("p", null, data.tickets_sold, " ", __$5('tickets sold', 'artpulse')));
  }

  var __$4 = wp.i18n.__;
  function ArtistSpotlightWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      items = _useState2[0],
      setItems = _useState2[1];
    React.useEffect(function () {
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
      return /*#__PURE__*/React.createElement("p", null, __$4('Loading...', 'artpulse'));
    }
    if (!items.length) {
      return /*#__PURE__*/React.createElement("p", null, __$4('No mentions yet.', 'artpulse'));
    }
    return /*#__PURE__*/React.createElement("ul", {
      className: "ap-spotlight-list"
    }, items.slice(0, 3).map(function (it) {
      return /*#__PURE__*/React.createElement("li", {
        key: it.id
      }, /*#__PURE__*/React.createElement("a", {
        href: it.link
      }, it.title));
    }));
  }

  function AudienceCRMWidget(_ref) {
    var apiRoot = _ref.apiRoot,
      nonce = _ref.nonce,
      orgId = _ref.orgId;
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      contacts = _useState2[0],
      setContacts = _useState2[1];
    React.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/org/").concat(orgId, "/audience"), {
        headers: {
          'X-WP-Nonce': nonce
        },
        credentials: 'same-origin'
      }).then(function (r) {
        return r.json();
      }).then(setContacts);
    }, [orgId]);
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-audience-crm-widget"
    }, /*#__PURE__*/React.createElement("ul", null, contacts.map(function (c) {
      return /*#__PURE__*/React.createElement("li", {
        key: c.email
      }, c.name || c.email);
    })));
  }

  var __$3 = wp.i18n.__;
  function SponsoredEventConfigWidget(_ref) {
    var postId = _ref.postId,
      apiRoot = _ref.apiRoot,
      nonce = _ref.nonce;
    var _useState = React.useState({
        sponsor_name: '',
        sponsor_link: '',
        sponsor_logo: ''
      }),
      _useState2 = _slicedToArray(_useState, 2),
      data = _useState2[0],
      setData = _useState2[1];
    React.useEffect(function () {
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
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-sponsored-config"
    }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, __$3('Sponsored By', 'artpulse'), /*#__PURE__*/React.createElement("input", {
      type: "text",
      value: data.sponsor_name,
      onChange: function onChange(e) {
        return setData(_objectSpread2(_objectSpread2({}, data), {}, {
          sponsor_name: e.target.value
        }));
      }
    }))), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, __$3('Sponsor Link', 'artpulse'), /*#__PURE__*/React.createElement("input", {
      type: "url",
      value: data.sponsor_link,
      onChange: function onChange(e) {
        return setData(_objectSpread2(_objectSpread2({}, data), {}, {
          sponsor_link: e.target.value
        }));
      }
    }))), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, __$3('Logo URL', 'artpulse'), /*#__PURE__*/React.createElement("input", {
      type: "text",
      value: data.sponsor_logo,
      onChange: function onChange(e) {
        return setData(_objectSpread2(_objectSpread2({}, data), {}, {
          sponsor_logo: e.target.value
        }));
      }
    }))), /*#__PURE__*/React.createElement("button", {
      type: "button",
      onClick: save
    }, __$3('Save Sponsor', 'artpulse')));
  }

  var __$2 = wp.i18n.__;
  function EmbedToolWidget(_ref) {
    var widgetId = _ref.widgetId,
      siteUrl = _ref.siteUrl;
    var _useState = React.useState('light'),
      _useState2 = _slicedToArray(_useState, 2),
      theme = _useState2[0],
      setTheme = _useState2[1];
    var code = "<script src=\"".concat(siteUrl, "/wp-json/widgets/embed.js?id=").concat(widgetId, "&theme=").concat(theme, "\"></script>");
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-embed-tool-widget"
    }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, __$2('Theme', 'artpulse'), /*#__PURE__*/React.createElement("select", {
      value: theme,
      onChange: function onChange(e) {
        return setTheme(e.target.value);
      }
    }, /*#__PURE__*/React.createElement("option", {
      value: "light"
    }, __$2('Light', 'artpulse')), /*#__PURE__*/React.createElement("option", {
      value: "dark"
    }, __$2('Dark', 'artpulse'))))), /*#__PURE__*/React.createElement("textarea", {
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
    var _useState = React.useState({
        logo: '',
        color: '#000000',
        footer: ''
      }),
      _useState2 = _slicedToArray(_useState, 2),
      settings = _useState2[0],
      setSettings = _useState2[1];
    React.useEffect(function () {
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
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-org-branding-settings"
    }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, __$1('Logo URL', 'artpulse'), /*#__PURE__*/React.createElement("input", {
      type: "text",
      value: settings.logo,
      onChange: function onChange(e) {
        return setSettings(_objectSpread2(_objectSpread2({}, settings), {}, {
          logo: e.target.value
        }));
      }
    }))), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, __$1('Brand Color', 'artpulse'), /*#__PURE__*/React.createElement("input", {
      type: "color",
      value: settings.color,
      onChange: function onChange(e) {
        return setSettings(_objectSpread2(_objectSpread2({}, settings), {}, {
          color: e.target.value
        }));
      }
    }))), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("label", null, __$1('Footer Text', 'artpulse'), /*#__PURE__*/React.createElement("input", {
      type: "text",
      value: settings.footer,
      onChange: function onChange(e) {
        return setSettings(_objectSpread2(_objectSpread2({}, settings), {}, {
          footer: e.target.value
        }));
      }
    }))), /*#__PURE__*/React.createElement("button", {
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
    id: 'artist_inbox_preview',
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

  var GridLayout = reactGridLayout.WidthProvider(reactGridLayout.Responsive);
  function DashboardContainer(_ref) {
    var _window$ArtPulseDashb, _window$ArtPulseDashb2;
    var _ref$role = _ref.role,
      role = _ref$role === void 0 ? 'member' : _ref$role;
    var apiRoot = ((_window$ArtPulseDashb = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb === void 0 ? void 0 : _window$ArtPulseDashb.root) || '/wp-json/';
    var nonce = ((_window$ArtPulseDashb2 = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb2 === void 0 ? void 0 : _window$ArtPulseDashb2.nonce) || '';
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      layout = _useState2[0],
      setLayout = _useState2[1];
    var widgets = registry.filter(function (w) {
      return !w.roles || w.roles.includes(role);
    });
    React.useEffect(function () {
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
    return /*#__PURE__*/React.createElement(GridLayout, {
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
      return /*#__PURE__*/React.createElement("div", {
        key: item.i,
        "data-grid": item
      }, Comp ? /*#__PURE__*/React.createElement(Comp, null) : item.i);
    }));
  }

  return DashboardContainer;

})(React, ReactGridLayout, ReactDOM);
