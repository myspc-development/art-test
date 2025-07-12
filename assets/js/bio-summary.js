const { registerBlockType } = wp.blocks;
const { TextareaControl, Button, Spinner } = wp.components;
const { useState } = wp.element;
const { useSelect } = wp.data;
const { apiFetch } = wp;

registerBlockType('artpulse/bio-summary', {
    title: 'Bio Summary',
    icon: 'excerpt-view',
    category: 'widgets',
    attributes: {
        summary: { type: 'string', default: '' }
    },
    edit: ({ attributes, setAttributes }) => {
        const [loading, setLoading] = useState(false);
        const postId = useSelect(select => select('core/editor').getCurrentPostId(), []);
        const generate = () => {
            setLoading(true);
            apiFetch({ path: '/artpulse/v1/bio-summary', method: 'POST', data: { post_id: postId } })
                .then(res => { setAttributes({ summary: res.summary }); setLoading(false); })
                .catch(() => setLoading(false));
        };
        return wp.element.createElement('div', null,
            wp.element.createElement(TextareaControl, {
                label: 'Summary',
                value: attributes.summary,
                onChange: value => setAttributes({ summary: value })
            }),
            wp.element.createElement(Button, { onClick: generate, disabled: loading },
                loading ? wp.element.createElement(Spinner, null) : 'Generate Summary'
            )
        );
    },
    save: ({ attributes }) => {
        return wp.element.createElement('p', null, attributes.summary);
    }
});
