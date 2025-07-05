const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('artpulse/spotlights', {
    title: __('Artist Spotlights', 'artpulse'),
    icon: 'star-filled',
    category: 'widgets',
    edit: () => wp.element.createElement('p', {}, __('Spotlights will display on the front end.', 'artpulse')),
    save: () => null,
});
