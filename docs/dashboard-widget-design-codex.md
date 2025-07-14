# Dashboard Widget Design Codex

This guide outlines the standard layout and styling for widgets shown in the ArtPulse dashboards. Use these conventions when adding or extending widgets so that the UI remains consistent across admin and front‑end screens.

## 1. Widget Markup

Widgets use a flexible card structure. Each widget callback should output content inside the following markup:

```html
<div class="ap-widget-card">
  <div class="ap-widget-header">
    <span class="ap-widget-icon">📊</span>
    <h3 class="ap-widget-title">Widget Name</h3>
    <div class="ap-widget-controls">
      <button class="widget-toggle" title="Toggle visibility">👁</button>
      <button class="widget-remove" title="Remove widget">✖</button>
    </div>
  </div>
  <div class="ap-widget-content">
    <!-- callback content -->
  </div>
</div>
```

The header icons and controls are optional. Widgets rendered in preview mode omit the controls entirely.

## 2. Core Styling

`assets/css/dashboard-widget.css` defines the base card styles:

```css
.ap-widget-card {
  background: #fff;
  border: 1px solid #ddd;
  padding: 16px;
  border-radius: 6px;
  margin-bottom: 16px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  transition: background 0.2s ease;
}
.ap-widget-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.ap-widget-icon { font-size: 20px; margin-right: 8px; }
.ap-widget-title { font-size: 16px; font-weight: 600; margin: 0; flex-grow: 1; }
.ap-widget-controls button { margin-left: 8px; background: none; border: none; cursor: pointer; font-size: 14px; }
.ap-widget-content { margin-top: 12px; font-size: 14px; line-height: 1.5; }
```

When the Salient theme is active the variables `--ap-bg-color`, `--ap-border-color` and `--ap-primary-color` are provided so the cards inherit the site palette.

## 3. Theme Integration

If your theme exposes utility classes (for example Tailwind or Salient helpers) you may replace the hard‑coded styles with their class equivalents. CSS variables allow the plugin to adapt automatically to light or dark schemes.

## 4. Tags and Categories

Widgets can show a category label:

```html
<span class="ap-widget-tag">Engagement</span>
```

```css
.ap-widget-tag {
  background: #f0f0f0;
  border-radius: 4px;
  padding: 2px 6px;
  font-size: 11px;
  margin-left: 8px;
  color: #555;
}
```

## 5. Preview Dashboard

The preview area displayed in the widget editor also uses `.ap-widget-card` markup but omits hover actions. Keep spacing minimal so multiple widgets can be viewed at once.
