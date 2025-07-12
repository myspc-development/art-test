<?php
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

if (!isset($user_role) || !ap_user_can_edit_layout($user_role)) {
    wp_die(__('Access denied', 'artpulse'));
}

get_header();

$dashboard_class = $user_role . '-dashboard';
$dashboard_title = ucfirst($user_role) . ' Dashboard';
include locate_template('partials/dashboard-wrapper-start.php');
?>

<?php if (isset($_GET['layout_reset'])): ?>
  <div class="notice notice-success is-dismissible">
    <p><?= __('Dashboard layout reset!', 'artpulse'); ?></p>
  </div>
<?php endif; ?>
<?php if (isset($_GET['preset_loaded'])): ?>
  <div class="notice notice-success is-dismissible">
    <p><?= __('Preset layout loaded.', 'artpulse'); ?></p>
  </div>
<?php endif; ?>

  <form id="ap-preset-loader" method="post" style="margin-bottom:1em;">
    <input type="hidden" name="_ajax_nonce" value="<?= wp_create_nonce('ap_dashboard_nonce') ?>" />
    <select name="preset_key" id="preset-select">
      <option value="">Apply Preset Layout…</option>
      <?php foreach (\ArtPulse\Core\DashboardController::get_default_presets() as $key => $preset): ?>
        <?php if ($preset['role'] === DashboardController::get_role(get_current_user_id())): ?>
          <option value="<?= esc_attr($key) ?>"><?= esc_html($preset['title']) ?></option>
        <?php endif; ?>
      <?php endforeach; ?>
    </select>
    <button type="submit">Apply</button>
  </form>

  <form id="ap-reset-layout" method="post" style="margin-top: 1em;">
    <input type="hidden" name="_ajax_nonce" value="<?= wp_create_nonce('ap_dashboard_nonce') ?>" />
    <button type="submit">Reset Layout</button>
  </form>

  <div id="ap-dashboard-message" style="margin-top: 1em;"></div>

  <label style="display:block;margin-bottom:1em;">
    <input type="checkbox" id="ap-toggle-dark-mode" />
    <?= __('Dark Mode', 'artpulse'); ?>
  </label>

<?php
$layout = DashboardController::get_user_dashboard_layout(get_current_user_id());
if (empty($layout)) {
    echo '<div class="ap-empty-state">No widgets available. Load a preset or reset layout.</div>';
} else {
    echo '<div id="ap-widget-sortable" class="ap-widget-grid" data-role="' . esc_attr(DashboardController::get_role(get_current_user_id())) . '">';
    foreach ($layout as $widget) {
        $id = $widget['id'];
        $config = DashboardWidgetRegistry::get_widget($id);
        if (!$config || empty($config['template'])) continue;

        echo '<div class="ap-widget-block" data-id="' . esc_attr($id) . '">';
        include locate_template('templates/' . $config['template']);
        echo '</div>';
    }
    echo '</div>';
}
?>

<?php
include locate_template('partials/dashboard-wrapper-end.php');
get_footer();
?>
