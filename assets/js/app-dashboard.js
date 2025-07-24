var APDashboardApp = (function (React$1, require$$0, Chart, GridLayout) {
  'use strict';
  var __ = wp.i18n.__;

  GridLayout = GridLayout.WidthProvider(GridLayout.Responsive);

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

  function DashboardNavbar(_ref) {
    var userRole = _ref.userRole,
      onLogout = _ref.onLogout;
    return /*#__PURE__*/React.createElement("nav", {
      className: "bg-white border-b shadow-sm px-4 py-2 flex justify-between items-center"
    }, /*#__PURE__*/React.createElement("div", {
      className: "text-lg font-bold"
    }, "ArtPulse Dashboard"), /*#__PURE__*/React.createElement("div", {
      className: "flex gap-4 items-center"
    }, userRole === 'artist' && /*#__PURE__*/React.createElement("a", {
      href: "#/artist",
      className: "text-blue-600"
    }, "Artist"), userRole === 'organization' && /*#__PURE__*/React.createElement("a", {
      href: "#/org",
      className: "text-blue-600"
    }, "Organization"), userRole === 'member' && /*#__PURE__*/React.createElement("a", {
      href: "#/member",
      className: "text-blue-600"
    }, "Member"), /*#__PURE__*/React.createElement("button", {
      onClick: onLogout,
      className: "bg-red-500 text-white px-3 py-1 rounded"
    }, "Logout")));
  }

  function MessagesPanel() {
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      messages = _useState2[0],
      setMessages = _useState2[1];
    React$1.useEffect(function () {
      fetch('/wp-json/artpulse/v1/dashboard/messages').then(function (res) {
        if (res.status === 401 || res.status === 403) {
          setMessages([{
            id: 0,
            content: __('Please log in to view messages.', 'artpulse')
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

  function TopUsersTable(_ref) {
    var _ref$users = _ref.users,
      users = _ref$users === void 0 ? [] : _ref$users;
    return /*#__PURE__*/React$1.createElement("table", {
      className: "min-w-full text-sm"
    }, /*#__PURE__*/React$1.createElement("thead", null, /*#__PURE__*/React$1.createElement("tr", null, /*#__PURE__*/React$1.createElement("th", {
      className: "text-left"
    }, "User"), /*#__PURE__*/React$1.createElement("th", {
      className: "text-right"
    }, "Count"))), /*#__PURE__*/React$1.createElement("tbody", null, users.map(function (u) {
      return /*#__PURE__*/React$1.createElement("tr", {
        key: u.user_id,
        className: "border-b"
      }, /*#__PURE__*/React$1.createElement("td", null, u.user_id), /*#__PURE__*/React$1.createElement("td", {
        className: "text-right"
      }, u.c));
    })));
  }

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
            label: 'Count',
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

  function FlaggedActivityLog(_ref) {
    var _ref$items = _ref.items,
      items = _ref$items === void 0 ? [] : _ref$items;
    return /*#__PURE__*/React$1.createElement("ul", {
      className: "space-y-1 text-sm"
    }, items.map(function (i) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: i.post_id || i.thread_id,
        className: "border-b pb-1"
      }, i.post_id || i.thread_id, " - ", i.c, " flags");
    }));
  }

  function CommunityAnalyticsPanel() {
    var _useState = React$1.useState('messaging'),
      _useState2 = _slicedToArray(_useState, 2),
      tab = _useState2[0],
      setTab = _useState2[1];
    var _useState3 = React$1.useState({}),
      _useState4 = _slicedToArray(_useState3, 2),
      data = _useState4[0],
      setData = _useState4[1];
    React$1.useEffect(function () {
      fetch("/wp-json/artpulse/v1/analytics/community/".concat(tab)).then(function (res) {
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
      }, t.charAt(0).toUpperCase() + t.slice(1));
    })), /*#__PURE__*/React$1.createElement("div", {
      className: "grid gap-4 md:grid-cols-2 mb-4"
    }, /*#__PURE__*/React$1.createElement(AnalyticsCard, {
      label: "Total",
      value: data.total
    }), data.flagged_count !== undefined && /*#__PURE__*/React$1.createElement(AnalyticsCard, {
      label: "Flagged",
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

  var m = require$$0;
  if (process.env.NODE_ENV === 'production') {
    m.createRoot;
    m.hydrateRoot;
  } else {
    m.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED;
  }

  function NearbyEventsMapWidget(_ref) {
    var apiRoot = _ref.apiRoot;
      _ref.nonce;
      var lat = _ref.lat,
      lng = _ref.lng;
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      events = _useState2[0],
      setEvents = _useState2[1];
    React$1.useEffect(function () {
      fetch("".concat(apiRoot, "artpulse/v1/events/nearby?lat=").concat(lat, "&lng=").concat(lng)).then(function (r) {
        return r.json();
      }).then(setEvents);
    }, [lat, lng]);
    return /*#__PURE__*/React$1.createElement("div", {
      className: "ap-nearby-events-widget"
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
      className: "ap-favorites-widget"
    }, /*#__PURE__*/React$1.createElement("ul", null, items.map(function (i) {
      return /*#__PURE__*/React$1.createElement("li", {
        key: i.post_id
      }, /*#__PURE__*/React$1.createElement("a", {
        href: i.link
      }, i.title));
    })));
  }

  var registry = [{
    id: 'nearby_events_map',
    title: 'Nearby Events Map',
    component: NearbyEventsMapWidget,
    roles: ['member', 'artist']
  }, {
    id: 'my_favorites',
    title: 'My Favorites',
    component: MyFavoritesWidget,
    roles: ['member', 'artist']
  }];

  function DashboardContainer(_ref) {
    var _window$ArtPulseDashb, _window$ArtPulseDashb2;
    var _ref$role = _ref.role,
      role = _ref$role === void 0 ? 'member' : _ref$role;
    var apiRoot = ((_window$ArtPulseDashb = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb === void 0 ? void 0 : _window$ArtPulseDashb.root) || '/wp-json/';
    var nonce = ((_window$ArtPulseDashb2 = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb2 === void 0 ? void 0 : _window$ArtPulseDashb2.nonce) || '';
    var _useState = React$1.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      layout = _useState2[0],
      setLayout = _useState2[1];
    var widgets = registry.filter(function (w) {
      return !w.roles || w.roles.includes(role);
    });
    React$1.useEffect(function () {
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
    return /*#__PURE__*/React$1.createElement(GridLayout, {
      className: "layout",
      layouts: {
        lg: layout
      },
      cols: {
        lg: 12
      },
      rowHeight: 30,
      onLayoutChange: function onLayoutChange(l, all) {
        return handleLayoutChange(all.lg);
      }
    }, layout.map(function (item) {
      var Comp = widgetMap[item.i];
      return /*#__PURE__*/React$1.createElement("div", {
        key: item.i,
        "data-grid": item
      }, Comp ? /*#__PURE__*/React$1.createElement(Comp, null) : item.i);
    }));
  }

  function AppDashboard() {
    var _useState = React$1.useState(null),
      _useState2 = _slicedToArray(_useState, 2),
      role = _useState2[0],
      setRole = _useState2[1];
    React$1.useEffect(function () {
      fetch('/wp-json/artpulse/v1/me').then(function (res) {
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
    if (rootEl && window.ReactDOM) {
      require$$0.render(/*#__PURE__*/React$1.createElement(AppDashboard, null), rootEl);
    }
  });

  return AppDashboard;

})(React, ReactDOM, Chart, GridLayout);
