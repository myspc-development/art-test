wp.plugins.registerPlugin(
	'artpulse-sidebar',
	{
		render: () => (
		wp.editor.PluginSidebar(
			{
				name: 'artpulse-sidebar',
				title: 'ArtPulse Tools',
				icon: 'admin-generic',
				children: wp.element.createElement( 'div', {}, 'Useful tools here' )
			}
		)
	)
	}
);
