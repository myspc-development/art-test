<?php
namespace ArtPulse\Widgets\Organization;

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class WebhooksWidget implements DashboardWidgetInterface
{
    public static function id(): string
    {
        return 'webhooks';
    }

    public static function label(): string
    {
        return esc_html__('Webhooks', 'artpulse');
    }

    public static function roles(): array
    {
        return ['organization', 'administrator'];
    }

    public static function description(): string
    {
        return esc_html__('Configured webhooks for this organization.', 'artpulse');
    }

    public static function boot(): void
    {
        add_action('artpulse/widgets/register', [self::class, 'register'], 10, 1);
    }

    public static function register($registry = null): void
    {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'rest-api',
            self::description(),
            [self::class, 'render'],
            [
                'roles'      => self::roles(),
                'capability' => 'ap_manage_org',
                'category'   => 'integrations',
            ]
        );
    }

    public static function render(int $user_id = 0): string
    {
        if (!current_user_can('ap_manage_org') && !current_user_can('manage_options')) {
            $heading_id = self::id() . '-heading';
            ob_start();
            ?>
            <section role="region" aria-labelledby="<?php echo esc_attr($heading_id); ?>"
                data-widget="<?php echo esc_attr(self::id()); ?>"
                data-widget-id="<?php echo esc_attr(self::id()); ?>"
                class="ap-widget ap-<?php echo esc_attr(self::id()); ?>">
                <h2 id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html(self::label()); ?></h2>
                <p><?php echo esc_html__('Permission denied', 'artpulse'); ?></p>
            </section>
            <?php
            return ob_get_clean();
        }
        $heading_id = self::id() . '-heading';
        $hooks = [];
        if (isset($GLOBALS['APOrgWebhooks']) && is_array($GLOBALS['APOrgWebhooks'])) {
            $hooks = $GLOBALS['APOrgWebhooks']['webhooks'] ?? [];
        } elseif (function_exists('wp_remote_get') && function_exists('rest_url')) {
            $org_id = $GLOBALS['APOrgWebhooks']['orgId'] ?? 0;
            $url    = rest_url('artpulse/v1/org/' . $org_id . '/webhooks');
            $resp   = wp_remote_get($url);
            if (!is_wp_error($resp) && 200 === wp_remote_retrieve_response_code($resp)) {
                $data = json_decode(wp_remote_retrieve_body($resp), true);
                if (is_array($data)) {
                    $hooks = $data;
                }
            }
        }
        ob_start();
        ?>
        <section role="region" aria-labelledby="<?php echo esc_attr($heading_id); ?>"
            data-widget="<?php echo esc_attr(self::id()); ?>"
            data-widget-id="<?php echo esc_attr(self::id()); ?>"
            class="ap-widget ap-<?php echo esc_attr(self::id()); ?>">
            <h2 id="<?php echo esc_attr($heading_id); ?>"><?php echo esc_html(self::label()); ?></h2>
            <?php if (empty($hooks)) : ?>
                <p><?php echo esc_html__('No webhooks configured.', 'artpulse'); ?></p>
            <?php else : ?>
                <ul>
                    <?php foreach ($hooks as $hook) : ?>
                        <?php $url = $hook['url'] ?? ''; ?>
                        <?php $active = !empty($hook['active']); ?>
                        <li><?php echo esc_html($url); ?> <?php echo $active ? '' : esc_html__('(inactive)', 'artpulse'); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <p><a class="ap-widget__cta" href="#"><?php echo esc_html__('Manage Webhooks', 'artpulse'); ?></a></p>
        </section>
        <?php
        return ob_get_clean();
    }
}

