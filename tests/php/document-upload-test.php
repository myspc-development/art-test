<?php
class Document_Upload_Test extends WP_UnitTestCase {
    public function test_wp_insert_attachment_creates_attachment() {
        $upload_dir = wp_upload_dir();
        $filename   = tempnam( $upload_dir['path'], 'doc' );
        file_put_contents( $filename, 'sample' );

        $filetype = wp_check_filetype( basename( $filename ), null );
        $attachment = [
            'post_mime_type' => $filetype['type'] ?: 'text/plain',
            'post_title'     => 'Unit Test Document',
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attach_id = wp_insert_attachment( $attachment, $filename );
        $this->assertIsInt( $attach_id );
        $this->assertGreaterThan( 0, $attach_id );

        // Clean up.
        wp_delete_attachment( $attach_id, true );
        unlink( $filename );
    }
}
