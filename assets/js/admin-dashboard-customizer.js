'use strict';

var _slicedToArray = (function () {
	function sliceIterator(arr, i) {
		var _arr = []; var _n = true; var _d = false; var _e = undefined; try {
			for (var _i = arr[Symbol.iterator](), _s; ! (_n = (_s = _i.next()).done); _n = true) {
				_arr.push( _s.value ); if (i && _arr.length === i) {
					break;
				} } } catch (err) {
			_d = true; _e = err; } finally { try {
					if ( ! _n && _i['return']) {
								_i['return']();
					} } finally { if (_d) {
							throw _e;
					} } } return _arr; } return function (arr, i) {
						if (Array.isArray( arr )) {
							return arr; } else if (Symbol.iterator in Object( arr )) {
							return sliceIterator( arr, i ); } else {
								throw new TypeError( 'Invalid attempt to destructure non-iterable instance' ); } }; })();

function _interopRequireDefault(obj) {
	return obj && obj.__esModule ? obj : { 'default' : obj }; }

function _toConsumableArray(arr) {
	if (Array.isArray( arr )) {
		for (var i = 0, arr2 = Array( arr.length ); i < arr.length; i++) {
			arr2[i] = arr[i];
		} return arr2; } else {
		return Array.from( arr ); } }

var _react = require( 'react' );

var _react2 = _interopRequireDefault( _react );

var _reactDom = require( 'react-dom' );

var _reactDom2 = _interopRequireDefault( _reactDom );

function WidgetConfig(_ref) {
	var widgets    = _ref.widgets;
	var config     = _ref.config;
	var roles      = _ref.roles;
	var nonce      = _ref.nonce;
	var adminNonce = _ref.adminNonce;
	var ajaxUrl    = _ref.ajaxUrl;

	var roleKeys = Object.keys( roles );

	var _useState = (0, _react.useState)( roleKeys[0] || '' );

	var _useState2 = _slicedToArray( _useState, 2 );

	var activeRole    = _useState2[0];
	var setActiveRole = _useState2[1];

	var _useState3 = (0, _react.useState)(
		config[activeRole] || widgets.map(
			function (w) {
				return w.id;
			}
		)
	);

	var _useState32 = _slicedToArray( _useState3, 2 );

	var layout    = _useState32[0];
	var setLayout = _useState32[1];

	function handleSave() {
		var form = new FormData();
		form.append( 'action', 'ap_save_dashboard_widget_config' );
		form.append( 'nonce', nonce );
		if (adminNonce) {
			form.append( '_wpnonce', adminNonce );
		}
		layout.forEach(
			function (id) {
				return form.append( 'config[' + activeRole + '][]', id );
			}
		);
		fetch( ajaxUrl, { method: 'POST', body: form } ).then(
			function (r) {
				return r.json();
			}
		).then(
			function () {
				return alert( 'Saved' );
			}
		);
	}

	function toggle(id) {
		setLayout(
			function (l) {
				return l.includes( id ) ? l.filter(
					function (w) {
						return w !== id;
					}
				) : [].concat( _toConsumableArray( l ), [id] );
			}
		);
	}

	return _react2['default'].createElement(
		'div',
		{ className: 'ap-dashboard-customizer' },
		_react2['default'].createElement(
			'select',
			{ value: activeRole, onChange: function (e) {
				var role = e.target.value;setActiveRole( role );setLayout(
					config[role] || widgets.map(
						function (w) {
							return w.id;
						}
					)
				);
			} },
			roleKeys.map(
				function (r) {
					return _react2['default'].createElement(
						'option',
						{ key: r, value: r },
						roles[r].name || r
					);
				}
			)
		),
		_react2['default'].createElement(
			'ul',
			null,
			widgets.map(
				function (w) {
					return _react2['default'].createElement(
						'li',
						{ key: w.id },
						_react2['default'].createElement(
							'label',
							null,
							_react2['default'].createElement(
								'input',
								{ type: 'checkbox', checked: layout.includes( w.id ), onChange: function () {
									return toggle( w.id );
								} }
							),
							w.name
						)
					);
				}
			)
		),
		_react2['default'].createElement(
			'button',
			{ onClick: handleSave },
			'Save'
		)
	);
}

document.addEventListener(
	'DOMContentLoaded',
	function () {
		var el = document.getElementById( 'ap-dashboard-widgets-admin' );
		if (el && window.APDashboardCustomizer) {
			_reactDom2['default'].render( _react2['default'].createElement( WidgetConfig, APDashboardCustomizer ), el );
		}
	}
);
