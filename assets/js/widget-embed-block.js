const { registerBlockType }      = wp.blocks;
const { useSelect }              = wp.data;
const { SelectControl, Spinner } = wp.components;

registerBlockType(
	'artpulse/widget-embed',
	{
		title: 'Widget Embed',
		icon: 'screenoptions',
		category: 'widgets',
		attributes: {
			widgetId: { type: 'number' }
		},
		edit: ({ attributes, setAttributes }) => {
			const widgets = useSelect(
				select =>
				select( 'core' ).getEntityRecords( 'postType', 'dashboard_widget', { per_page: -1 } )
				,
				[]
			);

		if ( ! widgets) {
			return wp.element.createElement( Spinner, null );
		}

		return wp.element.createElement(
			SelectControl,
			{
				label: 'Select Dashboard Widget',
				value: attributes.widgetId,
				options: [
				{ label: '\u2014 Select \u2014', value: 0 },
				...widgets.map( w => ({ label: w.title.rendered, value: w.id }) )
				],
				onChange: val => setAttributes( { widgetId: parseInt( val, 10 ) } )
			}
		);
		},
		save: () => null,
	}
);
