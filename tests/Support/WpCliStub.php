<?php
namespace {
// Global WP-CLI shims.
if (!class_exists('WP_CLI_Command')) {
    class WP_CLI_Command {}
}
if (!class_exists('WP_CLI')) {
    class WP_CLI
    {
        /** @var array<string,mixed> */
        public static $commands = array();
        /** @var string */
        public static $last_output = '';
        /** @var array<string,mixed> */
        private static $config = array();

        public static function log($msg): void { echo rtrim((string)$msg, "\n") . PHP_EOL; }
        public static function line($msg): void { echo rtrim((string)$msg, "\n") . PHP_EOL; }
        public static function success($msg): void { echo rtrim((string)$msg, "\n") . PHP_EOL; }
        public static function warning($msg): void { echo rtrim((string)$msg, "\n") . PHP_EOL; }
        public static function error($msg, $exit_code = 1): void {
            throw new \WP_CLI\ExitException(is_string($msg) ? $msg : json_encode($msg), (int) $exit_code);
        }
        public static function print_value($value, array $assoc_args = array()): void
        {
            $is_json = !empty($assoc_args['json']);
            $nl      = array_key_exists('nl', $assoc_args) ? (bool) $assoc_args['nl'] : !$is_json;

            if ($is_json) {
                $out = json_encode($value);
            } elseif (is_array($value) || is_object($value)) {
                $out = print_r($value, true);
            } else {
                $out = (string) $value;
            }

            if ($nl) {
                echo rtrim($out, "\n") . PHP_EOL;
            } else {
                echo $out;
            }
        }
        public static function debug($msg, $group = null): void
        {
            $enabled = getenv('WP_CLI_DEBUG');
            if ($enabled || (defined('WP_CLI_DEBUG') && WP_CLI_DEBUG)) {
                $prefix = $group ? "[{$group}] " : '';
                fwrite(STDERR, $prefix . rtrim((string)$msg, "\n") . PHP_EOL);
            }
        }
        public static function add_command($name, $callable, $args = array()): void
        {
            self::$commands[$name] = $callable;
        }
        public static function get_command($name)
        {
            return self::$commands[$name] ?? null;
        }
        private static function resolve_method($obj, $token)
        {
            $candidates = array($token, str_replace('-', '_', $token));
            $reserved = array('list','new','class','namespace','trait','echo','print');
            if (in_array($token, $reserved, true)) {
                $candidates[] = $token . '_';
            }
            foreach ($candidates as $cand) {
                if (method_exists($obj, $cand)) {
                    return $cand;
                }
            }
            return null;
        }
        public static function run($name, array $positional = array(), array $assoc = array())
        {
            $callable = self::get_command($name);
            if (!$callable) {
                throw new \RuntimeException("Command '$name' not found");
            }
            $obj = null;
            $method = null;
            if (is_string($callable) && class_exists($callable)) {
                $obj = new $callable();
            } elseif (is_array($callable) && isset($callable[0])) {
                $obj = is_string($callable[0]) ? new $callable[0]() : $callable[0];
                $method = $callable[1] ?? null;
            } elseif (is_callable($callable)) {
                $obj = $callable;
            } else {
                throw new \RuntimeException('Uncallable command');
            }
            if ($obj && !$method && !($obj instanceof \Closure)) {
                if ($positional && is_string($positional[0])) {
                    $sub = array_shift($positional);
                    $m   = self::resolve_method($obj, $sub);
                    if ($m) {
                        $method = $m;
                    } elseif (method_exists($obj, '__invoke')) {
                        array_unshift($positional, $sub);
                        $method = '__invoke';
                    } else {
                        throw new \RuntimeException("Subcommand '{$sub}' not found on command");
                    }
                } elseif (method_exists($obj, '__invoke')) {
                    $method = '__invoke';
                } else {
                    throw new \RuntimeException('No subcommand and not invokable');
                }
            }
            ob_start();
            try {
                if ($obj instanceof \Closure || (is_object($obj) && $method === '__invoke')) {
                    ($obj)($positional, $assoc);
                } elseif (is_object($obj) && $method && method_exists($obj, $method)) {
                    $obj->{$method}($positional, $assoc);
                } elseif (is_callable($callable)) {
                    call_user_func($callable, $positional, $assoc);
                } else {
                    throw new \RuntimeException('Uncallable command method');
                }
            } finally {
                self::$last_output = ob_get_clean();
            }
            return self::$last_output;
        }
        public static function runcommand($command_string)
        {
            $tokens = preg_split('/\s+/', trim($command_string));
            if (!$tokens) {
                return '';
            }
            $best = null;
            $bestLen = 0;
            foreach (array_keys(self::$commands) as $name) {
                $parts = preg_split('/\s+/', trim($name));
                $len   = count($parts);
                if ($len > $bestLen && $len <= count($tokens)) {
                    $slice = array_slice($tokens, 0, $len);
                    if ($slice === $parts) {
                        $best    = $name;
                        $bestLen = $len;
                    }
                }
            }
            if ($best === null) {
                $best    = $tokens[0];
                $bestLen = 1;
            }
            $rest = array_slice($tokens, $bestLen);
            $assoc = array();
            $positional = array();
            foreach ($rest as $t) {
                if (strpos($t, '--') === 0) {
                    $eq = strpos($t, '=');
                    if ($eq !== false) {
                        $k = substr($t, 2, $eq - 2);
                        $v = substr($t, $eq + 1);
                        $assoc[$k] = $v;
                    } else {
                        $assoc[substr($t, 2)] = true;
                    }
                } else {
                    $positional[] = $t;
                }
            }
            return self::run($best, $positional, $assoc);
        }
        public static function colorize($string)
        {
            return (string) $string;
        }
        public static function confirm($question, $assoc_args = array()): void {}
        public static function error_multi_line($lines, $exit_code = 1): void {
            throw new \WP_CLI\ExitException(implode("\n", (array) $lines), (int) $exit_code);
        }
        public static function add_hook($event, $callable, $args = array()): void {}
        public static function get_config($key = null)
        {
            if ($key === null) {
                return self::$config;
            }
            return array_key_exists($key, self::$config) ? self::$config[$key] : null;
        }
        public static function set_config(array $cfg): bool { self::$config = $cfg; return true; }
    }
}
}

namespace WP_CLI {
if (!class_exists('WP_CLI\\ExitException')) {
    class ExitException extends \Exception {}
}
if (!class_exists('WP_CLI\\Formatter')) {
    class Formatter
    {
        /** @var array<int,string>|null */
        private $fields;
        /** @var string */
        private $format;
        public function __construct(array $assoc_args = array(), $fields = null, $default = 'table')
        {
            if (is_string($fields)) {
                $fields = array_map('trim', explode(',', $fields));
            }
            $this->fields = $fields;
            $this->format = isset($assoc_args['format']) ? $assoc_args['format'] : $default;
        }
        public function display_items($items): void
        {
            \WP_CLI\Utils\format_items($this->format, $items, $this->fields);
        }
    }
}
}

namespace WP_CLI\Utils {
if (!function_exists('WP_CLI\\Utils\\format_items')) {
    function format_items($format, $items, $fields = null): void
    {
        if ($format === 'json') {
            echo json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
            return;
        }
        if ($format === 'table') {
            if (is_array($items) && $items) {
                $headers = $fields ?: array_keys((array) reset($items));
                echo implode("\t", $headers) . PHP_EOL;
                foreach ($items as $row) {
                    $vals = array();
                    if ($fields) {
                        foreach ($fields as $f) {
                            $vals[] = isset($row[$f]) ? (string) $row[$f] : '';
                        }
                    } else {
                        foreach ((array) $row as $v) {
                            $vals[] = (string) $v;
                        }
                    }
                    echo implode("\t", $vals) . PHP_EOL;
                }
            }
            return;
        }
        echo print_r($items, true) . PHP_EOL;
    }
    function get_flag_value($assoc_args, $key, $default = null)
    {
        if (!is_array($assoc_args)) {
            return $default;
        }
        return array_key_exists($key, $assoc_args) ? $assoc_args[$key] : $default;
    }
    class ProgressBar { public function tick($n = 1): void {} public function finish(): void {} }
    function make_progress_bar($label, $count, $interval = 1): ProgressBar { return new ProgressBar(); }
}
}

namespace ArtPulse\Tests {

final class WpCliStub
{
    public static function load(): void
    {
        if (!defined('WP_CLI')) {
            define('WP_CLI', true);
        }
    }
}
}
