"use strict";

function _typeof(o) {
	"@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
		return typeof o; } : function (o) {
			return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof( o ); }
Object.defineProperty(
	exports,
	"__esModule",
	{
		value: true
	}
);
exports["default"]   = DashboardContainer;
var _react           = _interopRequireWildcard( require( "react" ) );
var _reactGridLayout = require( "react-grid-layout" );
var _index           = _interopRequireDefault( require( "./widgets/index.js" ) );
var _jsxRuntime      = require( "react/jsx-runtime" );
function _interopRequireDefault(e) {
	return e && e.__esModule ? e : { "default" : e }; }
function _interopRequireWildcard(e, t) {
	if ("function" == typeof WeakMap) {
		var r = new WeakMap(), n = new WeakMap();
	} return (_interopRequireWildcard = function _interopRequireWildcard(e, t) {
		if ( ! t && e && e.__esModule) {
			return e;
		} var o, i, f = { __proto__: null, "default": e }; if (null === e || "object" != _typeof( e ) && "function" != typeof e) {
			return f;
		} if (o = t ? n : r) {
			if (o.has( e )) {
				return o.get( e );
			} o.set( e, f ); } for (var _t in e) {
			"default" !== _t && }.hasOwnProperty.call( e, _t ) && ((i = (o = Object.defineProperty) && Object.getOwnPropertyDescriptor( e, _t )) && (i.get || i.set) ? o( f, _t, i ) : f[_t] = e[_t]); return f; })( e, t ); }
function ownKeys(e, r) {
	var t = Object.keys( e ); if (Object.getOwnPropertySymbols) {
		var o = Object.getOwnPropertySymbols( e ); r && (o = o.filter(
			function (r) {
				return Object.getOwnPropertyDescriptor( e, r ).enumerable; }
		)), t.push.apply( t, o ); } return t; }
function _objectSpread(e) {
	for (var r = 1; r < arguments.length; r++) {
		var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys( Object( t ), ! 0 ).forEach(
			function (r) {
				_defineProperty( e, r, t[r] ); }
		) : Object.getOwnPropertyDescriptors ? Object.defineProperties( e, Object.getOwnPropertyDescriptors( t ) ) : ownKeys( Object( t ) ).forEach(
			function (r) {
				Object.defineProperty( e, r, Object.getOwnPropertyDescriptor( t, r ) ); }
		); } return e; }
function _defineProperty(e, r, t) {
	return (r = _toPropertyKey( r )) in e ? Object.defineProperty( e, r, { value : t, enumerable : ! 0, configurable : ! 0, writable : ! 0 } ) : e[r] = t, e; }
function _toPropertyKey(t) {
	var i = _toPrimitive( t, "string" ); return "symbol" == _typeof( i ) ? i : i + ""; }
function _toPrimitive(t, r) {
	if ("object" != _typeof( t ) || ! t) {
		return t;
	} var e = t[Symbol.toPrimitive]; if (void 0 !== e) {
		var i = e.call( t, r || "default" ); if ("object" != _typeof( i )) {
			return i;
		} throw new TypeError( "@@toPrimitive must return a primitive value." ); } return ("string" === r ? String : Number)( t ); }
function _slicedToArray(r, e) {
	return _arrayWithHoles( r ) || _iterableToArrayLimit( r, e ) || _unsupportedIterableToArray( r, e ) || _nonIterableRest(); }
function _nonIterableRest() {
	throw new TypeError( "Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method." ); }
function _unsupportedIterableToArray(r, a) {
	if (r) {
		if ("string" == typeof r) {
			return _arrayLikeToArray( r, a );
		} var t = {}.toString.call( r ).slice( 8, -1 ); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from( r ) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test( t ) ? _arrayLikeToArray( r, a ) : void 0; } }
function _arrayLikeToArray(r, a) {
	(null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array( a ); e < a; e++) {
		n[e] = r[e];
	} return n; }
function _iterableToArrayLimit(r, l) {
	var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) {
		var e, n, i, u, a = [], f = ! 0, o = ! 1; try {
			if (i = (t = t.call( r )).next, 0 === l) {
				if (Object( t ) !== t) {
					return;
				} f = ! 1; } else for (; ! (f = (e = i.call( t )).done) && (a.push( e.value ), a.length !== l); f = ! 0); } catch (r) {
			o = ! 0, n = r; } finally { try {
					if ( ! f && null != t["return"] && (u = t["return"](), Object( u ) !== u)) {
						return;
					} } finally { if (o) {
							throw n;
					} } } return a; } }
function _arrayWithHoles(r) {
	if (Array.isArray( r )) {
		return r;
	} }
var GridLayout = (0, _reactGridLayout.WidthProvider)( _reactGridLayout.Responsive );
function DashboardContainer(_ref) {
	var _window$ArtPulseDashb, _window$ArtPulseDashb2;
	var _ref$role    = _ref.role,
	role             = _ref$role === void 0 ? 'member' : _ref$role;
	var apiRoot      = ((_window$ArtPulseDashb = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb === void 0 ? void 0 : _window$ArtPulseDashb.root) || '/wp-json/';
	var nonce        = window.apNonce || ((_window$ArtPulseDashb2 = window.ArtPulseDashboardApi) === null || _window$ArtPulseDashb2 === void 0 ? void 0 : _window$ArtPulseDashb2.nonce) || '';
	var _useState    = (0, _react.useState)( [] ),
	_useState2       = _slicedToArray( _useState, 2 ),
	layout           = _useState2[0],
	setLayout        = _useState2[1];
	var widgets      = _index["default"].filter(
		function (w) {
			return ! w.roles || w.roles.includes( role );
		}
	);
	var widgetTitles = Object.fromEntries(
		widgets.map(
			function (w) {
				return [w.id, w.title];
			}
		)
	);
	(0, _react.useEffect)(
		function () {
			fetch(
				"".concat( apiRoot, "artpulse/v1/ap_dashboard_layout" ),
				{
					headers: {
						'X-WP-Nonce': nonce
					},
					credentials: 'same-origin'
				}
			).then(
				function (r) {
					return r.json();
				}
			).then(
				function (data) {
					var ids = Array.isArray( data.layout ) ? data.layout : [];
					setLayout(
						ids.map(
							function (id, i) {
								return {
									i: id,
									x: 0,
									y: i,
									w: 4,
									h: 2
								};
							}
						)
					);
				}
			);
		},
		[role]
	);
	var handleLayoutChange = function handleLayoutChange(l) {
		setLayout( l );
		var ids = l.map(
			function (it) {
				return it.i;
			}
		);
		fetch(
			"".concat( apiRoot, "artpulse/v1/ap_dashboard_layout" ),
			{
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce
				},
				credentials: 'same-origin',
				body: JSON.stringify(
					{
						layout: ids
					}
				)
			}
		);
	};
	var widgetMap     = Object.fromEntries(
		widgets.map(
			function (w) {
				return [w.id, w.component];
			}
		)
	);
	var handleKeyDown = function handleKeyDown(e, item) {
		var key     = e.key;
		var changes = null;
		if (e.shiftKey) {
			switch (key) {
				case 'ArrowLeft':
					changes = {
						w: Math.max( 1, item.w - 1 )
					};
					break;
				case 'ArrowRight':
					changes = {
						w: item.w + 1
					};
					break;
				case 'ArrowUp':
					changes = {
						h: Math.max( 1, item.h - 1 )
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
						x: Math.max( 0, item.x - 1 )
					};
				break;
				case 'ArrowRight':
					changes = {
						x: item.x + 1
					};
				break;
				case 'ArrowUp':
					changes = {
						y: Math.max( 0, item.y - 1 )
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
			var updated = layout.map(
				function (it) {
					return it.i === item.i ? _objectSpread( _objectSpread( {}, it ), changes ) : it;
				}
			);
			handleLayoutChange( updated );
		}
	};
	var breakpoints = {
		lg: 1200,
		md: 996,
		sm: 768,
		xs: 480,
		xxs: 0
	};
	var cols        = {
		lg: 12,
		md: 10,
		sm: 6,
		xs: 4,
		xxs: 2
	};
	return /*#__PURE__*/(0, _jsxRuntime.jsx)(
		GridLayout,
		{
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
				return handleLayoutChange( l );
			},
			children: layout.map(
				function (item) {
					var Comp = widgetMap[item.i];
					return /*#__PURE__*/(0, _jsxRuntime.jsx)(
						"div",
						{
							"data-grid": item,
							role: "gridcell",
							tabIndex: 0,
							"aria-label": widgetTitles[item.i],
							onKeyDown: function onKeyDown(e) {
								return handleKeyDown( e, item );
							},
							children: Comp ? /*#__PURE__*/(0, _jsxRuntime.jsx)( Comp, {} ) : /*#__PURE__*/(0, _jsxRuntime.jsx)(
								"div",
								{
									role: "region",
									"aria-label": "Unavailable Widget",
									children: /*#__PURE__*/(0, _jsxRuntime.jsx)(
										"p",
										{
											children: ""
										}
									)
								}
							)
						},
						item.i
					);
				}
			)
		}
	);
}
