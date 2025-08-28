<?php
use function ArtPulse\Tests\safe_unlink;

class Document_Upload_Test extends WP_UnitTestCase {
	protected $attachment_id;
	protected $filename;

	protected function tearDown(): void {
		if ( $this->attachment_id ) {
			wp_delete_attachment( $this->attachment_id, true );
		}

		if ( $this->filename ) {
			safe_unlink( $this->filename );
		}

		parent::tearDown();
	}

	public function test_wp_insert_attachment_creates_attachment() {
		$upload_dir     = wp_upload_dir();
		$this->filename = $upload_dir['path'] . '/test-image.png';

		$image = imagecreatetruecolor( 1, 1 );
		$black = imagecolorallocate( $image, 0, 0, 0 );
		imagesetpixel( $image, 0, 0, $black );
		imagepng( $image, $this->filename );
		imagedestroy( $image );

		$filetype   = wp_check_filetype( basename( $this->filename ), null );
		$attachment = array(
			'post_mime_type' => $filetype['type'] ?: 'image/png',
			'post_title'     => 'Unit Test Document',
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$this->attachment_id = wp_insert_attachment( $attachment, $this->filename );
		$this->assertIsInt( $this->attachment_id );
		$this->assertGreaterThan( 0, $this->attachment_id );

		$metadata = wp_generate_attachment_metadata( $this->attachment_id, $this->filename );
		wp_update_attachment_metadata( $this->attachment_id, $metadata );

		$post = get_post( $this->attachment_id );
		$this->assertSame( 'Unit Test Document', $post->post_title );
		$this->assertSame( $filetype['type'], $post->post_mime_type );

		$stored_metadata = wp_get_attachment_metadata( $this->attachment_id );
		$this->assertIsArray( $stored_metadata );
		$this->assertSame( 1, $stored_metadata['width'] );
		$this->assertSame( 1, $stored_metadata['height'] );
	}

	public function test_wp_insert_attachment_with_invalid_file() {
		$attachment = array(
			'post_mime_type' => 'image/png',
			'post_title'     => 'Invalid File',
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$result = wp_insert_attachment( $attachment, '/path/does/not/exist.png', 0, true );
		$this->assertWPError( $result );
	}
}
