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
	SelectControl     = _wp$components.SelectControl,
	PanelBody         = _wp$components.PanelBody;
var _wp$element       = wp.element,
	useState          = _wp$element.useState,
	useEffect         = _wp$element.useEffect;
var _wp               = wp,
	apiFetch          = _wp.apiFetch;
registerBlockType(
	'artpulse/ajax-filter',
	{
		title: 'AJAX Taxonomy Filter',
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
		edit: function edit(_ref) {
			var attributes = _ref.attributes,
			setAttributes  = _ref.setAttributes;
			var postType   = attributes.postType,
			taxonomy       = attributes.taxonomy;
			var _useState  = useState( [] ),
			_useState2     = _slicedToArray( _useState, 2 ),
			postTypes      = _useState2[0],
			setPostTypes   = _useState2[1];
			var _useState3 = useState( [] ),
			_useState4     = _slicedToArray( _useState3, 2 ),
			taxonomies     = _useState4[0],
			setTaxonomies  = _useState4[1];
			useEffect(
				function () {
					apiFetch(
						{
							path: '/wp/v2/types'
						}
					).then(
						function (types) {
							var filtered = Object.entries( types ).filter(
								function (_ref2) {
									var _ref3 = _slicedToArray( _ref2, 1 ),
									key       = _ref3[0];
									return key.startsWith( 'ead_' );
								}
							);
							setPostTypes(
								filtered.map(
									function (_ref4) {
										var _ref5 = _slicedToArray( _ref4, 1 ),
										key       = _ref5[0];
										return {
											label: key,
											value: key
										};
									}
								)
							);
						}
					);
				},
				[]
			);
			useEffect(
				function () {
					if ( ! postType) {
						setTaxonomies( [] );
						return;
					}
					apiFetch(
						{
							path: "/wp/v2/types/".concat( postType )
						}
					).then(
						function (type) {
							setTaxonomies(
								Object.keys( type.taxonomies || {} ).map(
									function (t) {
										return {
											label: t,
											value: t
										};
									}
								)
							);
						}
					);
				},
				[postType]
			);
			return /*#__PURE__*/React.createElement(
				React.Fragment,
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
							options: postTypes,
							onChange: function onChange(val) {
								return setAttributes(
									{
										postType: val,
										taxonomy: ''
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
							options: taxonomies,
							onChange: function onChange(val) {
								return setAttributes(
									{
										taxonomy: val
									}
								);
							}
						}
					)
				), /*#__PURE__*/
				React.createElement( "p", null, "This block renders a live filter widget on the frontend." )
			);
		},
		save: function save() {
			return null; // Rendered via PHP and frontend JS
		}
	}
);

// Frontend JS — Render filter UI and fetch filtered posts dynamically
document.addEventListener(
	'DOMContentLoaded',
	function () {
		var containers = document.querySelectorAll( '.artpulse-ajax-filter-block' );
		containers.forEach(
			function (container) {
				var postType = container.dataset.postType;
				var taxonomy = container.dataset.taxonomy;
				if ( ! postType || ! taxonomy) {
					return;
				}
				var filterDiv        = document.createElement( 'div' );
				filterDiv.className  = 'ap-ajax-filter-controls';
				var resultsDiv       = document.createElement( 'div' );
				resultsDiv.className = 'ap-ajax-filter-results';
				resultsDiv.setAttribute( 'role', 'status' );
				resultsDiv.setAttribute( 'aria-live', 'polite' );
				container.appendChild( filterDiv );
				container.appendChild( resultsDiv );

				// Fetch taxonomy terms to build checkboxes
				wp.apiFetch(
					{
						path: "/wp/v2/".concat( taxonomy, "?per_page=100" )
					}
				).then(
					function (terms) {
						if ( ! terms.length) {
							resultsDiv.textContent = 'No filter terms available.';
							return;
						}
						terms.forEach(
							function (term) {
								var label               = document.createElement( 'label' );
								label.style.marginRight = '10px';
								var checkbox            = document.createElement( 'input' );
								checkbox.type           = 'checkbox';
								checkbox.value          = term.slug;
								label.appendChild( checkbox );
								label.appendChild( document.createTextNode( ' ' + term.name ) );
								filterDiv.appendChild( label );
								checkbox.addEventListener(
									'change',
									function () {
										return fetchAndRender();
									}
								);
							}
						);
						fetchAndRender();
					}
				);
				function fetchAndRender() {
						var page         = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
						var checkedTerms = Array.from( filterDiv.querySelectorAll( 'input[type=checkbox]:checked' ) ).map(
							function (input) {
								return input.value;
							}
						);
						var path         = "/artpulse/v1/filtered-posts?post_type=".concat( postType, "&taxonomy=" ).concat( taxonomy, "&per_page=5&page=" ).concat( page );
					if (checkedTerms.length) {
							path += "&terms=".concat( checkedTerms.join( ',' ) );
					}
						resultsDiv.innerHTML = '<p>Loading…</p>';
						resultsDiv.setAttribute( 'aria-busy', 'true' );
						wp.apiFetch(
							{
								path: path
							}
						).then(
							function (data) {
								if ( ! data.posts.length) {
									resultsDiv.innerHTML = '<p>No posts found.</p>';
									resultsDiv.removeAttribute( 'aria-busy' );
									return;
								}
								var ul = document.createElement( 'ul' );
								data.posts.forEach(
									function (post) {
										var li        = document.createElement( 'li' );
										var a         = document.createElement( 'a' );
										a.href        = post.link;
										a.textContent = post.title;
										a.target      = '_blank';
										a.rel         = 'noopener noreferrer';
										li.appendChild( a );
										ul.appendChild( li );
									}
								);
								resultsDiv.innerHTML = '';
								resultsDiv.appendChild( ul );
								if (data.totalPages > 1) {
									var paginationDiv       = document.createElement( 'div' );
									paginationDiv.className = 'ap-ajax-filter-pagination';
									if (page > 1) {
										var prevBtn         = document.createElement( 'button' );
										prevBtn.textContent = 'Previous';
										prevBtn.onclick     = function () {
											return fetchAndRender( page - 1 );
										};
										paginationDiv.appendChild( prevBtn );
									}
									var pageInfo         = document.createElement( 'span' );
									pageInfo.textContent = " Page ".concat( page, " of " ).concat( data.totalPages, " " );
									paginationDiv.appendChild( pageInfo );
									if (page < data.totalPages) {
										var nextBtn         = document.createElement( 'button' );
										nextBtn.textContent = 'Next';
										nextBtn.onclick     = function () {
												return fetchAndRender( page + 1 );
										};
										paginationDiv.appendChild( nextBtn );
									}
									resultsDiv.appendChild( paginationDiv );
								}
								resultsDiv.removeAttribute( 'aria-busy' );
							}
						);
				}
			}
		);
	}
);
