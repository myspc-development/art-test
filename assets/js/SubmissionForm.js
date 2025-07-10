"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = SubmissionForm;
var _react = _interopRequireWildcard(require("react"));
function _interopRequireWildcard(e, t) { if ("function" == typeof WeakMap) var r = new WeakMap(), n = new WeakMap(); return (_interopRequireWildcard = function _interopRequireWildcard(e, t) { if (!t && e && e.__esModule) return e; var o, i, f = { __proto__: null, "default": e }; if (null === e || "object" != _typeof(e) && "function" != typeof e) return f; if (o = t ? n : r) { if (o.has(e)) return o.get(e); o.set(e, f); } for (var _t3 in e) "default" !== _t3 && {}.hasOwnProperty.call(e, _t3) && ((i = (o = Object.defineProperty) && Object.getOwnPropertyDescriptor(e, _t3)) && (i.get || i.set) ? o(f, _t3, i) : f[_t3] = e[_t3]); return f; })(e, t); }
function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { if (r) i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n;else { var o = function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); }; o("next", 0), o("throw", 1), o("return", 2); } }, _regeneratorDefine2(e, r, n, t); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function SubmissionForm() {
  var _useState = (0, _react.useState)(''),
    _useState2 = _slicedToArray(_useState, 2),
    title = _useState2[0],
    setTitle = _useState2[1];
  var _useState3 = (0, _react.useState)(''),
    _useState4 = _slicedToArray(_useState3, 2),
    eventDate = _useState4[0],
    setEventDate = _useState4[1];
  var _useState5 = (0, _react.useState)(''),
    _useState6 = _slicedToArray(_useState5, 2),
    startDate = _useState6[0],
    setStartDate = _useState6[1];
  var _useState7 = (0, _react.useState)(''),
    _useState8 = _slicedToArray(_useState7, 2),
    endDate = _useState8[0],
    setEndDate = _useState8[1];
  var _useState9 = (0, _react.useState)(''),
    _useState0 = _slicedToArray(_useState9, 2),
    venueName = _useState0[0],
    setVenueName = _useState0[1];
  var _useState1 = (0, _react.useState)(''),
    _useState10 = _slicedToArray(_useState1, 2),
    location = _useState10[0],
    setLocation = _useState10[1];
  var _useState11 = (0, _react.useState)(''),
    _useState12 = _slicedToArray(_useState11, 2),
    streetAddress = _useState12[0],
    setStreetAddress = _useState12[1];
  var _useState13 = (0, _react.useState)(''),
    _useState14 = _slicedToArray(_useState13, 2),
    city = _useState14[0],
    setCity = _useState14[1];
  var _useState15 = (0, _react.useState)(''),
    _useState16 = _slicedToArray(_useState15, 2),
    stateProv = _useState16[0],
    setStateProv = _useState16[1];
  var _useState17 = (0, _react.useState)(''),
    _useState18 = _slicedToArray(_useState17, 2),
    country = _useState18[0],
    setCountry = _useState18[1];
  var _useState19 = (0, _react.useState)(''),
    _useState20 = _slicedToArray(_useState19, 2),
    postcode = _useState20[0],
    setPostcode = _useState20[1];
  var _useState21 = (0, _react.useState)(''),
    _useState22 = _slicedToArray(_useState21, 2),
    addressComponents = _useState22[0],
    setAddressComponents = _useState22[1];
  var _useState23 = (0, _react.useState)(Array(5).fill(null)),
    _useState24 = _slicedToArray(_useState23, 2),
    images = _useState24[0],
    setImages = _useState24[1];
  var _useState25 = (0, _react.useState)(null),
    _useState26 = _slicedToArray(_useState25, 2),
    banner = _useState26[0],
    setBanner = _useState26[1];
  var _useState27 = (0, _react.useState)(Array(5).fill(null)),
    _useState28 = _slicedToArray(_useState27, 2),
    previews = _useState28[0],
    setPreviews = _useState28[1];
  var _useState29 = (0, _react.useState)(Array(5).fill(0)),
    _useState30 = _slicedToArray(_useState29, 2),
    progresses = _useState30[0],
    setProgresses = _useState30[1];
  var _useState31 = (0, _react.useState)([0, 1, 2, 3, 4]),
    _useState32 = _slicedToArray(_useState31, 2),
    order = _useState32[0],
    setOrder = _useState32[1];
  var _useState33 = (0, _react.useState)(null),
    _useState34 = _slicedToArray(_useState33, 2),
    dragIndex = _useState34[0],
    setDragIndex = _useState34[1];
  var _useState35 = (0, _react.useState)(false),
    _useState36 = _slicedToArray(_useState35, 2),
    loading = _useState36[0],
    setLoading = _useState36[1];
  var _useState37 = (0, _react.useState)(''),
    _useState38 = _slicedToArray(_useState37, 2),
    message = _useState38[0],
    setMessage = _useState38[1];
  var _useState39 = (0, _react.useState)(''),
    _useState40 = _slicedToArray(_useState39, 2),
    organizerName = _useState40[0],
    setOrganizerName = _useState40[1];
  var _useState41 = (0, _react.useState)(''),
    _useState42 = _slicedToArray(_useState41, 2),
    organizerEmail = _useState42[0],
    setOrganizerEmail = _useState42[1];
  var _useState43 = (0, _react.useState)(false),
    _useState44 = _slicedToArray(_useState43, 2),
    featured = _useState44[0],
    setFeatured = _useState44[1];
  var _useState45 = (0, _react.useState)(false),
    _useState46 = _slicedToArray(_useState45, 2),
    rsvpEnabled = _useState46[0],
    setRsvpEnabled = _useState46[1];
  var _useState47 = (0, _react.useState)(''),
    _useState48 = _slicedToArray(_useState47, 2),
    rsvpLimit = _useState48[0],
    setRsvpLimit = _useState48[1];
  var _useState49 = (0, _react.useState)(false),
    _useState50 = _slicedToArray(_useState49, 2),
    waitlistEnabled = _useState50[0],
    setWaitlistEnabled = _useState50[1];
  var orderRef = (0, _react.useRef)(null);
  var handleImageChange = function handleImageChange(index) {
    return function (e) {
      var file = e.target.files[0] || null;
      setImages(function (prev) {
        var arr = _toConsumableArray(prev);
        arr[index] = file;
        return arr;
      });
      setPreviews(function (prev) {
        var arr = _toConsumableArray(prev);
        arr[index] = file ? URL.createObjectURL(file) : null;
        return arr;
      });
      setProgresses(function (prev) {
        var arr = _toConsumableArray(prev);
        arr[index] = 0;
        return arr;
      });
    };
  };
  var handleBannerChange = function handleBannerChange(e) {
    setBanner(e.target.files[0] || null);
  };
  (0, _react.useEffect)(function () {
    setAddressComponents(JSON.stringify({
      country: country,
      state: stateProv,
      city: city
    }));
  }, [country, stateProv, city]);
  (0, _react.useEffect)(function () {
    if (orderRef.current) {
      orderRef.current.value = order.join(',');
    }
  }, [order]);
  var handleDragStart = function handleDragStart(i) {
    return setDragIndex(i);
  };
  var handleDrop = function handleDrop(i) {
    if (dragIndex === null || dragIndex === i) return;
    setOrder(function (prev) {
      var ord = _toConsumableArray(prev);
      var _ord$splice = ord.splice(dragIndex, 1),
        _ord$splice2 = _slicedToArray(_ord$splice, 1),
        o = _ord$splice2[0];
      ord.splice(i, 0, o);
      return ord;
    });
    setDragIndex(null);
  };
  var uploadMedia = function uploadMedia(file, index) {
    return new Promise(function (resolve, reject) {
      var formData = new FormData();
      formData.append('file', file);
      var xhr = new XMLHttpRequest();
      xhr.open('POST', APSubmission.mediaEndpoint);
      xhr.setRequestHeader('X-WP-Nonce', APSubmission.nonce);
      xhr.upload.addEventListener('progress', function (e) {
        if (e.lengthComputable) {
          var percent = Math.round(e.loaded / e.total * 100);
          setProgresses(function (prev) {
            var copy = _toConsumableArray(prev);
            copy[index] = percent;
            return copy;
          });
        }
      });
      xhr.onload = function () {
        var json = {};
        try {
          json = JSON.parse(xhr.responseText);
        } catch (_) {}
        if (xhr.status >= 200 && xhr.status < 300) {
          resolve(json.id);
        } else {
          reject(new Error(json.message || 'Upload failed'));
        }
      };
      xhr.onerror = function () {
        return reject(new Error('Upload failed'));
      };
      xhr.send(formData);
    });
  };
  var handleSubmit = /*#__PURE__*/function () {
    var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(e) {
      var imageIds, _iterator, _step, idx, file, id, bannerId, res, json, _t, _t2;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            e.preventDefault();
            setLoading(true);
            setMessage('');
            _context.p = 1;
            imageIds = [];
            _iterator = _createForOfIteratorHelper(order);
            _context.p = 2;
            _iterator.s();
          case 3:
            if ((_step = _iterator.n()).done) {
              _context.n = 7;
              break;
            }
            idx = _step.value;
            file = images[idx];
            if (file) {
              _context.n = 4;
              break;
            }
            return _context.a(3, 6);
          case 4:
            setMessage("Uploading image ".concat(imageIds.length + 1));
            _context.n = 5;
            return uploadMedia(file, idx);
          case 5:
            id = _context.v;
            imageIds.push(id);
          case 6:
            _context.n = 3;
            break;
          case 7:
            _context.n = 9;
            break;
          case 8:
            _context.p = 8;
            _t = _context.v;
            _iterator.e(_t);
          case 9:
            _context.p = 9;
            _iterator.f();
            return _context.f(9);
          case 10:
            bannerId = null;
            if (!banner) {
              _context.n = 12;
              break;
            }
            setMessage('Uploading banner');
            _context.n = 11;
            return uploadMedia(banner, images.length);
          case 11:
            bannerId = _context.v;
          case 12:
            setMessage('Submitting form');
            _context.n = 13;
            return fetch(APSubmission.endpoint, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': APSubmission.nonce
              },
              body: JSON.stringify(_objectSpread(_objectSpread({
                post_type: 'artpulse_event',
                title: title,
                event_date: eventDate,
                event_start_date: startDate,
                event_end_date: endDate,
                venue_name: venueName,
                event_location: location,
                event_street_address: streetAddress,
                event_city: city,
                event_state: stateProv,
                event_country: country,
                event_postcode: postcode,
                event_organizer_name: organizerName,
                event_organizer_email: organizerEmail,
                event_rsvp_enabled: rsvpEnabled ? '1' : '0',
                event_waitlist_enabled: waitlistEnabled ? '1' : '0',
                event_featured: featured ? '1' : '0',
                image_ids: imageIds,
                address_components: addressComponents
              }, bannerId ? {
                event_banner_id: bannerId
              } : {}), rsvpLimit ? {
                event_rsvp_limit: parseInt(rsvpLimit, 10)
              } : {}))
            });
          case 13:
            res = _context.v;
            _context.n = 14;
            return res.json();
          case 14:
            json = _context.v;
            if (res.ok) {
              _context.n = 15;
              break;
            }
            throw new Error(json.message || 'Submission failed');
          case 15:
            setMessage('Submission successful!');
            setTimeout(function () {
              window.location.href = APSubmission.dashboardUrl;
            }, 3000);
            setTitle('');
            setEventDate('');
            setStartDate('');
            setEndDate('');
            setVenueName('');
            setLocation('');
            setStreetAddress('');
            setCity('');
            setStateProv('');
            setCountry('');
            setPostcode('');
            setOrganizerName('');
            setOrganizerEmail('');
            setFeatured(false);
            setRsvpEnabled(false);
            setRsvpLimit('');
            setWaitlistEnabled(false);
            setImages(Array(5).fill(null));
            setPreviews(Array(5).fill(null));
            setProgresses(Array(5).fill(0));
            setBanner(null);
            _context.n = 17;
            break;
          case 16:
            _context.p = 16;
            _t2 = _context.v;
            console.error(_t2);
            setMessage("Error: ".concat(_t2.message));
          case 17:
            _context.p = 17;
            setLoading(false);
            return _context.f(17);
          case 18:
            return _context.a(2);
        }
      }, _callee, null, [[2, 8, 9, 10], [1, 16, 17, 18]]);
    }));
    return function handleSubmit(_x) {
      return _ref.apply(this, arguments);
    };
  }();
  return /*#__PURE__*/_react["default"].createElement("form", {
    onSubmit: handleSubmit,
    className: "ap-form-container",
    "data-no-ajax": "true"
  }, /*#__PURE__*/_react["default"].createElement("div", {
    className: "ap-form-messages",
    role: "status",
    "aria-live": "polite"
  }, message), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_title"
  }, "Event Title"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_title",
    className: "ap-input",
    type: "text",
    value: title,
    onChange: function onChange(e) {
      return setTitle(e.target.value);
    },
    required: true
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_date"
  }, "Date"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_date",
    className: "ap-input",
    type: "date",
    value: eventDate,
    onChange: function onChange(e) {
      return setEventDate(e.target.value);
    },
    required: true
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_start"
  }, "Start Date"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_start",
    className: "ap-input",
    type: "date",
    value: startDate,
    onChange: function onChange(e) {
      return setStartDate(e.target.value);
    },
    required: true
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_end"
  }, "End Date"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_end",
    className: "ap-input",
    type: "date",
    value: endDate,
    onChange: function onChange(e) {
      return setEndDate(e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_venue_name"
  }, "Venue Name"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_venue_name",
    className: "ap-input",
    type: "text",
    value: venueName,
    onChange: function onChange(e) {
      return setVenueName(e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_street"
  }, "Street Address"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_street",
    className: "ap-input",
    type: "text",
    value: streetAddress,
    onChange: function onChange(e) {
      return setStreetAddress(e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_country"
  }, "Country"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_country",
    className: "ap-input ap-address-country",
    type: "text",
    value: country,
    onChange: function onChange(e) {
      return setCountry(e.target.value);
    },
    required: true
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_state"
  }, "State/Province"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_state",
    className: "ap-input ap-address-state",
    type: "text",
    value: stateProv,
    onChange: function onChange(e) {
      return setStateProv(e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_city"
  }, "City"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_city",
    className: "ap-input ap-address-city",
    type: "text",
    value: city,
    onChange: function onChange(e) {
      return setCity(e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_postcode"
  }, "Postcode"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_postcode",
    className: "ap-input ap-address-postcode",
    type: "text",
    value: postcode,
    onChange: function onChange(e) {
      return setPostcode(e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_location"
  }, "Location"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_location",
    className: "ap-input ap-google-autocomplete",
    type: "text",
    value: location,
    onChange: function onChange(e) {
      return setLocation(e.target.value);
    },
    required: true
  }), /*#__PURE__*/_react["default"].createElement("input", {
    type: "hidden",
    value: addressComponents,
    readOnly: true,
    name: "address_components"
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_organizer"
  }, "Organizer Name"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_organizer",
    className: "ap-input",
    type: "text",
    value: organizerName,
    onChange: function onChange(e) {
      return setOrganizerName(e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_event_organizer_email"
  }, "Organizer Email"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_event_organizer_email",
    className: "ap-input",
    type: "email",
    value: organizerEmail,
    onChange: function onChange(e) {
      return setOrganizerEmail(e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_banner"
  }, "Event Banner"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_banner",
    className: "ap-input",
    type: "file",
    accept: "image/*",
    onChange: handleBannerChange
  })), /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label"
  }, /*#__PURE__*/_react["default"].createElement("input", {
    type: "checkbox",
    className: "ap-input",
    checked: rsvpEnabled,
    onChange: function onChange(e) {
      return setRsvpEnabled(e.target.checked);
    }
  }), /*#__PURE__*/_react["default"].createElement("span", null, " Enable RSVP")), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label",
    htmlFor: "ap_rsvp_limit"
  }, "RSVP Limit"), /*#__PURE__*/_react["default"].createElement("input", {
    id: "ap_rsvp_limit",
    className: "ap-input",
    type: "number",
    value: rsvpLimit,
    onChange: function onChange(e) {
      return setRsvpLimit(e.target.value);
    }
  })), /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label"
  }, /*#__PURE__*/_react["default"].createElement("input", {
    type: "checkbox",
    className: "ap-input",
    checked: waitlistEnabled,
    onChange: function onChange(e) {
      return setWaitlistEnabled(e.target.checked);
    }
  }), /*#__PURE__*/_react["default"].createElement("span", null, " Enable Waitlist")), /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label"
  }, /*#__PURE__*/_react["default"].createElement("input", {
    type: "checkbox",
    className: "ap-input",
    checked: featured,
    onChange: function onChange(e) {
      return setFeatured(e.target.checked);
    }
  }), /*#__PURE__*/_react["default"].createElement("span", null, " Request Featured")), /*#__PURE__*/_react["default"].createElement("p", null, /*#__PURE__*/_react["default"].createElement("label", {
    className: "ap-form-label"
  }, "Images (max 5)"), [0, 1, 2, 3, 4].map(function (i) {
    return /*#__PURE__*/_react["default"].createElement("input", {
      key: i,
      id: "ap_image_".concat(i + 1),
      className: "ap-input",
      type: "file",
      accept: "image/*",
      onChange: handleImageChange(i)
    });
  }), /*#__PURE__*/_react["default"].createElement("input", {
    type: "hidden",
    name: "image_order",
    ref: orderRef,
    readOnly: true
  })), /*#__PURE__*/_react["default"].createElement("div", {
    className: "flex gap-2 flex-wrap"
  }, order.map(function (idx, i) {
    return previews[idx] ? /*#__PURE__*/_react["default"].createElement("div", {
      key: idx,
      className: "w-24 text-center"
    }, /*#__PURE__*/_react["default"].createElement("img", {
      src: previews[idx],
      alt: "",
      className: "w-24 h-24 object-cover rounded border",
      draggable: true,
      onDragStart: function onDragStart() {
        return handleDragStart(i);
      },
      onDragOver: function onDragOver(e) {
        return e.preventDefault();
      },
      onDrop: function onDrop() {
        return handleDrop(i);
      }
    }), /*#__PURE__*/_react["default"].createElement("progress", {
      className: "ap-upload-progress w-full",
      value: progresses[idx] || 0,
      max: "100"
    })) : null;
  })), /*#__PURE__*/_react["default"].createElement("button", {
    className: "ap-form-button",
    type: "submit",
    disabled: loading
  }, loading ? 'Submitting...' : 'Submit'));
}
