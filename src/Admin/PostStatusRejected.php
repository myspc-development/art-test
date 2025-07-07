<?php
namespace ArtPulse\Admin;

class PostStatusRejected
{
    public static function register()
    {
        add_action('init', [self::class, 'register_status']);
        add_filter('display_post_states', [self::class, 'state_label']);
        add_action('admin_footer-post.php', [self::class, 'inject_dropdown']);
        add_action('admin_footer-post-new.php', [self::class, 'inject_dropdown']);
    }

    public static function register_status()
    {
        register_post_status('rejected', [
            'label'                     => _x('Rejected', 'post', 'artpulse'),
            'public'                    => false,
            'exclude_from_search'       => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>', 'artpulse'),
        ]);
    }

    public static function state_label($states)
    {
        global $post;
        if ($post && $post->post_status === 'rejected') {
            $states[] = __('Rejected', 'artpulse');
        }
        return $states;
    }

    public static function inject_dropdown()
    {
        global $post;
        if (!$post || $post->post_type !== 'artpulse_event') {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(function($){
            var $select = $('#post-status-select select');
            if(!$select.find('option[value="rejected"]').length){
                var selected = $select.val() === 'rejected' ? ' selected="selected"' : '';
                $select.append('<option value="rejected"'+selected+'><?php echo esc_js(__('Rejected', 'artpulse')); ?></option>');
            }
        });
        </script>
        <?php
    }
}
