
 <?php
 if (function_exists('ap_dashboard_v2_enabled') && !ap_dashboard_v2_enabled()) {
     return;
 }
 $roles = array_intersect(['member','artist','organization'], wp_get_current_user()->roles);
 if (count($roles) <= 1) {
     return; // no tabs needed
 }
 $labels = [
     'member'       => __('Member Tools', 'artpulse'),
     'artist'       => __('Artist Tools', 'artpulse'),
     'organization' => __('Org Tools', 'artpulse'),
 ];
-$icons = [
-    'member' => '<svg viewBox="0 0 24 24" fill="none" stroke="cu...="12" cy="7" r="4"/><path d="M5.5 21a10 10 0 0 1 13 0"/></svg>',
-    'artist' => '<svg viewBox="0 0 24 24" fill="none" stroke="cu...10" r="1"/><path d="M8 16c1.333-1.333 4.667-1.333 6 0"/></svg>',
-    'organization' => '<svg ...>',
-];
+$icons = [
+    'member' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><circle cx="12" cy="7" r="4" fill="currentColor"/><path d="M4 21c2-3 5-5 8-5s6 2 8 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
+    'artist' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M3 21l7-7 2 2 7-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="19" cy="5" r="2" fill="currentColor"/></svg>',
+    'organization' => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M3 21V9l9-6 9 6v12H3z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M9 21v-6h6v6" fill="none" stroke="currentColor" stroke-width="2"/></svg>',
+];
 ?>
-<div class="ap-role-tabs" role="tablist">
+<div class="ap-role-tabs" role="tablist" aria-label="<?php echo esc_attr__('Switch role view', 'artpulse'); ?>">
   <?php
   $first = true;
   foreach ($roles as $role):
     $is_active = $first;
     $first = false;
   ?>
     <button
       type="button"
       class="ap-role-tab<?= $is_active ? ' active' : '' ?>"
       role="tab"
       id="ap-tab-<?php echo esc_attr($role); ?>"
       aria-controls="ap-panel-<?php echo esc_attr($role); ?>"
       aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
       tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
       data-role="<?php echo esc_attr($role); ?>">
       <?php if (isset($icons[$role])): ?>
         <span class="ap-role-icon" aria-hidden="true"><?php echo $icons[$role]; ?></span>
       <?php endif; ?>
       <span class="ap-role-label"><?php echo esc_html($labels[$role] ?? ucfirst($role)); ?></span>
     </button>
   <?php endforeach; ?>
 </div>
