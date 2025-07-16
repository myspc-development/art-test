<?php

function ap_render_card( $id, $title, $content_callback ) {
    echo '<div class="ap-card" role="region" aria-labelledby="' . esc_attr( $id ) . '-title">';
    echo '<h2 id="' . esc_attr( $id ) . '-title" class="ap-card__title">' . esc_html( $title ) . '</h2>';
    if ( is_callable( $content_callback ) ) {
        call_user_func( $content_callback );
    } else {
        echo '<p><em>' . esc_html__( 'Widget could not be rendered.', 'artpulse' ) . '</em></p>';
    }
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

    static $registered = [];
    if ( isset( $registered[ $args['id'] ] ) ) {
        trigger_error( 'Dashboard widget ID already registered: ' . $args['id'], E_USER_WARNING );
        return;
    }
    foreach ( $registered as $widget ) {
        if ( $widget['title'] === $args['title'] ) {
            trigger_error( 'Dashboard widget title already registered: ' . $args['title'], E_USER_WARNING );
            return;
        }
    }

    global $wp_meta_boxes;

    $wp_meta_boxes['dashboard']['normal']['core'][ $args['id'] ] = [
        'id'       => $args['id'],
        'title'    => $args['title'],
        'callback' => function () use ( $args ) {
            $cb = null;
            if ( is_callable( $args['render'] ) ) {
                $cb = $args['render'];
            } elseif ( $args['render'] ) {
                $cb = static function () use ( $args ) {
                    $path = locate_template( $args['render'], false );
                    if ( $path ) {
                        load_template( $path, true );
                    } else {
                        echo '<p><em>' . esc_html__( 'Widget template not found.', 'artpulse' ) . '</em></p>';
                    }
                };
            }

            ap_render_card( $args['id'], $args['title'], $cb );
        },
    ];

    $registered[ $args['id'] ] = [ 'title' => $args['title'] ];
}

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( 'index.php' !== $hook ) {
        return;
    }
    wp_enqueue_style(
        'ap-dashboard',
        plugins_url( '../assets/css/min/dashboard-widget.css', __FILE__ ),
        [],
        '1.0'
    );
} );
