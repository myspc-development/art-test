# Default Design System Codex

This codex explains how to implement, use, and extend the Default Design System Recommendation for your WordPress dashboard plugin. Follow it as an internal README for contributors and as a step‑by‑step guide when you scaffold new widgets.

## 1. Folder Structure

```text
plugin-root/
├─ src/
│  ├─ css/
│  │  ├─ tokens.css
│  │  ├─ dashboard.css      # ✅ Unified Dashboard Styling (NEW)
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
│  └─ helpers.php
├─ templates/
│  └─ card-wrapper.php
└─ package.json
```

## 2. Design Tokens (`src/css/tokens.css`)

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
```

Import this file at the top of all SCSS modules:

```scss
@use "../css/tokens.css";
```

### 2.1 Unified Dashboard Styling (`src/css/dashboard.css`)

```css
@import "./tokens.css";

.ap-dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: var(--ap-space);
  align-items: start;
}

.ap-card {
  background-color: var(--ap-surface);
  border: 1px solid var(--ap-border-color);
  border-radius: var(--ap-radius);
  padding: var(--ap-space);
  box-shadow: var(--ap-shadow);
  transition: box-shadow 0.2s ease-in-out;
}

.ap-card:where(:hover, :focus-within) {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.07);
}

.ap-card__title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
}

.ap-muted {
  color: var(--ap-muted);
  font-size: 0.875rem;
}

.ap-card__button {
  background-color: var(--ap-accent);
  color: #fff;
  border: none;
  border-radius: var(--ap-radius);
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background-color 0.2s ease-in-out;
}

.ap-card__button:hover {
  background-color: #0064a6;
}
```

Compile the styles with `npm run build` which outputs `build/css/dashboard.css`.

Enqueue globally:

```php
wp_enqueue_style( 'ap-dashboard', plugins_url( '../build/css/dashboard.css', __FILE__ ), [], '1.0' );
```

This ensures all dashboards use the same grid layout and card styling system.

## 3. Global Layout & Card Styles (`src/css/widgets.scss`)

```scss
@import "./tokens.css";
@import "./dashboard.css";
```

Only widget-specific overrides or utility classes should go here. All shared layout components now come from `dashboard.css`.

## 4. Build Setup (`package.json`)

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

## 5. Final Setup Completion

Follow these actions to implement the system:

1. **Migrate markup to use shared CSS classes**

   Replace old wrappers like `.dashboard-overview-card`, `.widget-box`, or `.card` with:

   ```html
   <div class="ap-card">
     <h2 class="ap-card__title">Widget title</h2>
     ...
   </div>
   ```

   Wrap collections in:

   ```html
   <div class="ap-dashboard-grid">
     <!-- Cards go here -->
   </div>
   ```

2. **Remove inline styles**

   Delete any `style="padding:..."`, `style="border:..."`, etc. All layout/spacing must come from CSS classes.

3. **Audit dashboards**

   Ensure templates like `dashboard-artist.php`, `dashboard-member.php`, etc.:

   - Use `.ap-dashboard-grid`
   - Contain only `.ap-card` widgets
   - Include `widgets.css` or `dashboard.css`

4. **Recompile styles**

   ```bash
   npm run build
   ```

   Make sure the output CSS reflects `dashboard.css` changes.
