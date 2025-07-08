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
  function _arrayWithoutHoles(r) {
    if (Array.isArray(r)) return _arrayLikeToArray(r);
  }
  function _defineProperty(e, r, t) {
    return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: true,
      configurable: true,
      writable: true
    }) : e[r] = t, e;
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
        if (i = (t = t.call(r)).next, 0 === l) {
          if (Object(t) !== t) return;
          f = !1;
        } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0);
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
        _defineProperty(e, r, t[r]);
      }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) {
        Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
      });
    }
    return e;
  }
  function _slicedToArray(r, e) {
    return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest();
  }
  function _toConsumableArray(r) {
    return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread();
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

  if (!window.APDashboardWidgetsEditor || !window.APDashboardWidgetsEditor.config) {
    console.error("APDashboardWidgetsEditor.config is missing; initializing empty layout.");
    window.APDashboardWidgetsEditor = _objectSpread2(_objectSpread2({}, window.APDashboardWidgetsEditor), {}, {
      config: {}
    });
  }
  if (!window.APDashboardWidgetsEditor || !Array.isArray(window.APDashboardWidgetsEditor.widgets)) {
    console.error("APDashboardWidgetsEditor.widgets is missing; using empty list.");
    window.APDashboardWidgetsEditor = _objectSpread2(_objectSpread2({}, window.APDashboardWidgetsEditor), {}, {
      widgets: []
    });
  }
  function WidgetSettingsForm(_ref) {
    var id = _ref.id,
      onClose = _ref.onClose,
      _ref$l10n = _ref.l10n,
      l10n = _ref$l10n === void 0 ? {} : _ref$l10n;
    var _useState = React.useState([]),
      _useState2 = _slicedToArray(_useState, 2),
      schema = _useState2[0],
      setSchema = _useState2[1];
    var _useState3 = React.useState({}),
      _useState4 = _slicedToArray(_useState3, 2),
      values = _useState4[0],
      setValues = _useState4[1];
    var _useState5 = React.useState(false),
      _useState6 = _slicedToArray(_useState5, 2),
      error = _useState6[0],
      setError = _useState6[1];
    var restRoot = window.wpApiSettings && window.wpApiSettings.root || '';
    var restNonce = window.wpApiSettings && window.wpApiSettings.nonce || '';
    React.useEffect(function () {
      if (!id) return;
      setError(false);
      fetch("".concat(restRoot, "artpulse/v1/widget-settings/").concat(id), {
        headers: {
          'X-WP-Nonce': restNonce
        }
      }).then(function (r) {
        if (!r.ok) throw new Error('Request failed');
        return r.json();
      }).then(function (data) {
        setSchema(data.schema || []);
        setValues(data.settings || {});
      })["catch"](function () {
        return setError(true);
      });
    }, [id]);
    function updateField(key, val) {
      setValues(function (v) {
        return _objectSpread2(_objectSpread2({}, v), {}, _defineProperty({}, key, val));
      });
    }
    function handleSubmit(e) {
      e.preventDefault();
      fetch("".concat(restRoot, "artpulse/v1/widget-settings/").concat(id), {
        method: 'POST',
        headers: {
          'X-WP-Nonce': restNonce,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          settings: values
        })
      }).then(function () {
        var _window$wp, _window$wp$data;
        if ((_window$wp = window.wp) !== null && _window$wp !== void 0 && (_window$wp$data = _window$wp.data) !== null && _window$wp$data !== void 0 && _window$wp$data.dispatch) {
          wp.data.dispatch('core/notices').createNotice('success', l10n.saveSuccess || 'Saved', {
            isDismissible: true
          });
        }
        onClose();
      })["catch"](function () {
        var _window$wp2, _window$wp2$data;
        if ((_window$wp2 = window.wp) !== null && _window$wp2 !== void 0 && (_window$wp2$data = _window$wp2.data) !== null && _window$wp2$data !== void 0 && _window$wp2$data.dispatch) {
          wp.data.dispatch('core/notices').createNotice('error', l10n.saveError || 'Error', {
            isDismissible: true
          });
        }
      });
    }
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-org-modal open",
      id: "ap-widget-settings-modal"
    }, /*#__PURE__*/React.createElement("div", {
      id: "ap-widget-settings-content"
    }, /*#__PURE__*/React.createElement("button", {
      type: "button",
      className: "ap-form-button",
      onClick: onClose
    }, l10n.close || 'Close'), error && /*#__PURE__*/React.createElement("p", null, "Unable to load widget settings."), /*#__PURE__*/React.createElement("form", {
      onSubmit: handleSubmit
    }, schema.map(function (field) {
      var _values$field$key;
      if (!field.key) return null;
      var val = (_values$field$key = values[field.key]) !== null && _values$field$key !== void 0 ? _values$field$key : field.type === 'checkbox' ? false : '';
      if (field.type === 'checkbox') {
        return /*#__PURE__*/React.createElement("label", {
          key: field.key,
          className: "ap-form-label"
        }, /*#__PURE__*/React.createElement("input", {
          type: "checkbox",
          checked: !!val,
          onChange: function onChange(e) {
            return updateField(field.key, e.target.checked);
          }
        }), field.label || field.key);
      }
      return /*#__PURE__*/React.createElement("label", {
        key: field.key,
        className: "ap-form-label"
      }, field.label || field.key, /*#__PURE__*/React.createElement("input", {
        type: field.type || 'text',
        value: val,
        onChange: function onChange(e) {
          return updateField(field.key, e.target.value);
        }
      }));
    }), /*#__PURE__*/React.createElement("button", {
      type: "submit",
      className: "ap-form-button"
    }, l10n.save || 'Save'))));
  }
  function WidgetsEditor(_ref2) {
    var widgets = _ref2.widgets,
      config = _ref2.config,
      roles = _ref2.roles,
      nonce = _ref2.nonce,
      ajaxUrl = _ref2.ajaxUrl,
      _ref2$l10n = _ref2.l10n,
      l10n = _ref2$l10n === void 0 ? {} : _ref2$l10n;
    var roleKeys = Object.keys(roles);
    var _useState7 = React.useState(roleKeys[0] || ''),
      _useState8 = _slicedToArray(_useState7, 2),
      activeRole = _useState8[0],
      setActiveRole = _useState8[1];
    var _useState9 = React.useState([]),
      _useState0 = _slicedToArray(_useState9, 2),
      active = _useState0[0],
      setActive = _useState0[1];
    var _useState1 = React.useState([]),
      _useState10 = _slicedToArray(_useState1, 2),
      available = _useState10[0],
      setAvailable = _useState10[1];
    var _useState11 = React.useState(false),
      _useState12 = _slicedToArray(_useState11, 2),
      showPreview = _useState12[0],
      setShowPreview = _useState12[1];
    var _useState13 = React.useState(null),
      _useState14 = _slicedToArray(_useState13, 2),
      selectedWidget = _useState14[0],
      setSelectedWidget = _useState14[1];
    var _useState15 = React.useState(function () {
        return JSON.parse(JSON.stringify(config));
      }),
      _useState16 = _slicedToArray(_useState15, 1),
      defaults = _useState16[0];
    var activeRef = React.useRef(null);
    var availRef = React.useRef(null);
    React.useEffect(function () {
      var activeIds = config[activeRole] || [];
      var activeWidgets = widgets.filter(function (w) {
        return activeIds.includes(w.id);
      });
      var availWidgets = widgets.filter(function (w) {
        return !activeIds.includes(w.id);
      });
      setActive(activeWidgets);
      setAvailable(availWidgets);
    }, [activeRole]);
    React.useEffect(function () {
      if (typeof Sortable === 'undefined') return;
      if (!activeRef.current || !availRef.current) return;
      var opts = {
        group: 'widgets',
        animation: 150,
        onSort: updateFromDom,
        onAdd: updateFromDom,
        onRemove: updateFromDom
      };
      var act = Sortable.create(activeRef.current, opts);
      var avail = Sortable.create(availRef.current, opts);
      return function () {
        act.destroy();
        avail.destroy();
      };
    }, [activeRole]);
    function moveItem(list, from, to) {
      var copy = _toConsumableArray(list);
      var item = copy.splice(from, 1)[0];
      copy.splice(to, 0, item);
      return copy;
    }
    function handleKeyDown(e, index, listName) {
      var isActive = listName === 'active';
      var list = isActive ? active : available;
      if (e.key === 'ArrowUp' && index > 0) {
        var newList = moveItem(list, index, index - 1);
        isActive ? setActive(newList) : setAvailable(newList);
        e.preventDefault();
      } else if (e.key === 'ArrowDown' && index < list.length - 1) {
        var _newList = moveItem(list, index, index + 1);
        isActive ? setActive(_newList) : setAvailable(_newList);
        e.preventDefault();
      } else if (e.key === 'ArrowLeft' && isActive) {
        var item = list[index];
        var newAct = _toConsumableArray(active);
        newAct.splice(index, 1);
        setActive(newAct);
        setAvailable([item].concat(_toConsumableArray(available)));
        e.preventDefault();
      } else if (e.key === 'ArrowRight' && !isActive) {
        var _item = list[index];
        var newAvail = _toConsumableArray(available);
        newAvail.splice(index, 1);
        setAvailable(newAvail);
        setActive([].concat(_toConsumableArray(active), [_item]));
        e.preventDefault();
      }
    }
    function updateFromDom() {
      if (!activeRef.current || !availRef.current) return;
      var idsFrom = function idsFrom(ul) {
        return Array.from(ul.querySelectorAll('li')).map(function (li) {
          return li.dataset.id;
        });
      };
      var actIds = idsFrom(activeRef.current);
      var availIds = idsFrom(availRef.current);
      setActive(actIds.map(function (id) {
        return widgets.find(function (w) {
          return w.id === id;
        });
      }));
      setAvailable(availIds.map(function (id) {
        return widgets.find(function (w) {
          return w.id === id;
        });
      }));
    }
    function handleSave() {
      var form = new FormData();
      form.append('action', 'ap_save_dashboard_widget_config');
      form.append('nonce', nonce);
      active.forEach(function (w) {
        return form.append("config[".concat(activeRole, "][]"), w.id);
      });
      fetch(ajaxUrl, {
        method: 'POST',
        body: form
      }).then(function (r) {
        return r.json();
      }).then(function (res) {
        if (res.success) {
          var _window$wp3, _window$wp3$data;
          if ((_window$wp3 = window.wp) !== null && _window$wp3 !== void 0 && (_window$wp3$data = _window$wp3.data) !== null && _window$wp3$data !== void 0 && _window$wp3$data.dispatch) {
            wp.data.dispatch('core/notices').createNotice('success', l10n.saveSuccess || 'Saved', {
              isDismissible: true
            });
          }
          config[activeRole] = active.map(function (w) {
            return w.id;
          });
        } else {
          var _res$data, _window$wp4, _window$wp4$data;
          var msg = ((_res$data = res.data) === null || _res$data === void 0 ? void 0 : _res$data.message) || l10n.saveError || 'Error';
          if ((_window$wp4 = window.wp) !== null && _window$wp4 !== void 0 && (_window$wp4$data = _window$wp4.data) !== null && _window$wp4$data !== void 0 && _window$wp4$data.dispatch) {
            wp.data.dispatch('core/notices').createNotice('error', msg, {
              isDismissible: true
            });
          }
        }
      })["catch"](function () {
        var _window$wp5, _window$wp5$data;
        if ((_window$wp5 = window.wp) !== null && _window$wp5 !== void 0 && (_window$wp5$data = _window$wp5.data) !== null && _window$wp5$data !== void 0 && _window$wp5$data.dispatch) {
          wp.data.dispatch('core/notices').createNotice('error', l10n.saveError || 'Error', {
            isDismissible: true
          });
        }
      });
    }
    function handleReset() {
      var activeIds = defaults[activeRole] || [];
      setActive(widgets.filter(function (w) {
        return activeIds.includes(w.id);
      }));
      setAvailable(widgets.filter(function (w) {
        return !activeIds.includes(w.id);
      }));
    }
    return /*#__PURE__*/React.createElement("div", {
      className: "ap-widgets-editor"
    }, /*#__PURE__*/React.createElement("label", {
      className: "screen-reader-text",
      htmlFor: "ap-role-select"
    }, l10n.selectRole || 'Select Role'), /*#__PURE__*/React.createElement("select", {
      id: "ap-role-select",
      value: activeRole,
      onChange: function onChange(e) {
        return setActiveRole(e.target.value);
      }
    }, roleKeys.map(function (r) {
      return /*#__PURE__*/React.createElement("option", {
        key: r,
        value: r
      }, roles[r].name || r);
    })), /*#__PURE__*/React.createElement("p", {
      className: "ap-widgets-help"
    }, l10n.instructions), /*#__PURE__*/React.createElement("div", {
      className: "ap-widgets-columns"
    }, /*#__PURE__*/React.createElement("div", {
      className: "ap-widgets-available"
    }, /*#__PURE__*/React.createElement("h4", {
      id: "ap-available-label"
    }, l10n.availableWidgets || 'Available Widgets'), /*#__PURE__*/React.createElement("ul", {
      ref: availRef,
      role: "listbox",
      "aria-labelledby": "ap-available-label"
    }, available.map(function (w, i) {
      return /*#__PURE__*/React.createElement("li", {
        key: w.id,
        "data-id": w.id,
        tabIndex: 0,
        role: "option",
        onClick: function onClick() {
          return setSelectedWidget(w.id);
        },
        onKeyDown: function onKeyDown(e) {
          return handleKeyDown(e, i, 'available');
        }
      }, w.name);
    }))), /*#__PURE__*/React.createElement("div", {
      className: "ap-widgets-active"
    }, /*#__PURE__*/React.createElement("h4", {
      id: "ap-active-label"
    }, l10n.activeWidgets || 'Active Widgets'), /*#__PURE__*/React.createElement("ul", {
      ref: activeRef,
      role: "listbox",
      "aria-labelledby": "ap-active-label"
    }, active.map(function (w, i) {
      return /*#__PURE__*/React.createElement("li", {
        key: w.id,
        "data-id": w.id,
        tabIndex: 0,
        role: "option",
        onClick: function onClick() {
          return setSelectedWidget(w.id);
        },
        onKeyDown: function onKeyDown(e) {
          return handleKeyDown(e, i, 'active');
        }
      }, w.name);
    })))), /*#__PURE__*/React.createElement("div", {
      className: "ap-widgets-actions"
    }, /*#__PURE__*/React.createElement("button", {
      className: "ap-form-button",
      onClick: handleSave
    }, l10n.save || 'Save'), /*#__PURE__*/React.createElement("button", {
      className: "ap-form-button",
      onClick: function onClick() {
        return setShowPreview(!showPreview);
      }
    }, l10n.preview || 'Preview'), /*#__PURE__*/React.createElement("button", {
      className: "ap-form-button",
      onClick: handleReset
    }, l10n.resetDefault || 'Reset to Default')), showPreview && /*#__PURE__*/React.createElement("div", {
      className: "ap-widgets-preview"
    }, /*#__PURE__*/React.createElement("h4", null, l10n.preview || 'Preview'), /*#__PURE__*/React.createElement("ol", null, active.map(function (w) {
      return /*#__PURE__*/React.createElement("li", {
        key: w.id
      }, w.name);
    }))), selectedWidget && /*#__PURE__*/React.createElement(WidgetSettingsForm, {
      id: selectedWidget,
      onClose: function onClose() {
        return setSelectedWidget(null);
      },
      l10n: l10n
    }));
  }
  document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('ap-dashboard-widgets-canvas');
    if (container && window.APDashboardWidgetsEditor) {
      client.createRoot(container).render(/*#__PURE__*/React.createElement(WidgetsEditor, APDashboardWidgetsEditor));
      console.log('Editor loaded');
    } else {
      console.error('WidgetsEditor failed: container or data missing');
    }
  });

})(React, ReactDOM);
