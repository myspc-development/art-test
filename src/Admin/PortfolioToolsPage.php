<?php
namespace ArtPulse\Admin;

class PortfolioToolsPage
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
    }

    public static function addMenu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Portfolio Sync', 'artpulse'),
            __('Portfolio Sync', 'artpulse'),
            'manage_options',
            'ap-portfolio-sync',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $synced = isset($_GET['synced']) ? absint($_GET['synced']) : 0;
        $migrated = isset($_GET['migrated']) ? absint($_GET['migrated']) : 0;
        $types   = get_option('ap_portfolio_sync_types', ['artpulse_event','artpulse_artist','artpulse_org']);
        $types   = is_array($types) ? $types : [];
        $cat_map = get_option('ap_portfolio_category_map', [
            'artpulse_event'  => 'Event',
            'artpulse_artist' => 'Artist',
            'artpulse_org'    => 'Organization',
        ]);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Portfolio Sync Tools', 'artpulse'); ?></h1>
            <?php if ($synced): ?>
                <div class="notice notice-success is-dismissible"><p><?php printf(esc_html__('Synchronized %d portfolio items.', 'artpulse'), $synced); ?></p></div>
            <?php endif; ?>
            <?php if ($migrated): ?>
                <div class="notice notice-success is-dismissible"><p><?php printf(esc_html__('Migrated %d legacy items.', 'artpulse'), $migrated); ?></p></div>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('ap_sync_all_portfolios'); ?>
                <input type="hidden" name="action" value="ap_sync_all_portfolios">
                <?php submit_button(__('Run Full Sync', 'artpulse')); ?>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:20px;">
                <?php wp_nonce_field('ap_migrate_portfolio'); ?>
                <input type="hidden" name="action" value="ap_migrate_portfolio">
                <?php submit_button(__('Migrate Legacy Portfolios', 'artpulse')); ?>
            </form>
            <hr/>
            <h2><?php esc_html_e('Sync Settings', 'artpulse'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('ap_save_portfolio_sync_settings'); ?>
                <input type="hidden" name="action" value="ap_save_portfolio_sync_settings">
                <p><?php esc_html_e('Select post types to sync:', 'artpulse'); ?></p>
                <label><input type="checkbox" name="sync_types[]" value="artpulse_event" <?php checked(in_array('artpulse_event',$types,true)); ?> /> <?php esc_html_e('Events','artpulse'); ?></label><br>
                <label><input type="checkbox" name="sync_types[]" value="artpulse_artist" <?php checked(in_array('artpulse_artist',$types,true)); ?> /> <?php esc_html_e('Artists','artpulse'); ?></label><br>
                <label><input type="checkbox" name="sync_types[]" value="artpulse_org" <?php checked(in_array('artpulse_org',$types,true)); ?> /> <?php esc_html_e('Organizations','artpulse'); ?></label>
                <h3><?php esc_html_e('Category Labels', 'artpulse'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Event', 'artpulse'); ?></th>
                        <td><input type="text" name="cat_map[artpulse_event]" value="<?php echo esc_attr($cat_map['artpulse_event'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Artist', 'artpulse'); ?></th>
                        <td><input type="text" name="cat_map[artpulse_artist]" value="<?php echo esc_attr($cat_map['artpulse_artist'] ?? ''); ?>" /></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Organization', 'artpulse'); ?></th>
                        <td><input type="text" name="cat_map[artpulse_org]" value="<?php echo esc_attr($cat_map['artpulse_org'] ?? ''); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(__('Save Settings','artpulse')); ?>
            </form>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=ap-portfolio-logs')); ?>"><?php esc_html_e('View Sync Logs', 'artpulse'); ?></a></p>
        </div>
        <?php
    }
}
