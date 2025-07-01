# Tailwind Integration

## Installation & Setup

### Install Tailwind and Dependencies

From the project root:

npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init

### Create Your Tailwind CSS Entry File

Create (or edit) assets/css/tailwind.css and add:

@tailwind base;
@tailwind components;
@tailwind utilities;

### Update Your Build Process

Configure your bundler (Webpack, Vite, etc.) to process Tailwind’s CSS file and output to `assets/css/tailwind.css` so it matches the plugin's existing structure.

Update tailwind.config.js to include all relevant file paths:

content: [
  "./assets/js/**/*.{js,jsx,ts,tsx}",
  "./templates/**/*.php",
  "./src/Frontend/**/*.php"
]

### Example Tailwind CLI Script

For a simple setup you can use the Tailwind CLI directly. Add a script to
`package.json`:

```json
"scripts": {
  "watch:tailwind": "npx tailwindcss -i assets/css/tailwind.css -o assets/css/tailwind.css --watch"
}
```

Run it during development with:

```bash
npm run watch:tailwind
```

### Enqueue the Compiled CSS in WordPress

In your plugin's main file, enqueue the compiled stylesheet:

```php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'ap-tailwind',
        plugin_dir_url(__FILE__) . 'public/tailwind.css',
        [],
        '1.0'
    );
});
```

## Refactoring Dashboard Pages

### Step-by-Step Checklist

- Audit existing dashboard templates and note legacy CSS classes.
- Replace layout wrappers with Tailwind flex or grid utilities.
- Convert common widgets to partials using Tailwind components.
- Remove deprecated stylesheets once Tailwind equivalents are in place.
- Verify responsive behavior and accessibility on each page.

## Codex: Tailwind Conventions for Devs

Prefer Tailwind utility classes for all layout and UI.

Use bg-white rounded-2xl shadow p-6 for dashboard cards.

Use text-lg font-semibold mb-2 for section headings.

For navigation, use flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-indigo-50.

For primary buttons, use bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700.

Keep layout responsive using Tailwind’s grid/flex and breakpoints (md:, lg:).

Always test for accessibility (skip links, keyboard nav, contrast).

Do not use Bootstrap or legacy SCSS/CSS in new or refactored code.

## Troubleshooting

If styles aren’t applying, check that:

The build process is running and outputting the CSS

Paths in tailwind.config.js cover all your templates/components

PurgeCSS isn’t too aggressive (adjust content paths if needed)

## Resources

Tailwind CSS Documentation

Heroicons for icons

shadcn/ui for reusable React components
