<?php
namespace ArtPulse\Frontend;

class OrganizationSubmissionForm {
    public static function register(): void {
        add_shortcode('ap_submit_organization', [self::class, 'render']);
    }

    public static function render(): string {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            return '';
        }

        wp_enqueue_script('ap-org-submission-js');

        $fields = \ArtPulse\Admin\MetaBoxesOrganisation::get_registered_org_meta_fields();
        // Avoid duplicate name input; the title field already captures the
        // organization name.
        unset($fields['ead_org_name']);

        ob_start();
        ?>
        <form class="ap-org-submission-form" enctype="multipart/form-data">
            <p>
                <label for="ap-org-title"><?php esc_html_e('Organization Name', 'artpulse'); ?></label><br>
                <input id="ap-org-title" type="text" name="title" required />
            </p>
            <?php foreach ($fields as $key => $args) {
                list($type, $label) = $args;
                ?>
                <p>
                    <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label><br>
                    <?php
                    switch ($type) {
                        case 'textarea':
                            printf('<textarea id="%1$s" name="%1$s"%2$s></textarea>', esc_attr($key), $key === 'ead_org_description' ? ' required' : '');
                            break;
                        case 'checkbox':
                            printf('<input id="%1$s" type="checkbox" name="%1$s" value="1" />', esc_attr($key));
                            break;
                        case 'select':
                            if ($key === 'ead_org_type') {
                                $opts = ['gallery', 'museum', 'studio', 'collective', 'non-profit', 'commercial-gallery', 'public-art-space', 'educational-institution', 'other'];
                                echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">';
                                echo '<option value="">' . esc_html__('Select', 'artpulse') . '</option>';
                                foreach ($opts as $opt) {
                                    echo '<option value="' . esc_attr($opt) . '">' . esc_html(ucfirst(str_replace('-', ' ', $opt))) . '</option>';
                                }
                                echo '</select>';
                            } else {
                                printf('<input id="%1$s" type="text" name="%1$s" />', esc_attr($key));
                            }
                            break;
                        case 'media':
                            printf('<input id="%1$s" type="file" name="%1$s" accept="image/*" />', esc_attr($key));
                            break;
                        default:
                            $req = $key === 'ead_org_primary_contact_email' ? ' required' : '';
                            printf('<input id="%1$s" type="%2$s" name="%1$s"%3$s />', esc_attr($key), esc_attr($type), $req);
                    }
                    ?>
                </p>
            <?php } ?>
            <p>
                <label for="ap-org-images"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label><br>
                <input id="ap-org-images" type="file" name="images[]" accept="image/*" multiple />
                <input type="hidden" name="address_components" id="ap-org-address-components" />
            </p>
            <p>
                <button type="submit"><?php esc_html_e('Submit', 'artpulse'); ?></button>
            </p>
        </form>
        <div class="ap-org-submission-message" role="status" aria-live="polite"></div>
        <?php
        return ob_get_clean();
    }
}
