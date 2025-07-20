---
title: User Dashboard Guide
category: user
role: user
last_updated: 2025-07-20
status: draft
---

# Implementation Guide for Codex

The following instructions outline how to enable drag-and-drop layout editing for user dashboards using SortableJS.

## 1. Add SortableJS to the User Dashboard

```php
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'index.php') {
        wp_enqueue_script('sortablejs', plugin_dir_url(__FILE__) . 'assets/libs/sortablejs/Sortable.min.js', [], '1.15.0', true);
        wp_enqueue_script('user-dashboard-layout', plugin_dir_url(__FILE__) . 'assets/js/user-dashboard-layout.js', ['sortablejs'], '1.0', true);
        wp_localize_script('user-dashboard-layout', 'APLayout', [
            'nonce' => wp_create_nonce('ap_save_user_layout'),
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }
});
```

## 2. Create `user-dashboard-layout.js`

```js
document.addEventListener('DOMContentLoaded', () => {
  const list = document.querySelector('#ap-user-dashboard');

  if (!list) return;

  Sortable.create(list, {
    animation: 150,
    onEnd: () => {
      const layout = Array.from(list.children).map(el => ({
        id: el.dataset.id,
        visible: true
      }));

      fetch(APLayout.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'ap_save_user_layout',
          nonce: APLayout.nonce,
          layout
        })
      });
    }
  });
});
```

## 3. Handle Save in PHP (AJAX)

```php
add_action('wp_ajax_ap_save_user_layout', function () {
    check_ajax_referer('ap_save_user_layout', 'nonce');

    $layout = json_decode(file_get_contents('php://input'), true)['layout'] ?? [];
    $user_id = get_current_user_id();

    if ($user_id && is_array($layout)) {
        \ArtPulse\Admin\UserLayoutManager::save_user_layout($user_id, $layout);
        wp_send_json_success();
    }

    wp_send_json_error();
});
```

## 4. Render Widgets in a Draggable Container

```php
echo '<div id="ap-user-dashboard">';
foreach ($layout as $widget) {
    if (!$widget['visible']) continue;
    echo '<div class="ap-widget-card" data-id="' . esc_attr($widget['id']) . '">';
    call_user_func($registry->get($widget['id'])['callback']);
    echo '</div>';
}
echo '</div>';
```

## 5. Style `.ap-widget-card`

```css
.ap-widget-card {
  background: #fff;
  padding: 16px;
  margin-bottom: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
```

## 🧠 Summary

| Feature | Admin | Users |
| --- | --- | --- |
| Drag and drop | ✅ Yes (SortableJS) | ✅ Add JS + handler |
| Styled widgets | ✅ `.ap-widget-card` | ✅ Use same |
| Layout saving | ✅ Per role | ✅ Per user via AJAX |
| Fallback logic | ✅ Fully implemented | ✅ Working |