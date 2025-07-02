# Tailwind Styling Upgrade Guide for ArtPulse Plugin

## ✨ Overview
This guide outlines the complete process for upgrading the ArtPulse WordPress plugin to use Tailwind CSS for modern, responsive, and maintainable styling.

---

## 🔎 Phase 1: Setup & Integration

### 1. Install Tailwind CSS and Dependencies
```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init
```

### 2. Configure Tailwind
**tailwind.config.js**
```js
module.exports = {
  content: [
    './src/**/*.php',
    './assets/**/*.js',
    './templates/**/*.php',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};
```

### 3. Create Tailwind Entry CSS File
**assets/css/style.css**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### 4. Add Build Script
**package.json**
```json
"scripts": {
  "build:css": "tailwindcss -i ./assets/css/style.css -o ./assets/css/tailwind.css --minify"
}
```

Run the build:
```bash
npm run build:css
```

### 5. Enqueue Styles in Plugin
Add to `artpulse-management.php`:
```php
function ap_enqueue_tailwind_styles() {
    wp_enqueue_style(
        'ap-tailwind',
        plugin_dir_url(__FILE__) . 'assets/css/tailwind.css',
        [],
        ARTPULSE_VERSION
    );
}
add_action('wp_enqueue_scripts', 'ap_enqueue_tailwind_styles');
```

---

## 🌎 Phase 2: Styling Strategy & Isolation

### 6. Define Wrapper & Guidelines
- Use `.ap-wrapper` to scope plugin styles
- Favor `flex`, `grid`, `gap`, `rounded`, `shadow`, `text-*`
- Apply utility-first classes

### 7. Button Example
```html
<button class="bg-blue-600 text-white rounded px-4 py-2 hover:bg-blue-700 transition">Submit</button>
```

Document in: `docs/ui-ux-polish-codex.md`

---

## 🏠 Phase 3: Migrate Components

### 8. Prioritize Migration Order
Start with frequently used shortcodes:
- `[ap_event_calendar]`
- `[ap_user_dashboard]`
- `[ap_events_slider]`
- `[ap_login]`

### 9. Update Output HTML
Before:
```php
echo '<div class="event-box">';
```
After:
```php
echo '<div class="bg-white p-4 rounded-lg shadow ap-wrapper">';
```

---

## 🧠 Phase 4: Preview Mode for Testing

### 10. Add a Toggle in Settings
Allow switching between legacy and Tailwind styles:
- On: Load `tailwind.css`
- Off: Fallback to original styles

---

## 🚀 Phase 5: Optimization

### 11. Enable PurgeCSS
Update `tailwind.config.js`:
```js
content: ['./src/**/*.php', './templates/**/*.php']
```
Rebuild:
```bash
npm run build:css
```

---

## 📚 Phase 6: Documentation

### 12. Add Tailwind Design Guide
New file: `docs/tailwind-design-guide.md`
Include:
- Layout patterns
- Button/alert components
- Form styling
- Dark mode strategy (optional)

---

## ✅ Summary

| Step | Deliverable |
|------|-------------|
| Tailwind installed | ✅ |
| Enqueue in plugin | ✅ |
| Shortcodes migrated | ⏳ In progress |
| Codex updated | ✅ |
| Preview mode | ✅ |
| Purged CSS | ✅ |
| Internal guide | ✅ |
