"use strict";

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
	TextControl       = _wp$components.TextControl,
	PanelBody         = _wp$components.PanelBody,
	SelectControl     = _wp$components.SelectControl,
	RangeControl      = _wp$components.RangeControl;
var InspectorControls = wp.blockEditor.InspectorControls;
var _wp$element       = wp.element,
	useState          = _wp$element.useState,
	useEffect         = _wp$element.useEffect;
var _wp               = wp,
	apiFetch          = _wp.apiFetch;
var postTypeOptions   = [{
	label: 'Artist',
	value: 'ead_artist'
}, {
	label: 'Artwork',
	value: 'ead_artwork'
}, {
	label: 'Event',
	value: 'ead_event'
}, {
	label: 'Organization',
	value: 'ead_organization'
}];
registerBlockType(
	'artpulse/filtered-list-shortcode',
	{
		title: 'Filtered List (Shortcode)',
		icon: 'list-view',
		category: 'widgets',
		attributes: {
			postType: {
				type: 'string',
				"default": 'ead_artist'
			},
			taxonomy: {
				type: 'string',
				"default": 'artist_specialty'
			},
			terms: {
				type: 'string',
				"default": ''
			},
			postsPerPage: {
				type: 'number',
				"default": 5
			}
		},
		edit: function edit(props) {
			var attributes         = props.attributes,
			setAttributes          = props.setAttributes;
			var postType           = attributes.postType,
			taxonomy               = attributes.taxonomy,
			terms                  = attributes.terms,
			postsPerPage           = attributes.postsPerPage;
			var _useState          = useState( [] ),
			_useState2             = _slicedToArray( _useState, 2 ),
			availableTaxonomies    = _useState2[0],
			setAvailableTaxonomies = _useState2[1];
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
						}
					);
				},
				[postType]
			);
			return /*#__PURE__*/React.createElement(
				React.Fragment,
				null, /*#__PURE__*/
				React.createElement(
					InspectorControls,
					null, /*#__PURE__*/
					React.createElement(
						PanelBody,
						{
							title: "Settings",
							initialOpen: true
						}, /*#__PURE__*/
						React.createElement(
							SelectControl,
							{
								label: "Post Type",
								value: postType,
								options: postTypeOptions,
								onChange: function onChange(val) {
									return setAttributes(
										{
											postType: val,
											taxonomy: '',
											terms: ''
										}
									);
								}
							}
						), /*#__PURE__*/
						React.createElement(
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
										terms: ''
									}
								);
							}
							}
						), /*#__PURE__*/
						React.createElement(
							TextControl,
							{
								label: "Terms (comma separated slugs)",
								value: terms,
								onChange: function onChange(val) {
									return setAttributes(
										{
											terms: val
										}
									);
								}
							}
						), /*#__PURE__*/
						React.createElement(
							RangeControl,
							{
								label: "Number of posts",
								value: postsPerPage,
								onChange: function onChange(val) {
									return setAttributes(
										{
											postsPerPage: val
										}
									);
								},
								min: 1,
								max: 20
							}
						)
					)
				), /*#__PURE__*/
				React.createElement( "p", null, "This block renders a filtered list using the shortcode:", /*#__PURE__*/React.createElement( "br", null ), /*#__PURE__*/React.createElement( "code", null, "[ap_filtered_list post_type=\"", postType, "\" taxonomy=\"", taxonomy, "\" terms=\"", terms, "\" posts_per_page=\"", postsPerPage, "\"]" ) )
			);
		},
		save: function save() {
			return null;
		} // Dynamic block rendered via PHP
	}
);
