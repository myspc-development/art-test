"use strict";

function _typeof(o) {
	"@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
		return typeof o; } : function (o) {
			return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof( o ); }
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
var registerPlugin             = wp.plugins.registerPlugin;
var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
var _wp$components             = wp.components,
	PanelBody                  = _wp$components.PanelBody,
	SelectControl              = _wp$components.SelectControl;
var _wp$data                   = wp.data,
	withSelect                 = _wp$data.withSelect,
	withDispatch               = _wp$data.withDispatch;
var compose                    = wp.compose.compose;
var Fragment                   = wp.element.Fragment;
var taxonomiesConfig           = {
	ead_artist: [{
		slug: 'artist_specialty',
		label: 'Artist Specialties'
	}],
	ead_artwork: [{
		slug: 'artwork_style',
		label: 'Artwork Styles'
	}],
	ead_event: [{
		slug: 'event_type',
		label: 'Event Types'
	}],
	ead_organization: [{
		slug: 'organization_category',
		label: 'Organization Categories'
	}]
};
var SidebarTaxonomies          = function SidebarTaxonomies(props) {
	var postType   = props.postType,
	terms          = props.terms,
	setTerms       = props.setTerms;
	var taxonomies = taxonomiesConfig[postType] || [];
	if (taxonomies.length === 0) {
		return null;
	}
	return /*#__PURE__*/React.createElement(
		PluginDocumentSettingPanel,
		{
			name: "ap-taxonomies",
			title: "Taxonomies"
		},
		taxonomies.map(
			function (_ref) {
				var slug          = _ref.slug,
				label             = _ref.label;
				var selectedTerms = terms[slug] || [];
				return /*#__PURE__*/React.createElement(
					SelectControl,
					{
						key: slug,
						multiple: true,
						label: label,
						value: selectedTerms,
						options: props.allTerms[slug] || [],
						onChange: function onChange(newTerms) {
							return setTerms( slug, newTerms );
						}
					}
				);
			}
		)
	);
};
var SidebarTaxonomiesWithData = compose(
	[withSelect(
		function (select) {
			var postType = select( 'core/editor' ).getCurrentPostType();
			var terms    = {};
			var allTerms = {};
			if ( ! postType) {
				return
				postType: postType,
				terms: terms,
				allTerms: allTerms
			};
			Object.entries( taxonomiesConfig[postType] || [] ).forEach(
				function (_ref2) {
					var _ref3      = _slicedToArray( _ref2, 2 ),
					index          = _ref3[0],
					slug           = _ref3[1].slug;
					terms[slug]    = select( 'core/editor' ).getEditedPostAttribute( 'taxonomies' )[slug] || [];
					allTerms[slug] = select( 'core' ).getEntityRecords(
						'taxonomy',
						slug,
						{
							per_page: -1
						}
					) || [];
				}
			);
			return {
				postType: postType,
				terms: terms,
				allTerms: allTerms
			};
		}
	), withDispatch(
		function (dispatch) {
			return {
				setTerms: function setTerms(taxonomy, values) {
					var _dispatch = dispatch( 'core/editor' ),
					editPost      = _dispatch.editPost;
					editPost(
						{
							taxonomies: _defineProperty( {}, taxonomy, values )
						}
					);
				}
			};
		}
	)]
)( SidebarTaxonomies );
registerPlugin(
	'artpulse-taxonomy-sidebar',
	{
		render: SidebarTaxonomiesWithData
	}
);
