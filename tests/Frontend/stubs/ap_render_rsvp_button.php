<?php
if ( ! function_exists( 'ap_render_rsvp_button' ) ) {
    function ap_render_rsvp_button( int $event_id ): string {
        return '<button class="ap-rsvp-btn ap-form-button">RSVP</button>';
    }
}
