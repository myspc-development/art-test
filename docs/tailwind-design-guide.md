# Tailwind Design Guide

This design guide summarizes core styling conventions used throughout the plugin once Tailwind CSS is enabled. It is referenced from the [Tailwind Upgrade Guide](tailwind_upgrade_guide.md) and acts as a quick reference for building consistent components.

## Layout Patterns

- **Wrapper** – All plugin output should live inside `.ap-wrapper` so styles remain isolated.
- **Flex & Grid** – Use `flex`, `grid`, `gap` and responsive modifiers (`md:`, `lg:`) for layout instead of custom CSS.
- **Spacing** – Prefer `p-*`, `m-*` and `space-y-*` utilities to maintain consistent spacing.
- **Cards** – Standard panels use `bg-white rounded-lg shadow p-4` with optional `hover:shadow-md`.

Example two column layout:
```html
<div class="ap-wrapper grid grid-cols-1 md:grid-cols-2 gap-6">
  <div class="bg-white rounded-lg shadow p-4">Content A</div>
  <div class="bg-white rounded-lg shadow p-4">Content B</div>
</div>
```

## Button & Alert Styles

- **Primary Button** – `bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition`.
- **Secondary Button** – `bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300`.
- **Success Alert** – `bg-green-50 border-l-4 border-green-500 text-green-700 p-4`.
- **Error Alert** – `bg-red-50 border-l-4 border-red-500 text-red-700 p-4`.

## Form Styling

Forms use vertical stacks with consistent input styling:

```html
<form class="space-y-4">
  <label class="block">
    <span class="text-sm font-medium text-gray-700">Name</span>
    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
  </label>
  <label class="block">
    <span class="text-sm font-medium text-gray-700">Email</span>
    <input type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
  </label>
  <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Submit</button>
</form>
```

## Dark Mode (Optional)

When dark mode is enabled, apply Tailwind's `dark:` variants by toggling a `dark` class on the `<body>` element. Example wrapper:

```html
<body class="dark:bg-gray-900 dark:text-gray-100">
  <div class="ap-wrapper p-4">
    ...
  </div>
</body>
```

Use `dark:` utilities for background, text and border colors as needed. Dark mode is optional and can be added incrementally.
