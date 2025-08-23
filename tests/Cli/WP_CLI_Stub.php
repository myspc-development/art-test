<?php
namespace {
    class WP_CLI {
        public static array $commands = [];
        public static string $last_output = '';

        public static function add_command( string $name, $callable ): void {
            self::$commands[ $name ] = $callable;
        }

        public static function runcommand( string $command ): string {
            $parts = preg_split('/\s+/', trim($command));
            $cmd_tokens = [];
            $assoc = [];
            foreach ($parts as $p) {
                if (str_starts_with($p, '--')) {
                    $p = substr($p, 2);
                    $kv = explode('=', $p, 2);
                    $assoc[$kv[0]] = $kv[1] ?? true;
                } else {
                    $cmd_tokens[] = $p;
                }
            }
            if (!$cmd_tokens) {
                throw new \RuntimeException('No command given.');
            }
            $root = $cmd_tokens[0];
            $index = 1;
            $two = $cmd_tokens[0] . ' ' . ($cmd_tokens[1] ?? '');
            if (isset(self::$commands[$two])) {
                $root = $two;
                $index = 2;
            }
            $sub = $cmd_tokens[$index] ?? null;
            $handler = self::$commands[$root] ?? null;
            if (is_string($handler)) {
                $obj = new $handler();
            } elseif (is_object($handler)) {
                $obj = $handler;
            } else {
                throw new \RuntimeException('Command not registered.');
            }
            $args = [];
            if ($sub !== null && method_exists($obj, $sub)) {
                $method = $sub;
                $args = array_slice($cmd_tokens, $index + 1);
            } elseif ($sub !== null && method_exists($obj, $sub . '_')) {
                $method = $sub . '_';
                $args = array_slice($cmd_tokens, $index + 1);
            } else {
                $method = '__invoke';
                $args = array_slice($cmd_tokens, $index);
            }
            ob_start();
            try {
                $obj->$method($args, $assoc);
            } catch (\RuntimeException $e) {
                self::$last_output = ob_get_clean();
                throw $e;
            }
            self::$last_output = ob_get_clean();
            return self::$last_output;
        }

        public static function success(string $msg): void { echo $msg . "\n"; }
        public static function log(string $msg): void { echo $msg . "\n"; }
        public static function warning(string $msg): void { echo $msg . "\n"; }
        public static function error(string $msg): void { throw new \RuntimeException($msg); }
        public static function print_value($value, array $opts = []): void {
            if (!empty($opts['json'])) {
                echo json_encode($value);
            } else {
                echo (string)$value;
            }
        }
    }
}

namespace WP_CLI\Utils {
    function format_items($type, $items, $fields): void {
        echo implode("\t", $fields) . "\n";
        foreach ($items as $row) {
            $out = [];
            foreach ($fields as $f) {
                $out[] = $row[$f] ?? '';
            }
            echo implode("\t", $out) . "\n";
        }
    }
}
