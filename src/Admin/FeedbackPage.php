<?php
namespace ArtPulse\Admin;

class FeedbackPage
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'add_menu']);
    }

    public static function add_menu(): void
    {
        add_submenu_page(
            'artpulse',
            __('User Feedback', 'artpulse'),
            __('Feedback', 'artpulse'),
            'manage_options',
            'ap-feedback',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_feedback';
        if (isset($_POST['ap_feedback_status']) && check_admin_referer('ap_feedback_status')) {
            $id = absint($_POST['id']);
            $status = sanitize_text_field($_POST['status']);
            if (in_array($status, ['planned', 'in_progress', 'completed'], true)) {
                $wpdb->update($table, ['status' => $status], ['id' => $id]);
            }
        }
        $items = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 200");
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('User Feedback', 'artpulse'); ?></h1>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Type', 'artpulse'); ?></th>
                        <th><?php esc_html_e('User/Email', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Description', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Context', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Votes', 'artpulse'); ?></th>
                        <th><?php esc_html_e('Status', 'artpulse'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item->created_at))); ?></td>
                        <td><?php echo esc_html($item->type); ?></td>
                        <td><?php echo esc_html($item->email ?: ($item->user_id ? get_user_by('ID', $item->user_id)->user_email : '')); ?></td>
                        <td><?php echo esc_html(wp_trim_words($item->description, 20)); ?></td>
                        <td><?php echo esc_html($item->context); ?></td>
                        <td><?php echo esc_html($item->votes); ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <?php wp_nonce_field('ap_feedback_status'); ?>
                                <input type="hidden" name="id" value="<?php echo esc_attr($item->id); ?>">
                                <select name="status">
                                    <option value="planned" <?php selected($item->status, 'planned'); ?>><?php esc_html_e('Planned', 'artpulse'); ?></option>
                                    <option value="in_progress" <?php selected($item->status, 'in_progress'); ?>><?php esc_html_e('In Progress', 'artpulse'); ?></option>
                                    <option value="completed" <?php selected($item->status, 'completed'); ?>><?php esc_html_e('Completed', 'artpulse'); ?></option>
                                </select>
                                <button type="submit" name="ap_feedback_status" class="button">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                    <tr><td colspan="7"><?php esc_html_e('No feedback found.', 'artpulse'); ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
