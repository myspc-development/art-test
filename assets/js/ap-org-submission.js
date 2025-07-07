var APOrgSubmission = (function (React) {
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
  function _createForOfIteratorHelper(r, e) {
    var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
    if (!t) {
      if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e) {
        t && (r = t);
        var n = 0,
          F = function () {};
        return {
          s: F,
          n: function () {
            return n >= r.length ? {
              done: true
            } : {
              done: false,
              value: r[n++]
            };
          },
          e: function (r) {
            throw r;
          },
          f: F
        };
      }
      throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
    }
    var o,
      a = true,
      u = false;
    return {
      s: function () {
        t = t.call(r);
      },
      n: function () {
        var r = t.next();
        return a = r.done, r;
      },
      e: function (r) {
        u = true, o = r;
      },
      f: function () {
        try {
          a || null == t.return || t.return();
        } finally {
          if (u) throw o;
        }
      }
    };
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
  function _unsupportedIterableToArray(r, a) {
    if (r) {
      if ("string" == typeof r) return _arrayLikeToArray(r, a);
      var t = {}.toString.call(r).slice(8, -1);
      return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
    }
  }

  var ORG_FIELDS = [{
    name: 'ead_org_description',
    type: 'textarea',
    label: 'Description',
    required: true
  }, {
    name: 'ead_org_website_url',
    type: 'url',
    label: 'Website'
  }, {
    name: 'ead_org_logo_id',
    type: 'media',
    label: 'Logo'
  }, {
    name: 'ead_org_banner_id',
    type: 'media',
    label: 'Banner'
  }, {
    name: 'ead_org_type',
    type: 'select',
    label: 'Organization Type'
  }, {
    name: 'ead_org_size',
    type: 'text',
    label: 'Organization Size'
  }, {
    name: 'ead_org_facebook_url',
    type: 'url',
    label: 'Facebook URL'
  }, {
    name: 'ead_org_twitter_url',
    type: 'url',
    label: 'Twitter URL'
  }, {
    name: 'ead_org_instagram_url',
    type: 'url',
    label: 'Instagram URL'
  }, {
    name: 'ead_org_linkedin_url',
    type: 'url',
    label: 'LinkedIn URL'
  }, {
    name: 'ead_org_artsy_url',
    type: 'url',
    label: 'Artsy URL'
  }, {
    name: 'ead_org_pinterest_url',
    type: 'url',
    label: 'Pinterest URL'
  }, {
    name: 'ead_org_youtube_url',
    type: 'url',
    label: 'YouTube URL'
  }, {
    name: 'ead_org_primary_contact_name',
    type: 'text',
    label: 'Primary Contact Name'
  }, {
    name: 'ead_org_primary_contact_email',
    type: 'email',
    label: 'Primary Contact Email',
    required: true
  }, {
    name: 'ead_org_primary_contact_phone',
    type: 'text',
    label: 'Primary Contact Phone'
  }, {
    name: 'ead_org_primary_contact_role',
    type: 'text',
    label: 'Primary Contact Role'
  }, {
    name: 'ead_org_street_address',
    type: 'text',
    label: 'Street Address'
  }, {
    name: 'ead_org_postal_address',
    type: 'text',
    label: 'Postal Address'
  }, {
    name: 'ead_org_venue_address',
    type: 'text',
    label: 'Venue Address'
  }, {
    name: 'ead_org_venue_email',
    type: 'email',
    label: 'Venue Email'
  }, {
    name: 'ead_org_venue_phone',
    type: 'text',
    label: 'Venue Phone'
  }, {
    name: 'ead_org_monday_start_time',
    type: 'time',
    label: 'Monday Opening Time'
  }, {
    name: 'ead_org_monday_end_time',
    type: 'time',
    label: 'Monday Closing Time'
  }, {
    name: 'ead_org_monday_closed',
    type: 'checkbox',
    label: 'Closed on Monday'
  }, {
    name: 'ead_org_tuesday_start_time',
    type: 'time',
    label: 'Tuesday Opening Time'
  }, {
    name: 'ead_org_tuesday_end_time',
    type: 'time',
    label: 'Tuesday Closing Time'
  }, {
    name: 'ead_org_tuesday_closed',
    type: 'checkbox',
    label: 'Closed on Tuesday'
  }, {
    name: 'ead_org_wednesday_start_time',
    type: 'time',
    label: 'Wednesday Opening Time'
  }, {
    name: 'ead_org_wednesday_end_time',
    type: 'time',
    label: 'Wednesday Closing Time'
  }, {
    name: 'ead_org_wednesday_closed',
    type: 'checkbox',
    label: 'Closed on Wednesday'
  }, {
    name: 'ead_org_thursday_start_time',
    type: 'time',
    label: 'Thursday Opening Time'
  }, {
    name: 'ead_org_thursday_end_time',
    type: 'time',
    label: 'Thursday Closing Time'
  }, {
    name: 'ead_org_thursday_closed',
    type: 'checkbox',
    label: 'Closed on Thursday'
  }, {
    name: 'ead_org_friday_start_time',
    type: 'time',
    label: 'Friday Opening Time'
  }, {
    name: 'ead_org_friday_end_time',
    type: 'time',
    label: 'Friday Closing Time'
  }, {
    name: 'ead_org_friday_closed',
    type: 'checkbox',
    label: 'Closed on Friday'
  }, {
    name: 'ead_org_saturday_start_time',
    type: 'time',
    label: 'Saturday Opening Time'
  }, {
    name: 'ead_org_saturday_end_time',
    type: 'time',
    label: 'Saturday Closing Time'
  }, {
    name: 'ead_org_saturday_closed',
    type: 'checkbox',
    label: 'Closed on Saturday'
  }, {
    name: 'ead_org_sunday_start_time',
    type: 'time',
    label: 'Sunday Opening Time'
  }, {
    name: 'ead_org_sunday_end_time',
    type: 'time',
    label: 'Sunday Closing Time'
  }, {
    name: 'ead_org_sunday_closed',
    type: 'checkbox',
    label: 'Closed on Sunday'
  }];
  var ORG_TYPES = ['gallery', 'museum', 'studio', 'collective', 'non-profit', 'commercial-gallery', 'public-art-space', 'educational-institution', 'other'];
  function OrganizationSubmissionForm() {
    var _useState = React.useState(''),
      _useState2 = _slicedToArray(_useState, 2),
      title = _useState2[0],
      setTitle = _useState2[1];
    var _useState3 = React.useState([]),
      _useState4 = _slicedToArray(_useState3, 2),
      images = _useState4[0],
      setImages = _useState4[1];
    var _useState5 = React.useState(null),
      _useState6 = _slicedToArray(_useState5, 2),
      logo = _useState6[0],
      setLogo = _useState6[1];
    var _useState7 = React.useState(null),
      _useState8 = _slicedToArray(_useState7, 2),
      banner = _useState8[0],
      setBanner = _useState8[1];
    var _useState9 = React.useState(''),
      _useState0 = _slicedToArray(_useState9, 2),
      addressComponents = _useState0[0],
      setAddressComponents = _useState0[1];
    var _useState1 = React.useState(''),
      _useState10 = _slicedToArray(_useState1, 2),
      country = _useState10[0],
      setCountry = _useState10[1];
    var _useState11 = React.useState(''),
      _useState12 = _slicedToArray(_useState11, 2),
      stateProv = _useState12[0],
      setStateProv = _useState12[1];
    var _useState13 = React.useState(''),
      _useState14 = _slicedToArray(_useState13, 2),
      city = _useState14[0],
      setCity = _useState14[1];
    var _useState15 = React.useState([]),
      _useState16 = _slicedToArray(_useState15, 2),
      previews = _useState16[0],
      setPreviews = _useState16[1];
    var _useState17 = React.useState(false),
      _useState18 = _slicedToArray(_useState17, 2),
      loading = _useState18[0],
      setLoading = _useState18[1];
    var _useState19 = React.useState(''),
      _useState20 = _slicedToArray(_useState19, 2),
      message = _useState20[0],
      setMessage = _useState20[1];
    var handleFileChange = function handleFileChange(e) {
      var files = Array.from(e.target.files).slice(0, 5);
      setImages(files);
      setPreviews(files.map(function (file) {
        return URL.createObjectURL(file);
      }));
    };
    var handleLogoChange = function handleLogoChange(e) {
      setLogo(e.target.files[0] || null);
    };
    var handleBannerChange = function handleBannerChange(e) {
      setBanner(e.target.files[0] || null);
    };
    React.useEffect(function () {
      setAddressComponents(JSON.stringify({
        country: country,
        state: stateProv,
        city: city
      }));
    }, [country, stateProv, city]);
    var uploadMedia = /*#__PURE__*/function () {
      var _ref = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee(file) {
        var formData, res, json;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.n) {
            case 0:
              formData = new FormData();
              formData.append('file', file);
              _context.n = 1;
              return fetch(APSubmission.mediaEndpoint, {
                method: 'POST',
                headers: {
                  'X-WP-Nonce': APSubmission.nonce
                },
                body: formData
              });
            case 1:
              res = _context.v;
              _context.n = 2;
              return res.json();
            case 2:
              json = _context.v;
              if (res.ok) {
                _context.n = 3;
                break;
              }
              throw new Error(json.message || 'Upload failed');
            case 3:
              return _context.a(2, json.id);
          }
        }, _callee);
      }));
      return function uploadMedia(_x) {
        return _ref.apply(this, arguments);
      };
    }();
    var handleSubmit = /*#__PURE__*/function () {
      var _ref2 = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee2(e) {
        var imageIds, _iterator, _step, file, id, logoId, bannerId, payload, fd, _iterator2, _step2, _step2$value, key, value, res, json, _t, _t2;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.n) {
            case 0:
              e.preventDefault();
              setLoading(true);
              setMessage('');
              _context2.p = 1;
              imageIds = [];
              _iterator = _createForOfIteratorHelper(images);
              _context2.p = 2;
              _iterator.s();
            case 3:
              if ((_step = _iterator.n()).done) {
                _context2.n = 6;
                break;
              }
              file = _step.value;
              _context2.n = 4;
              return uploadMedia(file);
            case 4:
              id = _context2.v;
              imageIds.push(id);
            case 5:
              _context2.n = 3;
              break;
            case 6:
              _context2.n = 8;
              break;
            case 7:
              _context2.p = 7;
              _t = _context2.v;
              _iterator.e(_t);
            case 8:
              _context2.p = 8;
              _iterator.f();
              return _context2.f(8);
            case 9:
              logoId = null;
              if (!logo) {
                _context2.n = 11;
                break;
              }
              _context2.n = 10;
              return uploadMedia(logo);
            case 10:
              logoId = _context2.v;
            case 11:
              bannerId = null;
              if (!banner) {
                _context2.n = 13;
                break;
              }
              _context2.n = 12;
              return uploadMedia(banner);
            case 12:
              bannerId = _context2.v;
            case 13:
              payload = {
                post_type: 'artpulse_org',
                title: title
              };
              fd = new FormData(e.target);
              fd["delete"]('title');
              fd["delete"]('images[]');
              fd["delete"]('ead_org_logo_id');
              fd["delete"]('ead_org_banner_id');
              _iterator2 = _createForOfIteratorHelper(fd.entries());
              try {
                for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
                  _step2$value = _slicedToArray(_step2.value, 2), key = _step2$value[0], value = _step2$value[1];
                  payload[key] = value;
                }
              } catch (err) {
                _iterator2.e(err);
              } finally {
                _iterator2.f();
              }
              document.querySelectorAll('form input[type="checkbox"]').forEach(function (cb) {
                if (!fd.has(cb.name)) payload[cb.name] = '0';
              });
              payload.image_ids = imageIds;
              if (logoId) payload.ead_org_logo_id = logoId;
              if (bannerId) payload.ead_org_banner_id = bannerId;
              if (addressComponents) payload.address_components = addressComponents;
              _context2.n = 14;
              return fetch(APSubmission.endpoint, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-WP-Nonce': APSubmission.nonce
                },
                body: JSON.stringify(payload)
              });
            case 14:
              res = _context2.v;
              _context2.n = 15;
              return res.json();
            case 15:
              json = _context2.v;
              if (res.ok) {
                _context2.n = 16;
                break;
              }
              throw new Error(json.message || 'Submission failed');
            case 16:
              setMessage('Submission successful!');
              setTimeout(function () {
                window.location.href = APSubmission.dashboardUrl;
              }, 3000);
              setTitle('');
              setImages([]);
              setPreviews([]);
              setLogo(null);
              setBanner(null);
              setCountry('');
              setStateProv('');
              setCity('');
              setAddressComponents('');
              _context2.n = 18;
              break;
            case 17:
              _context2.p = 17;
              _t2 = _context2.v;
              console.error(_t2);
              setMessage("Error: ".concat(_t2.message));
            case 18:
              _context2.p = 18;
              setLoading(false);
              return _context2.f(18);
            case 19:
              return _context2.a(2);
          }
        }, _callee2, null, [[2, 7, 8, 9], [1, 17, 18, 19]]);
      }));
      return function handleSubmit(_x2) {
        return _ref2.apply(this, arguments);
      };
    }();
    return /*#__PURE__*/React.createElement("form", {
      onSubmit: handleSubmit,
      className: "ap-form-container",
      encType: "multipart/form-data",
      "data-no-ajax": "true"
    }, /*#__PURE__*/React.createElement("div", {
      className: "ap-form-messages",
      role: "status",
      "aria-live": "polite"
    }, message), /*#__PURE__*/React.createElement("div", {
      className: "form-group"
    }, /*#__PURE__*/React.createElement("label", {
      className: "ap-form-label",
      htmlFor: "ap_org_title"
    }, "Organization Name"), /*#__PURE__*/React.createElement("input", {
      id: "ap_org_title",
      className: "ap-input",
      type: "text",
      value: title,
      onChange: function onChange(e) {
        return setTitle(e.target.value);
      },
      required: true
    })), /*#__PURE__*/React.createElement("div", {
      className: "form-group"
    }, /*#__PURE__*/React.createElement("label", {
      className: "ap-form-label",
      htmlFor: "ap_org_country"
    }, "Country"), /*#__PURE__*/React.createElement("input", {
      id: "ap_org_country",
      className: "ap-input ap-address-country",
      type: "text",
      value: country,
      onChange: function onChange(e) {
        return setCountry(e.target.value);
      },
      required: true
    })), /*#__PURE__*/React.createElement("div", {
      className: "form-group"
    }, /*#__PURE__*/React.createElement("label", {
      className: "ap-form-label",
      htmlFor: "ap_org_state"
    }, "State/Province"), /*#__PURE__*/React.createElement("input", {
      id: "ap_org_state",
      className: "ap-input ap-address-state",
      type: "text",
      value: stateProv,
      onChange: function onChange(e) {
        return setStateProv(e.target.value);
      }
    })), /*#__PURE__*/React.createElement("div", {
      className: "form-group"
    }, /*#__PURE__*/React.createElement("label", {
      className: "ap-form-label",
      htmlFor: "ap_org_city"
    }, "City"), /*#__PURE__*/React.createElement("input", {
      id: "ap_org_city",
      className: "ap-input ap-address-city",
      type: "text",
      value: city,
      onChange: function onChange(e) {
        return setCity(e.target.value);
      }
    })), ORG_FIELDS.map(function (field) {
      return /*#__PURE__*/React.createElement("div", {
        className: "form-group",
        key: field.name
      }, /*#__PURE__*/React.createElement("label", {
        className: "ap-form-label",
        htmlFor: field.name
      }, field.label), field.type === 'textarea' && /*#__PURE__*/React.createElement("textarea", {
        id: field.name,
        name: field.name,
        className: "ap-input",
        required: field.required
      }), field.type === 'checkbox' && /*#__PURE__*/React.createElement("input", {
        id: field.name,
        className: "ap-input",
        type: "checkbox",
        name: field.name,
        value: "1"
      }), field.type === 'select' && field.name === 'ead_org_type' && /*#__PURE__*/React.createElement("select", {
        id: field.name,
        name: field.name,
        className: "ap-input"
      }, /*#__PURE__*/React.createElement("option", {
        value: ""
      }, "Select"), ORG_TYPES.map(function (t) {
        return /*#__PURE__*/React.createElement("option", {
          key: t,
          value: t
        }, t.replace('-', ' '));
      })), field.type === 'media' && /*#__PURE__*/React.createElement("input", {
        id: field.name,
        className: "ap-input",
        type: "file",
        name: field.name,
        accept: "image/*",
        onChange: field.name === 'ead_org_logo_id' ? handleLogoChange : handleBannerChange
      }), ['textarea', 'checkbox', 'select', 'media'].indexOf(field.type) === -1 && /*#__PURE__*/React.createElement("input", {
        id: field.name,
        className: "ap-input",
        type: field.type,
        name: field.name,
        required: field.required
      }));
    }), /*#__PURE__*/React.createElement("div", {
      className: "form-group"
    }, /*#__PURE__*/React.createElement("label", {
      className: "ap-form-label",
      htmlFor: "ap_org_images"
    }, "Images (max 5)"), /*#__PURE__*/React.createElement("input", {
      id: "ap_org_images",
      className: "ap-input",
      type: "file",
      multiple: true,
      accept: "image/*",
      onChange: handleFileChange
    })), /*#__PURE__*/React.createElement("input", {
      type: "hidden",
      value: addressComponents,
      readOnly: true,
      name: "address_components"
    }), /*#__PURE__*/React.createElement("div", {
      className: "ap-form-group"
    }, previews.map(function (src, i) {
      return /*#__PURE__*/React.createElement("img", {
        key: i,
        src: src,
        alt: "",
        className: "ap-image-preview"
      });
    })), /*#__PURE__*/React.createElement("button", {
      className: "ap-form-button",
      type: "submit",
      disabled: loading
    }, loading ? 'Submitting...' : 'Submit'));
  }

  return OrganizationSubmissionForm;

})(React);
