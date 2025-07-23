var APDashboardContainer = (function (React, GridLayout, require$$0) {
  'use strict';

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
      onLayoutChange: function onLayoutChange(l, all) {
        return handleLayoutChange(all.lg);
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
