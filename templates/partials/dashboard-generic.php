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
ap_safe_include('partials/dashboard-wrapper-start.php', plugin_dir_path(__FILE__) . 'dashboard-wrapper-start.php');

// Admin preview selector
if (current_user_can('manage_options')): ?>
  <form method="get" id="ap-preview-switcher" class="ap-inline-form">
    <label>🔍 Preview Dashboard As:</label>
    <select name="ap_preview_role">
      <option value="">— Select Role —</option>
      <option value="member" <?= selected($_GET['ap_preview_role'] ?? '', 'member') ?>>Member</option>
      <option value="artist" <?= selected($_GET['ap_preview_role'] ?? '', 'artist') ?>>Artist</option>
      <option value="organization" <?= selected($_GET['ap_preview_role'] ?? '', 'organization') ?>>Organization</option>
    </select>
    <input type="number" name="ap_preview_user" placeholder="User ID" value="<?= esc_attr($_GET['ap_preview_user'] ?? '') ?>" />
    <input type="submit" value="Preview">
  </form>
<?php endif; ?>

<?php if (current_user_can('manage_options') && isset($_GET['ap_preview_role'])): ?>
  <div class="ap-admin-preview">
    <strong>Previewing Dashboard as:</strong> <?= esc_html($_GET['ap_preview_role']) ?>
  </div>
<?php endif; ?>
<?php if (current_user_can('manage_options') && isset($_GET['ap_preview_user'])): ?>
  <div class="ap-admin-preview">
    <strong>Previewing Dashboard for User ID:</strong> <?= (int) $_GET['ap_preview_user'] ?>
  </div>
<?php endif; ?>

<?php if (current_user_can('manage_options')): ?>
  <div class="notice notice-info">
    <p><strong>🧩 DEBUG: Rendering Widget Diagnostic</strong></p>

    <p><strong>Current User Role:</strong><br>
    <?= esc_html(\ArtPulse\Core\DashboardController::get_role(get_current_user_id())) ?></p>

    <p><strong>📋 Active Layout:</strong></p>
    <pre><?php print_r(\ArtPulse\Core\DashboardController::get_user_dashboard_layout(get_current_user_id())); ?></pre>

    <p><strong>🗂️ Registered Widgets:</strong></p>
    <pre><?php print_r(\ArtPulse\Core\DashboardWidgetRegistry::get_all()); ?></pre>
  </div>
<?php endif; ?>
<?php
$user_id = get_current_user_id();
if (current_user_can('manage_options') && isset($_GET['ap_preview_user'])) {
    $preview_id = (int) $_GET['ap_preview_user'];
    if (get_userdata($preview_id)) {
        $user_id = $preview_id;
    }
}
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

  <form id="ap-preset-loader" method="post" class="ap-inline-form">
    <input type="hidden" name="_ajax_nonce" value="<?= wp_create_nonce('ap_dashboard_nonce') ?>" />
    <select name="preset_key" id="preset-select">
      <option value="">Apply Preset Layout…</option>
      <?php foreach (\ArtPulse\Core\DashboardController::get_default_presets() as $key => $preset): ?>
        <?php if ($preset['role'] === DashboardController::get_role($user_id)): ?>
          <option value="<?= esc_attr($key) ?>"><?= esc_html($preset['title']) ?></option>
        <?php endif; ?>
      <?php endforeach; ?>
    </select>
    <button type="submit">Apply</button>
  </form>

  <form id="ap-reset-layout" method="post" class="ap-inline-form--mt">
    <input type="hidden" name="_ajax_nonce" value="<?= wp_create_nonce('ap_dashboard_nonce') ?>" />
    <button type="submit">Reset Layout</button>
  </form>

  <div id="ap-dashboard-message" class="ap-dashboard-message"></div>

  <label class="ap-form-group">
    <input type="checkbox" id="ap-toggle-dark-mode" />
    <?= __('Dark Mode', 'artpulse'); ?>
  </label>

<?php
$layout = $layout ?? DashboardController::get_user_dashboard_layout($user_id);
if (empty($layout)) {
    echo '<div class="ap-empty-state">No widgets available. Load a preset or reset layout.</div>';
} else {
    echo '<div id="ap-user-dashboard" class="ap-dashboard-grid" data-role="' . esc_attr(DashboardController::get_role($user_id)) . '">';
    foreach ($layout as $widget) {
        $id      = $widget['id'];
        $visible = $widget['visible'] ?? true;
        $config  = DashboardWidgetRegistry::get_widget($id);
        if (!$config) {
            continue;
        }

        echo '<div class="ap-widget-card" data-id="' . esc_attr($id) . '" data-visible="' . ($visible ? '1' : '0') . '">';
        echo '<span class="drag-handle" role="button" tabindex="0" aria-label="Move widget"></span>';

        ap_render_widget($id, $user_id);

        echo '</div>';
    }

    \ArtPulse\Core\DashboardWidgetRegistry::render_for_role($user_id);

    echo '</div>';
}
?>

<?php
ap_safe_include('partials/dashboard-wrapper-end.php', plugin_dir_path(__FILE__) . 'dashboard-wrapper-end.php');
get_footer();
?>
