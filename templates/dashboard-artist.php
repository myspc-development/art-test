<?php
$user_role = 'artist';

ap_safe_include('templates/partials/dashboard-nav.php', plugin_dir_path(__FILE__) . 'partials/dashboard-nav.php');

add_action('wp_enqueue_scripts', function () use ($user_role) {
    if (ap_user_can_edit_layout($user_role)) {
        wp_enqueue_script('sortablejs', plugin_dir_url(__FILE__) . '../assets/js/Sortable.min.js', [], '1.15.0', true);
        wp_enqueue_script('user-dashboard-layout', plugin_dir_url(__FILE__) . './assets/js/user-dashboard-layout.js', ['sortablejs'], null, true);
        wp_localize_script('user-dashboard-layout', 'APLayout', [
            'nonce'    => wp_create_nonce('ap_save_user_layout'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
        wp_enqueue_script('dark-mode-toggle', plugin_dir_url(__FILE__) . '../assets/js/dark-mode-toggle.js', [], null, true);
    }
});
?>
<div class="ap-dashboard-wrap <?= esc_attr($user_role) ?>-dashboard">
  <h2 class="ap-card__title"><?= ucfirst($user_role) ?> Dashboard</h2>
  <?php
  ap_safe_include('templates/partials/dashboard-generic.php', plugin_dir_path(__FILE__) . 'partials/dashboard-generic.php');
  ?>
  <div class="ap-quickstart-wrapper">
    <?php
    ap_safe_include('templates/partials/quickstart-artist-guide.php', plugin_dir_path(__FILE__) . 'partials/quickstart-artist-guide.php');
    ?>
  </div>
</div>
