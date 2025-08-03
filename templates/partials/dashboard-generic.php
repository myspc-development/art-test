<?php
use ArtPulse\Admin\DashboardWidgetTools;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardWidgetRegistry;

if (!isset($user_role) || !ap_user_can_edit_layout($user_role)) {
    wp_die(__('Access denied', 'artpulse'));
}

get_header();
?>
<div class="wrap">
  <div class="dashboard-widgets-wrap <?php echo esc_attr( $user_role . '-dashboard' ); ?>">
    <?php $dashboard_title = ucfirst($user_role) . ' Dashboard'; ?>
    <h2 class="ap-card__title"><?php echo esc_html( $dashboard_title ); ?></h2>
    <form method="post" class="ap-dashboard-reset ap-inline-form">
      <?php wp_nonce_field('ap_reset_user_layout'); ?>
      <input type="hidden" name="reset_user_layout" value="1" />
      <button class="button"><?php esc_html_e('♻ Reset My Dashboard', 'artpulse'); ?></button>
    </form>

    <?php $user = wp_get_current_user(); ?>
    <header class="dashboard-topbar">
      <div class="welcome">
        <img src="<?php echo esc_url(get_avatar_url($user->ID, ['size' => 60])); ?>" alt="" />
        <div>
          <h2 class="ap-card__title"><?php printf(__('Welcome back, %s', 'artpulse'), esc_html($user->display_name)); ?></h2>
          <span class="membership-status">
            <?php
            $level = \ArtPulse\ap_user_membership_level($user->ID);
            echo esc_html(sprintf(__('Membership: %s', 'artpulse'), ucfirst($level)));
            ?>
          </span>
          <?php if (\ArtPulse\Core\DashboardController::get_role($user->ID) === 'artist'): ?>
          <?php $public = (bool) get_user_meta($user->ID, 'ap_profile_public', true); ?>
          <label class="ap-profile-toggle">
            <input type="checkbox" id="ap-profile-public" <?php checked($public, true); ?> />
            <?php esc_html_e('Public Profile', 'artpulse'); ?>
          </label>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <div class="ap-dashboard-layout">
      <aside class="ap-dashboard-sidebar">
        <?php
        $show_notifications = true; // Optional logic to toggle certain links
        ap_safe_include('partials/dashboard-nav.php', plugin_dir_path(__FILE__) . 'dashboard-nav.php');
        ?>
      </aside>
      <main class="ap-dashboard-main">
        <?php
        $followed = \ArtPulse\Community\FollowManager::get_user_follows($user->ID, 'artist');
        $follows  = is_array($followed) ? count($followed) : 0;
        $orgs     = \ArtPulse\Community\FollowManager::get_user_follows($user->ID, 'artpulse_org');
        $org_count = is_array($orgs) ? count($orgs) : 0;
        $summary = \ArtPulse\Frontend\EventRsvpHandler::get_rsvp_summary_for_user($user->ID);
        $events  = $summary['going'] ?? 0;
        ?>
        <p><?php printf(__('You\'re following %d artists and %d organizations. You’ve RSVP’d to %d events.', 'artpulse'), $follows, $org_count, $events); ?></p>
        <?php if ($org_count === 0): ?>
          <p><?php esc_html_e('Follow organizations to see more events in your digest.', 'artpulse'); ?></p>
        <?php endif; ?>

<?php
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
        $config  = DashboardWidgetRegistry::get_widget($id, $user_id);
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
      </main>
    </div>
    <?php if (is_user_logged_in() && \ArtPulse\Core\DashboardController::get_role(get_current_user_id()) === 'artist'): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      const cb = document.getElementById('ap-profile-public');
      if(!cb) return;
      cb.addEventListener('change', function(){
        fetch('<?php echo esc_url_raw(rest_url('artpulse/v1/user/profile')); ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
          },
          body: JSON.stringify({ ap_profile_public: cb.checked ? 1 : 0 })
        });
      });
    });
    </script>
    <?php endif; ?>
  </div>
</div>
<?php
get_footer();
?>
