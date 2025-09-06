"use strict";

function _toConsumableArray(r) {
	return _arrayWithoutHoles( r ) || _iterableToArray( r ) || _unsupportedIterableToArray( r ) || _nonIterableSpread(); }
function _nonIterableSpread() {
	throw new TypeError( "Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method." ); }
function _iterableToArray(r) {
	if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) {
		return Array.from( r );
	} }
function _arrayWithoutHoles(r) {
	if (Array.isArray( r )) {
		return _arrayLikeToArray( r );
	} }
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
var registerBlockType = wp.blocks.registerBlockType;
var _wp$components    = wp.components,
	SelectControl     = _wp$components.SelectControl,
	Spinner           = _wp$components.Spinner,
	Button            = _wp$components.Button;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$element       = wp.element,
	useState          = _wp$element.useState,
	useEffect         = _wp$element.useEffect;
var _wp               = wp,
	apiFetch          = _wp.apiFetch;
registerBlockType(
	'artpulse/advanced-taxonomy-filter',
	{
		title: 'Advanced Taxonomy Filter',
		icon: 'filter',
		category: 'widgets',
		attributes: {
			postType: {
				type: 'string',
				"default": 'ead_artist'
			},
			taxonomy: {
				type: 'string',
				"default": 'artist_specialty'
			}
		},
		edit: function edit(props) {
			var attributes         = props.attributes,
			setAttributes          = props.setAttributes;
			var postType           = attributes.postType,
			taxonomy               = attributes.taxonomy;
			var _useState          = useState( [] ),
			_useState2             = _slicedToArray( _useState, 2 ),
			availablePostTypes     = _useState2[0],
			setAvailablePostTypes  = _useState2[1];
			var _useState3         = useState( [] ),
			_useState4             = _slicedToArray( _useState3, 2 ),
			availableTaxonomies    = _useState4[0],
			setAvailableTaxonomies = _useState4[1];
			var _useState5         = useState( [] ),
			_useState6             = _slicedToArray( _useState5, 2 ),
			terms                  = _useState6[0],
			setTerms               = _useState6[1];
			var _useState7         = useState( '' ),
			_useState8             = _slicedToArray( _useState7, 2 ),
			selectedTerm           = _useState8[0],
			setSelectedTerm        = _useState8[1];
			var _useState9         = useState( [] ),
			_useState0             = _slicedToArray( _useState9, 2 ),
			posts                  = _useState0[0],
			setPosts               = _useState0[1];
			var _useState1         = useState( 1 ),
			_useState10            = _slicedToArray( _useState1, 2 ),
			page                   = _useState10[0],
			setPage                = _useState10[1];
			var _useState11        = useState( 1 ),
			_useState12            = _slicedToArray( _useState11, 2 ),
			totalPages             = _useState12[0],
			setTotalPages          = _useState12[1];
			var _useState13        = useState( false ),
			_useState14            = _slicedToArray( _useState13, 2 ),
			loading                = _useState14[0],
			setLoading             = _useState14[1];
			useEffect(
				function () {
					apiFetch(
						{
							path: '/wp/v2/types'
						}
					).then(
						function (types) {
							var cpts = Object.entries( types ).filter(
								function (_ref) {
									var _ref2 = _slicedToArray( _ref, 2 ),
									key       = _ref2[0],
									val       = _ref2[1];
									return val.rest_base && key.startsWith( 'ead_' );
								}
							).map(
								function (_ref3) {
									var _ref4 = _slicedToArray( _ref3, 1 ),
									key       = _ref4[0];
									return key;
								}
							);
							setAvailablePostTypes( cpts );
						}
					);
				},
				[]
			);
			useEffect(
				function () {
					if ( ! postType) {
						setAvailableTaxonomies( [] );
						return;
					}
					apiFetch(
						{
							path: "/wp/v2/types/".concat( postType )
						}
					).then(
						function (type) {
							setAvailableTaxonomies( Object.keys( type.taxonomies || {} ) );
							setSelectedTerm( '' );
						}
					);
				},
				[postType]
			);
			useEffect(
				function () {
					if ( ! taxonomy) {
						setTerms( [] );
						setSelectedTerm( '' );
						return;
					}
					apiFetch(
						{
							path: "/wp/v2/".concat( taxonomy, "?per_page=100" )
						}
					).then( setTerms );
				},
				[taxonomy]
			);
			var fetchPosts = function fetchPosts() {
				var pageNum = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
				setLoading( true );
				var path = "/wp/v2/".concat( postType, "?per_page=5&page=" ).concat( pageNum );
				if (selectedTerm) {
						path += "&tax_".concat( taxonomy, "=" ).concat( selectedTerm );
				}
				apiFetch(
					{
						path: path
					}
				).then(
					function (data, res) {
						setPosts( data );
						setTotalPages( parseInt( res.headers.get( 'X-WP-TotalPages' ), 10 ) || 1 );
						setPage( pageNum );
						setLoading( false );
					}
				)["catch"](
					function () {
						setPosts( [] );
						setLoading( false );
					}
				);
			};
			useEffect(
				function () {
					fetchPosts();
				},
				[postType, taxonomy, selectedTerm]
			);
			return /*#__PURE__*/React.createElement(
				React.Fragment,
				null, /*#__PURE__*/
				React.createElement(
					InspectorControls,
					null, /*#__PURE__*/
					React.createElement(
						SelectControl,
						{
							label: "Post Type",
							value: postType,
							options: availablePostTypes.map(
								function (pt) {
									return {
										label: pt,
										value: pt
									};
								}
							),
						onChange: function onChange(val) {
							return setAttributes(
								{
									postType: val,
									taxonomy: '',
									selectedTerm: ''
								}
							);
						}
						}
					),
					availableTaxonomies.length > 0 && /*#__PURE__*/React.createElement(
						SelectControl,
						{
							label: "Taxonomy",
							value: taxonomy,
							options: availableTaxonomies.map(
								function (t) {
									return {
										label: t,
										value: t
									};
								}
							),
						onChange: function onChange(val) {
							return setAttributes(
								{
									taxonomy: val,
									selectedTerm: ''
								}
							);
						}
						}
					),
					terms.length > 0 && /*#__PURE__*/React.createElement(
						SelectControl,
						{
							label: "Filter by Term",
							value: selectedTerm,
							options: [{
								label: 'All',
								value: ''
							}].concat(
								_toConsumableArray(
									terms.map(
										function (term) {
											return {
												label: term.name,
												value: term.slug
											};
										}
									)
								)
							),
						onChange: function onChange(val) {
							return setSelectedTerm( val );
						}
						}
					)
				),
				loading ? /*#__PURE__*/React.createElement( Spinner, null ) : /*#__PURE__*/React.createElement(
					"div",
					null, /*#__PURE__*/
					React.createElement(
						"ul",
						null,
						posts.map(
							function (post) {
										return /*#__PURE__*/React.createElement(
											"li",
											{
												key: post.id
											}, /*#__PURE__*/
											React.createElement(
												"a",
												{
													href: post.link,
													target: "_blank",
													rel: "noopener noreferrer"
												},
												post.title.rendered || '(No title)'
											)
										);
							}
						)
					), /*#__PURE__*/
					React.createElement(
						"div",
						null, /*#__PURE__*/
						React.createElement(
							Button,
							{
								isDisabled: page <= 1,
								onClick: function onClick() {
									return fetchPosts( page - 1 );
								}
							},
							"Prev"
						), /*#__PURE__*/
						React.createElement( "span", null, " Page ", page, " of ", totalPages, " " ), /*#__PURE__*/
						React.createElement(
							Button,
							{
								isDisabled: page >= totalPages,
								onClick: function onClick() {
									return fetchPosts( page + 1 );
								}
							},
							"Next"
						)
					)
				)
			);
		},
		save: function save() {
			return null;
		}
	}
);
