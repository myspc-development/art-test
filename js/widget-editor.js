(function () {
	var createElement = React.createElement;
	var createRoot    = ReactDOM.createRoot;
	function WidgetEditorApp() {
		var _window$APWidgetEdito;
		var _React$useState  = React.useState( [{ id: 1, visible: true }, { id: 2, visible: true }] ),
		_React$useState2     = _slicedToArray( _React$useState, 2 ),
		items                = _React$useState2[0],
		setItems             = _React$useState2[1];
		var _React$useState3 = React.useState( '' ),
		_React$useState4     = _slicedToArray( _React$useState3, 2 ),
		notice               = _React$useState4[0],
		setNotice            = _React$useState4[1];
		var showNotice       = function showNotice(msg) {
			setNotice( msg );
			setTimeout(
			function () {
				return setNotice( '' );
			},
			3000
        );
		};
		var save = function save(nextItems) {
			if ( ! window.wp || ! wp.ajax) {
			  return;
			}
			wp.ajax.send(
			'ap_save_role_layout',
			{
				data: {
					nonce: (_window$APWidgetEdito = window.APWidgetEditor) === null || _window$APWidgetEdito === void 0 ? void 0 : _window$APWidgetEdito.nonce,
					role: window.APWidgetEditor && window.APWidgetEditor.role,
					layout: JSON.stringify( nextItems )
				  }
			}
        ).then(
			function () {
				return showNotice( 'Saved' );
			}
			)["catch"](
			function () {
				return showNotice( 'Failed to save' );
			}
			);
		};
		var toggle = function toggle(id) {
			var updated = items.map(
			function (item) {
				return item.id === id ? Object.assign(
				{},
				item,
				{
				visible: ! item.visible
				}
				) : item;
			}
        );
			setItems( updated );
			save( updated );
		};
		return createElement(
            'div',
            null,
            notice ? createElement(
            'div',
            {
			id: 'ap-widget-notice'
            },
            notice
            ) : null,
            createElement(
            'div',
            {
			id: 'ap-widget-items'
            },
            items.map(
		function (item) {
			return createElement(
                'div',
                {
				key: item.id,
				className: 'ap-widget-item',
				'data-id': item.id
                },
                createElement(
                'span',
                {
				className: 'title'
                },
                'Widget ' + item.id
                ),
                createElement(
                'button',
                {
				type: 'button',
				className: 'toggle',
				onClick: function onClick() {
					return toggle( item.id );
				}
                },
                item.visible ? window.APWidgetEditor && window.APWidgetEditor.hide || 'Hide' : window.APWidgetEditor && window.APWidgetEditor.show || 'Show'
                )
			);
            }
            )
            )
		);
	}
	function _slicedToArray(arr, i) {
		return _arrayWithHoles( arr ) || _iterableToArrayLimit( arr, i ) || _unsupportedIterableToArray( arr, i ) || _nonIterableRest();
	}
	function _arrayWithHoles(arr) {
		if (Array.isArray( arr )) {
			return arr;
		}
	}
	function _iterableToArrayLimit(arr, i) {
		var _i = null == arr ? null : "undefined" != typeof Symbol && arr[Symbol.iterator] || arr["@@iterator"];
		if (null != _i) {
			var _s, _e, _x, _r, _arr = [], _n = ! 0, _d = ! 1;
			try {
				if (_x = (_i = _i.call( arr )).next, 0 === i) {
				  if (Object( _i ) !== _i) {
return;
			  }
				  _n = ! 1;
				} else for (; ! (_n = (_s = _x.call( _i )).done) && (_arr.push( _s.value ), _arr.length !== i); _n = ! 0);
			} catch (err) {
			  _d = ! 0;
			  _e = err;
			} finally {
			  try {
				  if ( ! _n && null != _i.return && (_r = _i.return(), Object( _r ) !== _r)) {
return;
				}
			  } finally {
				if (_d) {
	throw _e;
			  }
			  }
			}
			return _arr;
		}
	}
	function _unsupportedIterableToArray(o, minLen) {
		if ( ! o) {
			return;
		}
		if ("string" == typeof o) {
			return _arrayLikeToArray( o, minLen );
		}
		var n = Object.prototype.toString.call( o ).slice( 8, -1 );
		if ("Object" === n && o.constructor) {
			n = o.constructor.name;
		}
		if ("Map" === n || "Set" === n) {
			return Array.from( o );
		}
		if ("Arguments" === n || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test( n )) {
			return _arrayLikeToArray( o, minLen );
		}
	}
	function _arrayLikeToArray(arr, len) {
		if (null == len || len > arr.length) {
			len = arr.length;
		}
		for (var i = 0, arr2 = new Array( len ); i < len; i++) {
			arr2[i] = arr[i];
		}
		return arr2;
	}
	function _nonIterableRest() {
		throw new TypeError(
			"Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must ha" +
			"ve a [Symbol.iterator]() method."
		);
	}
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			var rootEl = document.getElementById( 'artpulse-widget-editor-root' );
			if ( ! rootEl) {
			return;
			}
			createRoot( rootEl ).render( createElement( WidgetEditorApp ) );
		}
	);
})();
