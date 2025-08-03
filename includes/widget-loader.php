<?php
namespace ArtPulse;

class DashboardWidgetRegistryLoader {
    public static function load_all(): void {
        $dir = __DIR__ . '/../widgets/member/';
        if (!is_dir($dir)) {
            return;
        }
        foreach (glob($dir . '*Widget.php') as $file) {
            include_once $file;
        }
    }
}
