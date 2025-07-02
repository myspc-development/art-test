<?php
function ap_get_ui_mode() {
    if (isset($_GET['ui_mode'])) {
        return sanitize_text_field($_GET['ui_mode']);
    }
    return get_option('ap_ui_mode', 'salient');
}
