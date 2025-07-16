<?php

function ap_render_card( $id, $title, $content_callback ) {
    echo '<div class="ap-card" role="region" aria-labelledby="' . esc_attr( $id ) . '-title">';
    echo '<h2 id="' . esc_attr( $id ) . '-title" class="ap-card__title">' . esc_html( $title ) . '</h2>';
    call_user_func( $content_callback );
    echo '</div>';
}

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
            $cb = is_callable( $args['render'] )
                ? $args['render']
                : static function () use ( $args ) { locate_template( $args['render'], true ); };

            ap_render_card( $args['id'], $args['title'], $cb );
        },
    ];
}

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( 'index.php' !== $hook ) {
        return;
    }
    wp_enqueue_style( 'ap-dashboard', plugins_url( '../build/css/dashboard.css', __FILE__ ), [], '1.0' );
} );
