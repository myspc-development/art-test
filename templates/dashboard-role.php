<?php
if (!defined('AP_DASHBOARD_RENDERING')) {
    $role = isset($_GET['ap_preview_role']) ? sanitize_key($_GET['ap_preview_role']) : null;
    ap_render_dashboard($role ? [$role] : []);
    return;
}

?>
<?php
static $first_panel = true;
$is_first = $first_panel;
$dashboard_v2 = function_exists('ap_dashboard_v2_enabled') ? ap_dashboard_v2_enabled() : true;
if ($is_first && $dashboard_v2) {
    ap_safe_include('partials/dashboard-role-tabs.php', plugin_dir_path(__FILE__) . 'partials/dashboard-role-tabs.php');
    ap_safe_include('partials/dashboard-nav.php', plugin_dir_path(__FILE__) . 'partials/dashboard-nav.php');
}
$is_active = $first_panel;
$first_panel = false;
?>
<section class="ap-role-layout" role="tabpanel" id="ap-panel-<?php echo esc_attr($user_role ?? ''); ?>" aria-labelledby="ap-tab-<?php echo esc_attr($user_role ?? ''); ?>" data-role="<?php echo esc_attr($user_role ?? ''); ?>"<?php echo $is_active ? '' : ' hidden'; ?>>
  <?php
  $sections = [
      'overview' => [
          'label'   => __('Overview', 'artpulse'),
          'content' => function () use ($user_role, $dashboard_v2) {
              echo '<div id="ap-dashboard-root" class="ap-dashboard-grid" role="grid" aria-label="' . esc_attr__('Dashboard widgets', 'artpulse') . '" data-role="' . esc_attr($user_role ?? '') . '" data-ap-v2="' . ($dashboard_v2 ? '1' : '0') . '"></div>';
          },
      ],
  ];

  $events_tpl   = plugin_dir_path(__FILE__) . 'partials/dashboard-events.php';
  $payments_tpl = plugin_dir_path(__FILE__) . 'partials/dashboard-payments.php';
  if (file_exists($events_tpl)) {
      $sections['activity'] = [
          'label'    => __('Activity', 'artpulse'),
          'template' => $events_tpl,
      ];
  }
  if (file_exists($payments_tpl)) {
      $sections['payments'] = [
          'label'    => __('Payments', 'artpulse'),
          'template' => $payments_tpl,
      ];
  }

  if ($sections) {
      echo '<nav class="ap-local-nav" aria-label="' . esc_attr__('Dashboard sections', 'artpulse') . '">';
      foreach ($sections as $id => $info) {
          echo '<a href="#' . esc_attr($id) . '">' . esc_html($info['label']) . '</a>';
      }
      echo '</nav>';

      foreach ($sections as $id => $info) {
          echo '<section id="' . esc_attr($id) . '" class="ap-dashboard-section">';
          if (isset($info['template'])) {
              ap_safe_include('partials/' . basename($info['template']), $info['template']);
          } else {
              $info['content']();
          }
          echo '</section>';
      }
  }
  ?>
</section>
