const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('artpulse/favorite-portfolio', {
    title: __('Favorite Portfolio', 'artpulse'),
    icon: 'star-filled',
    category: 'widgets',
    attributes: {
        category: { type: 'string' },
        limit: { type: 'number', default: 12 },
        sort: { type: 'string', default: 'date' },
        page: { type: 'number', default: 1 },
    },
    edit: () => wp.element.createElement('p', null, __('Favorite portfolio will render on the front-end.', 'artpulse')),
    save: () => null,
});
