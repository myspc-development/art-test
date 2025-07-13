wp.plugins.registerPlugin('artpulse-sidebar', {
  render: () => (
    wp.editPost.PluginSidebar({
      name: 'artpulse-sidebar',
      title: 'ArtPulse Tools',
      icon: 'admin-generic',
      children: wp.element.createElement('div', {}, 'Useful tools here')
    })
  )
});
