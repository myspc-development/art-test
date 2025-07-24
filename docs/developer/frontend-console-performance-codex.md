---
title: Frontend Console Fixes & Performance Snippets
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Frontend Console Fixes & Performance Snippets

This guide provides small code snippets to avoid common console errors and improve dashboard responsiveness when using WordPress with the Salient theme.

## Force Avatar URLs to HTTPS
Ensure all avatar and profile images load over secure `https://` URLs to prevent mixed content warnings.

```php
// Place in functions.php or a must‑use plugin
add_action('after_setup_theme', function () {
    add_filter('get_avatar_url', function ($url) {
        return $url ? set_url_scheme($url, 'https') : $url;
    });

    // Support Simple Local Avatars
    add_filter('simple_local_avatar_url', function ($url) {
        return $url ? set_url_scheme($url, 'https') : $url;
    });
});
```

## Fix `GridLayout is not defined` Errors
Load the grid layout library before scripts that depend on it and import correctly in your React code.

### Enqueue in WordPress
```php
function ap_enqueue_dashboard_assets() {
    wp_enqueue_script(
        'react-grid-layout',
        'https://unpkg.com/react-grid-layout/dist/react-grid-layout.min.js',
        array('react', 'react-dom'),
        null,
        true
    );
    wp_enqueue_script(
        'ap-dashboard-app',
        plugins_url('build/dashboard-app.js', __FILE__),
        array('react', 'react-dom', 'react-grid-layout'),
        ART_PULSE_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'ap_enqueue_dashboard_assets');
```

### React Usage
```jsx
import GridLayout from 'react-grid-layout';

export default function Dashboard({ children }) {
  return (
    <GridLayout className="layout" cols={12} rowHeight={30} width={1200}>
      {children}
    </GridLayout>
  );
}
```

## Prevent Leaflet `LatLng` NaN Errors
Render maps only when valid coordinates are available.

```php
// Example in a template partial
add_action( 'ap_event_meta', 'ap_event_map' );
function ap_event_map( $event_id ) {
    $lat = get_post_meta( $event_id, 'lat', true );
    $lng = get_post_meta( $event_id, 'lng', true );

    if ( is_numeric( $lat ) && is_numeric( $lng ) ) {
        ?>
        <div id="map" data-lat="<?php echo esc_attr( $lat ); ?>" data-lng="<?php echo esc_attr( $lng ); ?>"></div>
        <?php
    } else {
        echo '<p class="no-location">' . esc_html__( 'Location not available', 'artpulse' ) . '</p>';
    }
}
```

```javascript
// map.js
const mapContainer = document.getElementById('map');
if (mapContainer) {
  const lat = parseFloat(mapContainer.dataset.lat);
  const lng = parseFloat(mapContainer.dataset.lng);
  if (!isNaN(lat) && !isNaN(lng)) {
    const map = L.map(mapContainer).setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
  }
}
```

## Performance Best Practices
- **Batch DOM updates**: modify elements in memory using `DocumentFragment` or batched React state updates before appending to the DOM.
- **Debounce expensive handlers**: use `debounce` for scroll and resize callbacks to avoid thrashing.
- **Prefer vanilla JS**: minimize reliance on jQuery when a native API is available.
- **Use `requestAnimationFrame`**: schedule animations or layout updates inside `requestAnimationFrame` for smoother rendering.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
