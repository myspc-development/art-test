<?php
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\OrgContext;

function ap_render_org_roles_page() {
    if (!current_user_can('edit_posts')) {
        wp_die(__('Insufficient permissions', 'artpulse'));
    }
    $org_id = OrgContext::get_active_org();
    echo '<div class="wrap"><h1>' . esc_html__('Roles & Permissions', 'artpulse') . '</h1>';
    echo '<div id="ap-org-roles-root" data-org="' . esc_attr($org_id) . '"></div></div>';
}
