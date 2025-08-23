<?php
namespace ArtPulse\Core;

use RuntimeException;

/**
 * Simple file-based document handler.
 * Allows creating, reading, updating, and deleting text documents
 * within a dedicated temporary directory.
 */
class DocumentStore
{
    private string $dir;

    public function __construct(?string $dir = null)
    {
        $this->dir = $dir ?: sys_get_temp_dir() . '/artpulse-docs';
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }

    /**
     * Create a new document.
     *
     * @throws RuntimeException If the document already exists.
     */
    public function create(string $name, string $contents): string
    {
        $path = $this->path($name);
        if (file_exists($path)) {
            throw new RuntimeException('Document already exists');
        }
        file_put_contents($path, $contents);
        return $path;
    }

    /**
     * Read an existing document's contents.
     *
     * @throws RuntimeException If the document does not exist.
     */
    public function read(string $name): string
    {
        $path = $this->path($name);
        if (!file_exists($path)) {
            throw new RuntimeException('Document not found');
        }
        return (string) file_get_contents($path);
    }

    /**
     * Update an existing document.
     *
     * @throws RuntimeException If the document does not exist.
     */
    public function update(string $name, string $contents): void
    {
        $path = $this->path($name);
        if (!file_exists($path)) {
            throw new RuntimeException('Document not found');
        }
        file_put_contents($path, $contents);
    }

    /**
     * Delete an existing document.
     *
     * @throws RuntimeException If the document does not exist.
     */
    public function delete(string $name): void
    {
        $path = $this->path($name);
        if (!file_exists($path)) {
            throw new RuntimeException('Document not found');
        }
        unlink($path);
    }

    /**
     * Remove all generated documents and directory.
     */
    public function cleanup(): void
    {
        if (!is_dir($this->dir)) {
            return;
        }
        $files = glob($this->dir . '/*') ?: [];
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->dir);
    }

    private function path(string $name): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        return $this->dir . '/' . $safe . '.txt';
    }
}
