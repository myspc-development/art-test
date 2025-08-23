<?php
use Yoast\PHPUnitPolyfills\TestCases\TestCase;
use ArtPulse\Core\DocumentStore;
use RuntimeException;

class Documents_Test extends TestCase {
    private DocumentStore $store;

    protected function setUp(): void {
        parent::setUp();
        $this->store = new DocumentStore();
    }

    protected function tearDown(): void {
        $this->store->cleanup();
        parent::tearDown();
    }

    public function test_crud_cycle() {
        // Create
        $this->store->create('doc', 'First');
        $this->assertSame('First', $this->store->read('doc'));

        // Update
        $this->store->update('doc', 'Updated');
        $this->assertSame('Updated', $this->store->read('doc'));

        // Delete
        $this->store->delete('doc');
        $this->expectException(RuntimeException::class);
        $this->store->read('doc');
    }

    public function test_read_nonexistent_document_throws() {
        $this->expectException(RuntimeException::class);
        $this->store->read('missing');
    }

    public function test_update_nonexistent_document_throws() {
        $this->expectException(RuntimeException::class);
        $this->store->update('missing', 'data');
    }

    public function test_delete_nonexistent_document_throws() {
        $this->expectException(RuntimeException::class);
        $this->store->delete('missing');
    }

    public function test_create_existing_document_throws() {
        $this->store->create('doc', 'First');
        $this->expectException(RuntimeException::class);
        $this->store->create('doc', 'Again');
    }
}
