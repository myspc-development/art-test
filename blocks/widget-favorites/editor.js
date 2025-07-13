import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

registerBlockType('artpulse/widget-favorites', {
    edit: () => {
        return <p>{ __('Favorites widget will render on the front end.', 'artpulse') }</p>;
    },
});
