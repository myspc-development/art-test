<?php
namespace {
    // Pretend we're in CLI mode.
    if (!defined('WP_CLI')) {
        define('WP_CLI', true);
    }

    // Minimal base class many commands extend.
    if (!class_exists('WP_CLI_Command')) {
        class WP_CLI_Command {}
    }

    if (!class_exists('WP_CLI')) {
        class WP_CLI {
            /** @var array<string,mixed> */
            private static $commands = [];
            /** @var array<string,mixed> */
            private static $config = [];

            // Console-ish output
            public static function log($msg)     { echo rtrim((string)$msg, "\n") . PHP_EOL; }
            public static function line($msg)    { echo rtrim((string)$msg, "\n") . PHP_EOL; }
            public static function success($msg) { echo rtrim((string)$msg, "\n") . PHP_EOL; }
            public static function warning($msg) { fwrite(STDERR, rtrim((string)$msg, "\n") . PHP_EOL); }
            public static function error($msg)   { throw new \RuntimeException(is_string($msg) ? $msg : json_encode($msg)); }
            public static function debug($msg, $group = null) {
                $enabled = getenv('WP_CLI_DEBUG') ?: (defined('WP_CLI_DEBUG') && WP_CLI_DEBUG);
                if ($enabled) {
                    $prefix = $group ? "[{$group}] " : '';
                    fwrite(STDERR, $prefix . rtrim((string)$msg, "\n") . PHP_EOL);
                }
            }

            // Command registry
            public static function add_command($name, $callable, $args = []) { self::$commands[$name] = $callable; }
            public static function get_command(string $name) { return self::$commands[$name] ?? null; }
            public static function run(string $name, array $positional = [], array $assoc = []) {
                $cmd = self::get_command($name);
                if (is_array($cmd) && isset($cmd[0], $cmd[1]) && is_object($cmd[0])) {
                    return $cmd[0]->{$cmd[1]}($positional, $assoc);
                } elseif (is_object($cmd) && method_exists($cmd, '__invoke')) {
                    return $cmd($positional, $assoc);
                } elseif (is_callable($cmd)) {
                    return call_user_func($cmd, $positional, $assoc);
                }
                throw new \RuntimeException("Command '$name' not found");
            }

            // Misc helpers
            public static function colorize($string) { return (string)$string; }  // no-op
            public static function confirm($question, $assoc_args = []) { /* auto-yes */ }
            public static function error_multi_line($lines) { throw new \RuntimeException(implode("\n", (array)$lines)); }
            public static function add_hook($event, $callable, $args = []) { /* no-op */ }

            // Config getters some code calls
            public static function get_config($key = null) {
                if ($key === null) return self::$config;
                return self::$config[$key] ?? null;
            }
            public static function set_config(array $cfg) { self::$config = $cfg; return true; }
        }
    }
}

namespace WP_CLI {
    /** Minimal stand-in for \WP_CLI\Formatter */
    class Formatter {
        /** @var array<int,string>|null */
        private $fields;
        /** @var string */
        private $format;

        /**
         * @param array<string,mixed> $assoc_args
         * @param array<int,string>|string|null $fields
         * @param string $default
         */
        public function __construct(array $assoc_args = [], $fields = null, $default = 'table') {
            $this->fields = is_string($fields) ? array_map('trim', explode(',', $fields)) : $fields;
            $this->format = $assoc_args['format'] ?? $default;
        }
        /** @param array<int,array<string,mixed>>|array<string,mixed> $items */
        public function display_items($items) {
            \WP_CLI\Utils\format_items($this->format, $items, $this->fields);
        }
    }
}

namespace WP_CLI\Utils {
    function format_items($format, $items, $fields = null) {
        if ($format === 'json') {
            echo json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
            return;
        }
        if ($format === 'table') {
            if (is_array($items) && $items) {
                $headers = $fields ?: array_keys((array)reset($items));
                echo implode("\t", $headers) . PHP_EOL;
                foreach ($items as $row) {
                    $vals = $fields
                        ? array_map(fn($f) => (string)($row[$f] ?? ''), $fields)
                        : array_map('strval', (array)$row);
                    echo implode("\t", $vals) . PHP_EOL;
                }
            }
            return;
        }
        echo print_r($items, true) . PHP_EOL;
    }
    function get_flag_value($assoc_args, $key, $default = null) {
        if (!is_array($assoc_args)) { return $default; }
        return array_key_exists($key, $assoc_args) ? $assoc_args[$key] : $default;
    }

    // No-op progress bar used by some commands
    class ProgressBar {
        public function tick($n = 1) {}
        public function finish() {}
    }
    function make_progress_bar($label, $count, $interval = 1) {
        return new ProgressBar();
    }
}
