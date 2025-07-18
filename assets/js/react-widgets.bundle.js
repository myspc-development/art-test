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
  function _slicedToArray(r, e) {
    return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest();
  }
  function _unsupportedIterableToArray(r, a) {
    if (r) {
      if ("string" == typeof r) return _arrayLikeToArray(r, a);
      var t = {}.toString.call(r).slice(8, -1);
      return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
    }
  }

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
    var load = function load() {
      fetch("".concat(APChat.apiRoot, "artpulse/v1/event/").concat(eventId, "/chat")).then(function (r) {
        return r.json();
      }).then(setMessages);
    };
    React.useEffect(function () {
      load();
      var id = setInterval(load, 10000);
      return function () {
        return clearInterval(id);
      };
    }, [eventId]);
    React.useEffect(function () {
      var list = document.querySelector('.ap-chat-list');
      if (list) list.scrollTop = list.scrollHeight;
    }, [messages]);
    var send = function send(e) {
      e.preventDefault();
      var msg = text.trim();
      if (!msg) return;
      fetch("".concat(APChat.apiRoot, "artpulse/v1/event/").concat(eventId, "/chat"), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': APChat.nonce
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
      className: "ap-event-chat",
      "data-event-id": eventId
    }, /*#__PURE__*/React.createElement("ul", {
      className: "ap-chat-list",
      role: "status",
      "aria-live": "polite"
    }, messages.map(function (m) {
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
      }, m.content));
    })), canPost ? /*#__PURE__*/React.createElement("form", {
      className: "ap-chat-form",
      onSubmit: send
    }, /*#__PURE__*/React.createElement("input", {
      type: "text",
      "aria-label": "Chat message",
      value: text,
      onChange: function onChange(e) {
        return setText(e.target.value);
      }
    }), /*#__PURE__*/React.createElement("button", {
      type: "submit",
      "aria-label": "Send chat message"
    }, "Send")) : /*#__PURE__*/React.createElement("p", null, APChat.loggedIn ? 'Only attendees can post messages' : 'Please log in to chat.'));
  }

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
      "aria-label": "Your question",
      value: text,
      onChange: function onChange(e) {
        return setText(e.target.value);
      }
    }), /*#__PURE__*/React.createElement("button", {
      type: "submit",
      "aria-label": "Post question"
    }, "Post")));
  }

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
        return alert('Purchased!');
      });
    };
    if (loading) return /*#__PURE__*/React.createElement("p", null, "Loading tickets...");
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
        "aria-label": "Buy ".concat(tier.name)
      }, "Buy"));
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
  });

})(React, ReactDOM);
