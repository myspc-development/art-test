<?php
namespace ArtPulse\Traits;

/**
 * Provides a generic register() implementation to attach hooks.
 *
 * Classes using this trait should define a constant HOOKS or
 * a static $hooks property describing hooks to register.
 */
trait Registerable
{
    public static function register(): void
    {
        $hooks = [];
        if (defined('static::HOOKS')) {
            $hooks = static::HOOKS;
        } elseif (property_exists(static::class, 'hooks')) {
            $hooks = static::$hooks;
        }

        foreach ($hooks as $key => $data) {
            if (is_int($key)) {
                $hook = $data[0] ?? null;
                $method = $data[1] ?? null;
                $priority = $data[2] ?? 10;
                $args = $data[3] ?? 1;
                $type = $data[4] ?? 'action';
            } else {
                $hook = $key;
                if (is_string($data)) {
                    $method = $data;
                    $priority = 10;
                    $args = 1;
                    $type = 'action';
                } elseif (isset($data['method'])) {
                    $method = $data['method'];
                    $priority = $data['priority'] ?? 10;
                    $args = $data['args'] ?? 1;
                    $type = $data['type'] ?? 'action';
                } else {
                    $method = $data[0] ?? null;
                    $priority = $data[1] ?? 10;
                    $args = $data[2] ?? 1;
                    $type = $data[3] ?? 'action';
                }
            }

            if (!$hook || !$method) {
                continue;
            }

            $callable = [static::class, $method];
            if ($type === 'filter') {
                add_filter($hook, $callable, $priority, $args);
            } else {
                add_action($hook, $callable, $priority, $args);
            }
        }
    }
}
