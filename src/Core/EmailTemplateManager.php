<?php
namespace ArtPulse\Core;

/**
 * Handles simple HTML email templates with placeholders.
 */
class EmailTemplateManager {

	/**
	 * Render HTML template replacing placeholders.
	 *
	 * @param string $content Email body content.
	 * @param array  $data    Additional placeholder data.
	 * @return string
	 */
	public static function render( string $content, array $data = array() ): string {
		$options         = get_option( 'artpulse_settings', array() );
		$template        = $options['default_email_template'] ?? '{{content}}';
		$template        = apply_filters( 'artpulse_email_template_html', $template, $data );
		$data['content'] = $content;
		foreach ( $data as $key => $value ) {
			$template = str_replace( '{{' . $key . '}}', (string) $value, $template );
		}
		return $template;
	}
}
