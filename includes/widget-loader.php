<?php
namespace ArtPulse;

class DashboardWidgetRegistryLoader {
    public static function load_all(): void {
        $base = __DIR__ . '/../widgets/';
        if (!is_dir($base)) {
            return;
        }

        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iter as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), 'Widget.php')) {
                include_once $file->getPathname();
            }
        }
    }
}
