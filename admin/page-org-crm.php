<?php
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Crm\ContactModel;
use ArtPulse\Crm\DonationModel;

function ap_render_org_crm_page() {
    $org_id = absint($_GET['org_id'] ?? get_user_meta(get_current_user_id(), 'ap_organization_id', true));
    $tag    = sanitize_text_field($_GET['tag'] ?? '');

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        $contacts = ContactModel::get_all($org_id, $tag);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="contacts.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['name','email','tags','last_active']);
        foreach ($contacts as $c) {
            fputcsv($out, [$c['name'], $c['email'], $c['tags'], $c['last_active']]);
        }
        fclose($out);
        exit;
    }

    $contacts = ContactModel::get_all($org_id, $tag);
    $summary  = DonationModel::get_summary($org_id);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Organization CRM', 'artpulse'); ?></h1>
        <form method="get" style="margin-bottom:15px;">
            <input type="hidden" name="page" value="ap-org-crm" />
            <input type="hidden" name="org_id" value="<?php echo esc_attr($org_id); ?>" />
            <input type="text" name="tag" value="<?php echo esc_attr($tag); ?>" placeholder="<?php esc_attr_e('Tag filter','artpulse'); ?>" />
            <?php submit_button(__('Filter','artpulse'), 'secondary', '', false); ?>
            <a class="button" href="<?php echo esc_url(add_query_arg('export','csv')); ?>"><?php esc_html_e('Export CSV','artpulse'); ?></a>
        </form>
        <h2><?php esc_html_e('Donor Summary','artpulse'); ?></h2>
        <p><?php printf(__('Total donated: $%s from %d donors','artpulse'), number_format_i18n($summary['total'],2), $summary['count']); ?></p>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name','artpulse'); ?></th>
                    <th><?php esc_html_e('Email','artpulse'); ?></th>
                    <th><?php esc_html_e('Tags','artpulse'); ?></th>
                    <th><?php esc_html_e('Last Active','artpulse'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c) : ?>
                <tr>
                    <td><?php echo esc_html($c['name']); ?></td>
                    <td><?php echo esc_html($c['email']); ?></td>
                    <td><?php echo esc_html($c['tags']); ?></td>
                    <td><?php echo esc_html($c['last_active']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

ap_render_org_crm_page();
