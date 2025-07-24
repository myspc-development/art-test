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
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=ap-portfolio-logs')); ?>"><?php esc_html_e('View Sync Logs', 'artpulse'); ?></a></p>
        </div>
        <?php
    }
}
