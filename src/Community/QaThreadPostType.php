<?php
namespace ArtPulse\Community;

class QaThreadPostType {
    public static function register(): void {
        add_action('init', [self::class, 'register_cpt']);
    }

    public static function register_cpt(): void {
        register_post_type('qa_thread', [
            'label'        => __('Q&A Threads', 'artpulse'),
            'public'       => false,
            'show_ui'      => true,
            'show_in_rest' => true,
            'supports'     => ['title', 'editor'],
            'menu_icon'    => 'dashicons-format-status',
        ]);

        register_post_meta('qa_thread', 'event_id', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'integer',
        ]);
        register_post_meta('qa_thread', 'start_time', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
        register_post_meta('qa_thread', 'end_time', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
        register_post_meta('qa_thread', 'participants', [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'array',
        ]);
    }
}
