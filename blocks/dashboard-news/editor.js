import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

registerBlockType('artpulse/dashboard-news', {
    edit: () => {
        return <p>{ __('News widget will render on the front end.', 'artpulse') }</p>;
    },
});
