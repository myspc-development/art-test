<?php
namespace ArtPulse\Admin;

class AdminColumnsEvent
{
    public static function register()
    {
        add_filter( 'manage_artpulse_event_posts_columns',        [ __CLASS__, 'add_columns' ] );
        add_action( 'manage_artpulse_event_posts_custom_column',  [ __CLASS__, 'render_columns' ], 10, 2 );
        add_filter( 'manage_edit-artpulse_event_sortable_columns', [ __CLASS__, 'make_sortable' ] );
        add_action( 'wp_ajax_ap_save_event_gallery_order',        [ __CLASS__, 'ajax_save_gallery_order' ] );
    }

    public static function add_columns( array $columns ): array
    {
        $new = [];
        foreach ( $columns as $key => $label ) {
            if ( 'cb' === $key ) {
                $new['cb']              = $label;
                $new['event_banner']    = __( 'Banner',  'artpulse' );
                $new['event_gallery']   = __( 'Gallery', 'artpulse' );
                $new['event_dates']     = __( 'Dates',   'artpulse' );
                $new['event_venue']     = __( 'Venue',   'artpulse' );
                $new['event_org']       = __( 'Organization', 'artpulse' );
                $new['event_featured']  = __( '⭐ Featured', 'artpulse' );
                $new['open_tasks']     = __( 'Open Tasks', 'artpulse' );
            }
            $new[ $key ] = $label;
        }
        return $new;
    }

    public static function render_columns( string $column, int $post_id )
    {
        switch ( $column ) {
            case 'event_banner':
                $id = get_post_meta( $post_id, 'event_banner_id', true );
                if ( $id ) {
                    echo wp_get_attachment_image( (int)$id, [60,60] );
                } else {
                    echo '&mdash;';
                }
                break;

            case 'event_gallery':
                $ids = get_post_meta( $post_id, '_ap_submission_images', true );
                if ( is_array( $ids ) && $ids ) {
                    $ids = array_slice( $ids, 0, 5 );
                    echo '<ul class="ap-event-gallery-sortable" data-post-id="' . intval( $post_id ) . '">';
                    foreach ( $ids as $img_id ) {
                        $link = get_edit_post_link( $img_id );
                        echo '<li data-id="' . intval( $img_id ) . '">';
                        if ( $link ) {
                            echo '<a href="' . esc_url( $link ) . '">';
                        }
                        echo wp_get_attachment_image( $img_id, [40,40] );
                        if ( $link ) {
                            echo '</a>';
                        }
                        echo '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '&mdash;';
                }
                break;

            case 'event_dates':
                $start = get_post_meta( $post_id, 'event_start_date', true );
                $end   = get_post_meta( $post_id, 'event_end_date',   true );
                echo esc_html( $start );
                if ( $end && $end !== $start ) {
                    echo ' – ' . esc_html( $end );
                }
                break;

            case 'event_venue':
                $venue = get_post_meta( $post_id, 'venue_name', true );
                echo esc_html( $venue ?: '—' );
                break;

            case 'event_org':
                $org_id = get_post_meta( $post_id, '_ap_event_organization', true );
                if ( $org_id ) {
                    $title = get_the_title( $org_id );
                    if ( $title ) {
                        $link = get_edit_post_link( $org_id );
                        echo $link ? '<a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a>' : esc_html( $title );
                    } else {
                        echo esc_html( $org_id );
                    }
                } else {
                    echo '&mdash;';
                }
                break;

            case 'event_featured':
                $flag = get_post_meta( $post_id, 'event_featured', true );
                echo '1' === $flag ? '⭐' : '&mdash;';
                break;
            case 'open_tasks':
                if ( class_exists('\\ArtPulse\\Admin\\EventNotesTasks') ) {
                    $tasks = \ArtPulse\Admin\EventNotesTasks::get_open_count( $post_id );
                    echo intval( $tasks );
                } else {
                    echo '0';
                }
                break;
        }
    }

    public static function make_sortable( array $columns ): array
    {
        $columns['event_featured'] = 'event_featured';
        $columns['event_org']      = '_ap_event_organization';
        $columns['event_dates']    = 'event_start_date';
        return $columns;
    }

    public static function ajax_save_gallery_order(): void
    {
        check_ajax_referer( 'ap_event_gallery_nonce', 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        $order   = isset( $_POST['order'] ) ? array_map( 'intval', (array) $_POST['order'] ) : [];

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied.' ] );
        }

        $existing = get_post_meta( $post_id, '_ap_submission_images', true );
        if ( ! is_array( $existing ) ) {
            $existing = [];
        }

        $new = array_values( array_unique( array_intersect( $order, $existing ) ) );
        foreach ( $existing as $id ) {
            if ( ! in_array( $id, $new, true ) ) {
                $new[] = $id;
            }
        }

        update_post_meta( $post_id, '_ap_submission_images', $new );

        wp_send_json_success();
    }
}

AdminColumnsEvent::register();
