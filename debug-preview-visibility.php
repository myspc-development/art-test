<?php
$role = isset($_GET['ap_preview_role']) ? sanitize_key($_GET['ap_preview_role']) : 'member';
$layouts = get_option('artpulse_default_layouts', []);
if (is_string($layouts)) { $tmp=json_decode($layouts,true); if (is_array($tmp)) $layouts=$tmp; }
$ids = isset($layouts[$role]) ? (array)$layouts[$role] : [];
$renderable=$hidden=$missing=[];
foreach ($ids as $id) {
  $slug = \ArtPulse\Core\DashboardWidgetRegistry::canon_slug($id);
  if (!\ArtPulse\Core\DashboardWidgetRegistry::exists($slug)) { $missing[]=$slug; continue; }
  $ok = \ArtPulse\Dashboard\WidgetVisibilityManager::isVisible($slug, get_current_user_id(), $role);
  if ($ok) {
    $renderable[] = $slug;
  } else {
    $hidden[] = $slug;
  }
}
echo json_encode(compact('role','renderable','hidden','missing'), JSON_PRETTY_PRINT)."\n";
