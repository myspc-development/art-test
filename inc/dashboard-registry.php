<?php
function ap_register_dashboard_widget( array $args ) {
    $defaults = [
        'id'       => '',
        'title'    => '',
        'render'   => '',
        'cap'      => 'read',
    ];
    $args = wp_parse_args( $args, $defaults );

    if ( ! current_user_can( $args['cap'] ) ) {
        return;
    }

    global $wp_meta_boxes;

    $wp_meta_boxes['dashboard']['normal']['core'][ $args['id'] ] = [
        'id'       => $args['id'],
        'title'    => $args['title'],
        'callback' => function () use ( $args ) {
            echo '<div class="ap-card" role="region" aria-labelledby="' . esc_attr( $args['id'] ) . '-title">';
            echo '<h2 id="' . esc_attr( $args['id'] ) . '-title" class="ap-card__title">' . esc_html( $args['title'] ) . '</h2>';
            if ( is_callable( $args['render'] ) ) {
                call_user_func( $args['render'] );
            } else {
                locate_template( $args['render'], true );
            }
            echo '</div>';
        },
    ];
}

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( 'index.php' !== $hook ) {
        return;
    }
    wp_enqueue_style( 'ap-dashboard', plugins_url( '../build/css/widgets.css', __FILE__ ), [], '1.0' );
} );
