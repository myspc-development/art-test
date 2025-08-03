<?php
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class Documents_Test extends TestCase {
    public function test_crud_cycle() {
        $documents = [];

        // Create
        $documents['doc'] = 'First';
        $this->assertArrayHasKey( 'doc', $documents );

        // Read
        $this->assertSame( 'First', $documents['doc'] );

        // Update
        $documents['doc'] = 'Updated';
        $this->assertSame( 'Updated', $documents['doc'] );

        // Delete
        unset( $documents['doc'] );
        $this->assertArrayNotHasKey( 'doc', $documents );
    }
}
