<?php
/**
 * Template Name: Simple Dashboard
 *
 * Minimal template rendering the fallback dashboard with debug pipeline.
 */

if ( ! is_user_logged_in() ) {
    return;
}

$preview   = isset( $_GET['ap_preview_role'] ) ? sanitize_key( $_GET['ap_preview_role'] ) : null;
$previewing = in_array( $preview, array( 'member', 'artist', 'organization' ), true );
$role    = $previewing ? $preview : ( function_exists( 'ap_get_effective_role' ) ? ap_get_effective_role() : 'member' );
$layouts = get_option( 'artpulse_default_layouts', [] );
if ( is_string( $layouts ) ) {
    $tmp = json_decode( $layouts, true );
    $layouts = is_array( $tmp ) ? $tmp : [];
}

$ids = isset( $layouts[ $role ] ) && is_array( $layouts[ $role ] ) ? $layouts[ $role ] : [];

$found = $missing = $hidden = $renderable = [];

$effective_role = $previewing ? $preview : null;

foreach ( $ids as $raw ) {
    $slug = \ArtPulse\Core\DashboardWidgetRegistry::canon_slug( $raw );
    if ( ! $slug || ! \ArtPulse\Core\DashboardWidgetRegistry::exists( $slug ) ) {
        $missing[] = $slug ?: $raw;
        continue;
    }
    $found[] = $slug;
    if ( ! \ArtPulse\Dashboard\WidgetVisibilityManager::isVisible( $slug, get_current_user_id(), $effective_role ) ) {
        $hidden[] = $slug;
        continue;
    }
    $renderable[] = $slug;
}

$user_id = get_current_user_id();

if ( empty( $renderable ) ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        $snap = \ArtPulse\Core\DashboardWidgetRegistry::debug_snapshot();
        error_log( 'AP:DASH \xE2\x9B\x94 placeholder ' . wp_json_encode( [
            'role'           => $role,
            'layout_ids'     => $ids,
            'missing'        => $missing,
            'hidden'         => $hidden,
            'registry_count' => $snap['count'] ?? null,
            'registered_ids' => $snap['registered_ids'] ?? [],
            'admin'          => current_user_can( 'manage_options' ),
            'previewing'     => isset( $_GET['ap_preview_role'] ),
        ] ) );
    }
    $renderable = [ 'empty_dashboard' ];
}

echo '<div class="ap-dashboard-fallback" data-role="' . esc_attr( $role ) . '">';
foreach ( $renderable as $id ) {
    ap_render_widget( $id, $user_id );
}
echo '</div>';

