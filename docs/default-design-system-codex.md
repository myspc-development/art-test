# Default Design System Codex

This codex explains how to implement, use, and extend the Default Design System Recommendation for your WordPress dashboard plugin. Follow it as an internal README for contributors and as a step‑by‑step guide when you scaffold new widgets.

1. **Folder Structure**

```
plugin-root/
├─ src/
│  ├─ css/
│  │  ├─ tokens.css
│  │  └─ widgets.scss
│  └─ js/
│     └─ dashboard.js
├─ build/
│  ├─ css/
│  └─ js/
├─ blocks/
│  └─ widget-foo/
│     ├─ block.json
│     ├─ edit.jsx
│     ├─ render.php
│     └─ style.scss
├─ inc/
│  ├─ dashboard-registry.php
│  └─ helpers.php
├─ templates/
│  └─ card-wrapper.php
└─ package.json
```

2. **Design Tokens (`src/css/tokens.css`)**

```css
:root {
  --ap-space: 1rem;
  --ap-radius: 0.75rem;
  --ap-border-color: #e0e0e0;
  --ap-surface: #ffffff;
  --ap-muted: #6c7077;
  --ap-accent: #007cba;
  --ap-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
}

@media (prefers-color-scheme: dark) {
  :root {
    --ap-surface: #1e1f24;
    --ap-border-color: #333;
    --ap-muted: #a0a0a0;
  }
}

/* Import this file at the top of all SCSS modules */
@use "../css/tokens.css";
```

3. **Global Layout & Card Styles (`src/css/widgets.scss`)**

```css
.ap-dashboard-grid {
  display: grid;
  gap: var(--ap-space);
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  align-items: start;
}

.ap-card {
  background: var(--ap-surface);
  border: 1px solid var(--ap-border-color);
  border-radius: var(--ap-radius);
  padding: var(--ap-space);
  box-shadow: var(--ap-shadow);
}

.ap-card__title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 calc(var(--ap-space) * 0.75);
}

.ap-card:where(:hover, :focus-visible) {
  box-shadow: 0 2px 4px rgb(0 0 0 / 0.07);
}
```

**Important:** All widgets must use `.ap-card` for layout and `.ap-card__title` for headings. No inline styles or custom class variants should override these base styles.

4. **Build Setup (`package.json`)**

```json
{
  "scripts": {
    "start": "wp-scripts start",
    "build": "wp-scripts build"
  },
  "devDependencies": {
    "@wordpress/scripts": "^30.0.0"
  }
}
```

Run `npm run start` to auto-watch files. Commit only compiled `build/` output.

5. **PHP Dashboard Registry Helper (`inc/dashboard-registry.php`)**

```php
function ap_register_dashboard_widget( array $args ) {
    $defaults = [
        'id'       => '',
        'title'    => '',
        'render'   => '',
        'cap'      => 'read',
    ];
    $args = wp_parse_args( $args, $defaults );

    if ( ! current_user_can( $args['cap'] ) ) return;

    global $wp_meta_boxes;

    $wp_meta_boxes['dashboard']['normal']['core'][ $args['id'] ] = [
        'id'     => $args['id'],
        'title'  => $args['title'],
        'callback' => function () use ( $args ) {
            echo '<div class="ap-card" role="region" aria-labelledby="' . esc_attr( $args['id'] ) . '-title">';
            echo '<h2 id="' . esc_attr( $args['id'] ) . '-title" class="ap-card__title">' . esc_html( $args['title'] ) . '</h2>';
            if ( is_callable( $args['render'] ) ) {
                call_user_func( $args['render'] );
            } else {
                locate_template( $args['render'], true );
            }
            echo '</div>';
        },
    ];
}

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( 'index.php' !== $hook ) return;
    wp_enqueue_style( 'ap-dashboard', plugins_url( '../build/css/widgets.css', __FILE__ ), [], '1.0' );
} );
```

6. **Dashboard JS (`src/js/dashboard.js`)**

```javascript
import { on } from '@wordpress/dom-ready';

on(() => {
  document.querySelectorAll('.ap-card').forEach(card => {
    const toggle = document.createElement('button');
    toggle.className   = 'dashicons dashicons-arrow-down';
    toggle.ariaLabel   = 'Collapse widget';
    toggle.onclick     = () => {
      card.classList.toggle('is-collapsed');
      localStorage.setItem(card.id + ':collapsed', card.classList.contains('is-collapsed'));
    };
    card.prepend(toggle);

    if (localStorage.getItem(card.id + ':collapsed') === 'true') {
      card.classList.add('is-collapsed');
    }
  });
});
```

7. **Widget Block Template (`blocks/widget-foo/`)**

See the example block in this folder for how to wire up a dashboard widget block with build files for scripts and styles.

8. **Accessibility Checklist**

- Use `role="region"` and `aria-labelledby` for all widget containers.
- Apply `:focus-visible` outlines on interactive elements.
- Maintain at least 4.5:1 color contrast.
- Provide descriptive `aria-label` values on icons (collapse, drag handles, etc.).

9. **Theming and Overrides**

```css
:root {
  --ap-accent: #ff006e;
  --ap-surface: #fafafa;
}
```

Enqueue these overrides after the core styles to update default tokens.

**Final Setup Completion Instructions**

- Audit each widget and replace old classes with `.ap-card`.
- Enqueue `build/css/widgets.css` globally.
- Standardize PHP widgets via `ap_register_dashboard_widget()`.
- Remove inline styles and hardcoded padding/margins.
- Test in wp-admin to confirm cards look uniform.

```
Once these instructions are followed, your dashboard UI will be fully unified and easily themeable across all widgets.
```
