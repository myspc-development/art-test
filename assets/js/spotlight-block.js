const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { TextControl, CheckboxControl } = wp.components;

registerBlockType('artpulse/spotlights', {
    title: __('Artist Spotlights', 'artpulse'),
    icon: 'star-filled',
    category: 'widgets',
    attributes: {
        title: { type: 'string' },
        image: { type: 'string' },
        visibleTo: { type: 'array', default: ['member', 'artist'] },
    },
    edit: (props) => {
        const { attributes, setAttributes } = props;
        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(TextControl, {
                label: 'Spotlight Title',
                value: attributes.title,
                onChange: (value) => setAttributes({ title: value })
            }),
            wp.element.createElement(CheckboxControl, {
                label: 'Show to Members',
                checked: (attributes.visibleTo || []).includes('member'),
                onChange: (checked) => {
                    const roles = new Set(attributes.visibleTo || []);
                    checked ? roles.add('member') : roles.delete('member');
                    setAttributes({ visibleTo: Array.from(roles) });
                }
            })
        );
    },
    save: () => null,
});
