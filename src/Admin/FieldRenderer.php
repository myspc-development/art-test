<?php
namespace ArtPulse\Admin;

class FieldRenderer
{
    /**
     * Render a settings field based on configuration.
     *
     * @param array  $field   Field definition including 'key', 'type', 'desc', etc.
     * @param string $tab_id  Tab slug the field belongs to.
     */
    public static function render(array $field, string $tab_id): void
    {
        $options = get_option('artpulse_settings');
        $key     = $field['key'];
        $value   = $options[$key] ?? '';
        $desc    = $field['desc'] ?? '';
        $type    = $field['type'] ?? 'text';

        switch ($type) {
            case 'checkbox':
                printf(
                    '<input type="checkbox" id="%1$s" name="artpulse_settings[%1$s]" value="1"%2$s />',
                    esc_attr($key),
                    checked(1, $value, false)
                );
                break;

            case 'textarea':
                printf(
                    '<textarea id="%1$s" name="artpulse_settings[%1$s]" rows="5" class="large-text">%2$s</textarea>',
                    esc_attr($key),
                    esc_textarea($value)
                );
                break;

            case 'number':
                printf(
                    '<input type="number" id="%1$s" name="artpulse_settings[%1$s]" value="%2$s" class="regular-text" />',
                    esc_attr($key),
                    esc_attr($value)
                );
                break;

            case 'select':
                echo '<select id="' . esc_attr($key) . '" name="artpulse_settings[' . esc_attr($key) . ']">';
                foreach ($field['options'] ?? [] as $opt_value => $opt_label) {
                    $val   = is_int($opt_value) ? $opt_label : $opt_value;
                    $label = is_int($opt_value) ? $opt_label : $opt_label;
                    echo '<option value="' . esc_attr($val) . '"' . selected($value, $val, false) . '>' . esc_html($label) . '</option>';
                }
                echo '</select>';
                break;

            case 'text':
            default:
                printf(
                    '<input type="text" id="%1$s" name="artpulse_settings[%1$s]" value="%2$s" class="regular-text" />',
                    esc_attr($key),
                    esc_attr($value)
                );
                break;
        }

        if ($desc) {
            echo '<p class="description">' . esc_html($desc) . '</p>';
        }
    }
}
