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

  var __$5 = wp.i18n.__;
  function ChatWidget(_ref) {
    var eventId = _ref.eventId,
      canPost = _ref.canPost;
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      messages = _useState2[0],
      setMessages = _useState2[1];
    var _useState3 = React.useState(''),
      _useState4 = _slicedToArray(_useState3, 2),
      text = _useState4[0],
      setText = _useState4[1];
    var _useState5 = React.useState(false),
      _useState6 = _slicedToArray(_useState5, 2),
      showPicker = _useState6[0],
      setShowPicker = _useState6[1];
    var _useState7 = React.useState(null),
      _useState8 = _slicedToArray(_useState7, 2),
      error = _useState8[0],
      setError = _useState8[1];
    var fetching = React.useRef(false);
    var load = /*#__PURE__*/function () {
      var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
        var resp, data, _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              if (!fetching.current) {
                _context.n = 1;
                break;
              }
              return _context.a(2);
            case 1:
              fetching.current = true;
              _context.p = 2;
              _context.n = 3;
              return fetch("".concat(APChat.apiRoot, "artpulse/v1/event/").concat(eventId, "/chat"), {
                headers: {
                  'X-WP-Nonce': APChat.nonce
                },
                credentials: 'same-origin'
              });
            case 3:
              resp = _context.v;
              _context.n = 4;
              return resp.json();
            case 4:
              data = _context.v;
              if (Array.isArray(data)) {
                setMessages(data);
              } else {
                setMessages([]);
              }
              _context.n = 6;
              break;
            case 5:
              _context.p = 5;
              _t = _context.v;
              setError(_t);
            case 6:
              _context.p = 6;
              fetching.current = false;
              return _context.f(6);
            case 7:
              return _context.a(2);
          }
        }, _callee, null, [[2, 5, 6, 7]]);
      }));
      return function load() {
        return _ref2.apply(this, arguments);
      };
    }();
    React.useEffect(function () {
      load();
      var id = null;
      if (!window.IS_DASHBOARD_BUILDER_PREVIEW) {
        id = setInterval(load, 5000);
      }
      return function () {
        if (id) clearInterval(id);
      };
    }, [eventId]);
    React.useEffect(function () {
      var list = document.querySelector('.ap-chat-list');
      if (list) list.scrollTop = list.scrollHeight;
    }, [messages]);
    var send = /*#__PURE__*/function () {
      var _ref3 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(e) {
        var msg, _t2;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.n) {
            case 0:
              e.preventDefault();
              msg = text.trim();
              if (msg) {
                _context2.n = 1;
                break;
              }
              return _context2.a(2);
            case 1:
              _context2.p = 1;
              _context2.n = 2;
              return fetch("".concat(APChat.apiRoot, "artpulse/v1/event/").concat(eventId, "/chat"), {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-WP-Nonce': APChat.nonce
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                  content: msg
                })
              });
            case 2:
              setText('');
              setShowPicker(false);
              load();
              _context2.n = 4;
              break;
            case 3:
              _context2.p = 3;
              _t2 = _context2.v;
              setError(_t2);
            case 4:
              return _context2.a(2);
          }
        }, _callee2, null, [[1, 3]]);
      }));
      return function send(_x) {
        return _ref3.apply(this, arguments);
      };
    }();
    var react = /*#__PURE__*/function () {
      var _ref4 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee3(id, emoji) {
        var _t3;
        return _regenerator().w(function (_context3) {
          while (1) switch (_context3.n) {
            case 0:
              _context3.p = 0;
              _context3.n = 1;
              return fetch("".concat(APChat.apiRoot, "artpulse/v1/chat/").concat(id, "/reaction"), {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-WP-Nonce': APChat.nonce
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                  emoji: emoji
                })
              });
            case 1:
              load();
              _context3.n = 3;
              break;
            case 2:
              _context3.p = 2;
              _t3 = _context3.v;
              setError(_t3);
            case 3:
              return _context3.a(2);
          }
        }, _callee3, null, [[0, 2]]);
      }));
      return function react(_x2, _x3) {
        return _ref4.apply(this, arguments);
      };
    }();
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-event-chat",
      "data-event-id": eventId
    }, /*#__PURE__*/React.createElement("ul", {
      className: "ap-chat-list",
      role: "status",
      "aria-live": "polite"
    }, Array.isArray(messages) && messages.length > 0 ? messages.map(function (m) {
      return /*#__PURE__*/React.createElement("li", {
        key: m.id
      }, /*#__PURE__*/React.createElement("img", {
        className: "ap-chat-avatar",
        src: m.avatar,
        alt: ""
      }), /*#__PURE__*/React.createElement("span", {
        className: "ap-chat-author"
      }, m.author), /*#__PURE__*/React.createElement("span", {
        className: "ap-chat-time"
      }, new Intl.DateTimeFormat('en', {
        timeStyle: 'short'
      }).format(new Date(m.created_at))), /*#__PURE__*/React.createElement("p", {
        className: "ap-chat-content"
      }, m.content), /*#__PURE__*/React.createElement("div", {
        className: "ap-chat-reactions"
      }, m.reactions && Object.entries(m.reactions).map(function (_ref5) {
        var _ref6 = _slicedToArray(_ref5, 2),
          emo = _ref6[0],
          c = _ref6[1];
        return /*#__PURE__*/React.createElement("button", {
          key: emo,
          type: "button",
          onClick: function onClick() {
            return react(m.id, emo);
          }
        }, emo, " ", c);
      }), /*#__PURE__*/React.createElement("button", {
        type: "button",
        onClick: function onClick() {
          return react(m.id, '❤️');
        }
      }, "\u2764\uFE0F"), /*#__PURE__*/React.createElement("button", {
        type: "button",
        onClick: function onClick() {
          return react(m.id, '👍');
        }
      }, "\uD83D\uDC4D")));
    }) : /*#__PURE__*/React.createElement("li", {
      className: "ap-chat-empty"
    }, __$5('No messages yet.', 'artpulse'))), error && /*#__PURE__*/React.createElement("p", {
      className: "ap-chat-error"
    }, __$5('Unable to load chat.', 'artpulse')), canPost ? /*#__PURE__*/React.createElement("form", {
      className: "ap-chat-form",
      onSubmit: send
    }, /*#__PURE__*/React.createElement("input", {
      type: "text",
      "aria-label": __$5('Chat message', 'artpulse'),
      value: text,
      onChange: function onChange(e) {
        return setText(e.target.value);
      }
    }), /*#__PURE__*/React.createElement("button", {
      type: "button",
      onClick: function onClick() {
        return setShowPicker(!showPicker);
      }
      }, "\uD83D\uDE0A"), showPicker && typeof window.EmojiPicker !== 'undefined' && /*#__PURE__*/React.createElement(window.EmojiPicker, {
      onEmojiClick: function onEmojiClick(e) {
        return setText(function (t) {
          return t + e.emoji;
        });
      }
    }), /*#__PURE__*/React.createElement("button", {
      type: "submit",
      "aria-label": __$5('Send chat message', 'artpulse')
    }, __$5('Send', 'artpulse'))) : /*#__PURE__*/React.createElement("p", null, APChat.loggedIn ? __$5('Only attendees can post messages', 'artpulse') : __$5('Please log in to chat.', 'artpulse')));
  }

  var __$4 = wp.i18n.__;
  function QaWidget(_ref) {
    var eventId = _ref.eventId,
      canPost = _ref.canPost;
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      comments = _useState2[0],
      setComments = _useState2[1];
    var _useState3 = React.useState(''),
      _useState4 = _slicedToArray(_useState3, 2),
      text = _useState4[0],
      setText = _useState4[1];
    var load = function load() {
      fetch("".concat(APQa.apiRoot, "artpulse/v1/qa-thread/").concat(eventId)).then(function (r) {
        return r.json();
      }).then(function (d) {
        return setComments(d.comments || []);
      });
    };
    React.useEffect(function () {
      load();
    }, [eventId]);
    var send = function send(e) {
      e.preventDefault();
      var msg = text.trim();
      if (!msg) return;
      fetch("".concat(APQa.apiRoot, "artpulse/v1/qa-thread/").concat(eventId, "/post"), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APQa.nonce
        },
        body: JSON.stringify({
          content: msg
        })
      }).then(function () {
        setText('');
        load();
      });
    };
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-qa-thread",
      "data-event-id": eventId
    }, /*#__PURE__*/React.createElement("ul", {
      className: "ap-qa-list"
    }, comments.map(function (c) {
      return /*#__PURE__*/React.createElement("li", {
        key: c.id
      }, c.author, ": ", c.content);
    })), canPost && /*#__PURE__*/React.createElement("form", {
      className: "ap-qa-form",
      onSubmit: send
    }, /*#__PURE__*/React.createElement("textarea", {
      required: true,
      "aria-label": __$4('Your question', 'artpulse'),
      value: text,
      onChange: function onChange(e) {
        return setText(e.target.value);
      }
    }), /*#__PURE__*/React.createElement("button", {
      type: "submit",
      "aria-label": __$4('Post question', 'artpulse')
    }, __$4('Post', 'artpulse'))));
  }

  var __$3 = wp.i18n.__;
  function TicketWidget(_ref) {
    var eventId = _ref.eventId;
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      tiers = _useState2[0],
      setTiers = _useState2[1];
    var _useState3 = React.useState(true),
      _useState4 = _slicedToArray(_useState3, 2),
      loading = _useState4[0],
      setLoading = _useState4[1];
    React.useEffect(function () {
      fetch("".concat(APTickets.apiRoot, "artpulse/v1/event/").concat(eventId, "/tickets")).then(function (r) {
        return r.json();
      }).then(function (d) {
        setTiers(d.tiers || []);
        setLoading(false);
      });
    }, [eventId]);
    var buy = function buy(tier) {
      fetch("".concat(APTickets.apiRoot, "artpulse/v1/event/").concat(eventId, "/buy-ticket"), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APTickets.nonce
        },
        body: JSON.stringify({
          ticket_id: tier.id
        })
      }).then(function () {
        return alert(__$3('Purchased!', 'artpulse'));
      });
    };
    if (loading) return /*#__PURE__*/React.createElement("p", null, __$3('Loading tickets...', 'artpulse'));
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-tickets",
      "data-event-id": eventId
    }, /*#__PURE__*/React.createElement("ul", {
      className: "ap-ticket-list"
    }, tiers.map(function (tier) {
      return /*#__PURE__*/React.createElement("li", {
        key: tier.id,
        className: "ap-ticket-tier"
      }, /*#__PURE__*/React.createElement("span", null, tier.name, " - ", tier.price), /*#__PURE__*/React.createElement("button", {
        onClick: function onClick() {
          return buy(tier);
        },
        "aria-label": "".concat(__$3('Buy', 'artpulse'), " ").concat(tier.name)
      }, __$3('Buy', 'artpulse')));
    })));
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
    if (!lat || !lng) {
      setEvents([]);
      return;
    }
    fetch("".concat(apiRoot, "artpulse/v1/events/nearby?lat=").concat(lat, "&lng=").concat(lng)).then(function (r) {
      return r.ok ? r.json() : [];
    }).then(function (data) {
      return Array.isArray(data) ? setEvents(data) : setEvents([]);
    })["catch"](function () {
      return setEvents([]);
    });
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
  function initNearbyEventsMapWidget(el) {
    var root = client.createRoot(el);
    var _el$dataset = el.dataset,
      lat = _el$dataset.lat,
      lng = _el$dataset.lng,
      apiRoot = _el$dataset.apiRoot,
      nonce = _el$dataset.nonce;
    root.render(/*#__PURE__*/React.createElement(NearbyEventsMapWidget, {
      apiRoot: apiRoot,
      nonce: nonce,
      lat: lat,
      lng: lng
    }));
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
  function initMyFavoritesWidget(el) {
    var root = client.createRoot(el);
    var _el$dataset = el.dataset,
      apiRoot = _el$dataset.apiRoot,
      nonce = _el$dataset.nonce;
    root.render(/*#__PURE__*/React.createElement(MyFavoritesWidget, {
      apiRoot: apiRoot,
      nonce: nonce
    }));
  }

  var __$2 = wp.i18n.__;
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
      return /*#__PURE__*/React.createElement("p", null, __$2('Loading...', 'artpulse'));
    }
    if (!threads.length) {
      return /*#__PURE__*/React.createElement("p", null, __$2('No new messages.', 'artpulse'));
    }
    return /*#__PURE__*/React.createElement("ul", {
      className: "ap-inbox-preview-list"
    }, threads.slice(0, 3).map(function (t) {
      return /*#__PURE__*/React.createElement("li", {
        key: t.user_id
      }, /*#__PURE__*/React.createElement("strong", null, t.display_name), t.preview && /*#__PURE__*/React.createElement("span", null, ": ", t.preview), t.date && /*#__PURE__*/React.createElement("em", null, " ", new Date(t.date).toLocaleDateString()));
    }));
  }
  function initArtistInboxPreviewWidget(el) {
    var root = client.createRoot(el);
    var _el$dataset = el.dataset,
      apiRoot = _el$dataset.apiRoot,
      nonce = _el$dataset.nonce;
    root.render(/*#__PURE__*/React.createElement(ArtistInboxPreviewWidget, {
      apiRoot: apiRoot,
      nonce: nonce
    }));
  }

  var __$1 = wp.i18n.__;
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
      return /*#__PURE__*/React.createElement("p", null, __$1('Loading...', 'artpulse'));
    }
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-revenue-summary"
    }, /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("strong", null, data.total_revenue), " ", __$1('total revenue this month', 'artpulse')), /*#__PURE__*/React.createElement("p", null, data.tickets_sold, " ", __$1('tickets sold', 'artpulse')));
  }
  function initArtistRevenueSummaryWidget(el) {
    var root = client.createRoot(el);
    var _el$dataset = el.dataset,
      apiRoot = _el$dataset.apiRoot,
      nonce = _el$dataset.nonce;
    root.render(/*#__PURE__*/React.createElement(ArtistRevenueSummaryWidget, {
      apiRoot: apiRoot,
      nonce: nonce
    }));
  }

  var __ = wp.i18n.__;
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
      return /*#__PURE__*/React.createElement("p", null, __('Loading...', 'artpulse'));
    }
    if (!items.length) {
      return /*#__PURE__*/React.createElement("p", null, __('No mentions yet.', 'artpulse'));
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
  function initArtistSpotlightWidget(el) {
    var root = client.createRoot(el);
    var _el$dataset = el.dataset,
      apiRoot = _el$dataset.apiRoot,
      nonce = _el$dataset.nonce;
    root.render(/*#__PURE__*/React.createElement(ArtistSpotlightWidget, {
      apiRoot: apiRoot,
      nonce: nonce
    }));
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ap-event-chat[data-event-id]').forEach(function (el) {
      var root = client.createRoot(el);
      var canPost = !!el.dataset.canPost;
      root.render(/*#__PURE__*/React.createElement(ChatWidget, {
        eventId: el.dataset.eventId,
        canPost: canPost
      }));
    });
    document.querySelectorAll('.ap-qa-thread[data-event-id]').forEach(function (el) {
      var root = client.createRoot(el);
      var canPost = !!el.dataset.canPost;
      root.render(/*#__PURE__*/React.createElement(QaWidget, {
        eventId: el.dataset.eventId,
        canPost: canPost
      }));
    });
    document.querySelectorAll('.ap-tickets[data-event-id]').forEach(function (el) {
      var root = client.createRoot(el);
      root.render(/*#__PURE__*/React.createElement(TicketWidget, {
        eventId: el.dataset.eventId
      }));
    });
    document.querySelectorAll('.ap-nearby-events-widget[data-api-root]').forEach(function (el) {
      initNearbyEventsMapWidget(el);
    });
    document.querySelectorAll('.ap-favorites-widget[data-api-root]').forEach(function (el) {
      initMyFavoritesWidget(el);
    });
    document.querySelectorAll('.ap-artist-inbox-preview[data-api-root]').forEach(function (el) {
      initArtistInboxPreviewWidget(el);
    });
    document.querySelectorAll('.ap-revenue-summary-widget[data-api-root]').forEach(function (el) {
      initArtistRevenueSummaryWidget(el);
    });
    document.querySelectorAll('.ap-artist-spotlight[data-api-root]').forEach(function (el) {
      initArtistSpotlightWidget(el);
    });
  });

})(React, ReactDOM);
