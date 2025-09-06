<?php
namespace ArtPulse\Community;

class QaThreadPostType {
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_cpt' ) );
	}

	public static function register_cpt(): void {
		register_post_type(
			'qa_thread',
			array(
				'label'        => __( 'Q&A Threads', 'artpulse' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor' ),
				'menu_icon'    => 'dashicons-format-status',
			)
		);

		register_post_meta(
			'qa_thread',
			'event_id',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'integer',
			)
		);
		register_post_meta(
			'qa_thread',
			'start_time',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
		register_post_meta(
			'qa_thread',
			'end_time',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
				register_post_meta(
					'qa_thread',
					'participants',
					array(
						'show_in_rest' => array(
							'schema' => array(
								'type'  => 'array',
								'items' => array( 'type' => 'integer' ),
							),
						),
						'single'       => true,
						'type'         => 'array',
					)
				);
	}
}
