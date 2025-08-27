<?php
/**
 * Template Name: Simple Dashboard
 *
 * Minimal template rendering widgets for the current user's role.
 */

use ArtPulse\Core\DashboardPresets;
use ArtPulse\Core\WidgetRegistry;

if (!is_user_logged_in()) {
    return;
}

$role = get_query_var('ap_role');
if (!in_array($role, ['member', 'artist', 'organization'], true)) {
    $role = 'member';
}

$user_id    = get_current_user_id();
$slugs      = DashboardPresets::forRole($role);
$validSlugs = WidgetRegistry::ids();
$slugs      = array_values(array_intersect($slugs, $validSlugs));
$context    = ['user_id' => $user_id];

?>
<section
  class="ap-role-layout"
  role="tabpanel"
  id="ap-panel-<?php echo esc_attr($role); ?>"
  aria-labelledby="ap-tab-<?php echo esc_attr($role); ?>"
  data-role="<?php echo esc_attr($role); ?>"
>
<?php
foreach ($slugs as $slug) {
    $html = WidgetRegistry::render($slug, $context);
    if ($html !== '') {
        echo $html;
    }
}
?>
</section>

