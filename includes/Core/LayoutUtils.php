<?php
namespace ArtPulse\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper utilities for dashboard layout normalization and style merging.
 */
class LayoutUtils
{
    /**
     * Normalize a raw layout array to the schema
     * [ ['id' => 'widget', 'visible' => bool ] ].
     */
    public static function normalize_layout(array $layout, array $valid_ids): array
    {
        $normalized = [];
        foreach ($layout as $item) {
            if (is_array($item) && isset($item['id'])) {
                $id  = sanitize_key($item['id']);
                $vis = isset($item['visible']) ? (bool) $item['visible'] : true;
            } else {
                $id  = sanitize_key((string) $item);
                $vis = true;
            }
            if (in_array($id, $valid_ids, true)) {
                $normalized[] = [ 'id' => $id, 'visible' => $vis ];
            }
        }
        return $normalized;
    }

    /**
     * Merge two style arrays, overwriting keys from the base with $updates.
     */
    public static function merge_styles(array $base, array $updates): array
    {
        $clean = [];
        foreach (array_merge($base, $updates) as $k => $v) {
            $key = sanitize_key($k);
            $val = is_string($v) ? sanitize_text_field($v) : $v;
            $clean[$key] = $val;
        }
        return $clean;
    }
}
