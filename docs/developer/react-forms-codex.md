---
title: React Forms Codex
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# React Forms Codex

This guide explains how to replace legacy PHP form templates with a modern React
implementation. Forms are bundled with Rollup and rendered via a shortcode. A
simple admin‑ajax endpoint processes submissions. Follow these steps when
adding new dynamic forms.

## 1. Component
Create `src/components/ReactForm.jsx` containing the form logic. The example
below submits to the `submit_react_form` action:

```jsx
import React, { useState } from 'react';

const ReactForm = () => {
  const [formData, setFormData] = useState({ name: '', email: '' });
  const [status, setStatus] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setStatus('Submitting...');

    const { ajaxurl, nonce } = window.apReactForm || {};

    const response = await fetch(ajaxurl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'submit_react_form',
        _ajax_nonce: nonce,
        ...formData,
      }),
    });

    const result = await response.json();
    setStatus(result.message || 'Done!');
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        name="name"
        placeholder="Name"
        value={formData.name}
        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
      />
      <input
        type="email"
        name="email"
        placeholder="Email"
        value={formData.email}
        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
      />
      <button type="submit">Submit</button>
      <p>{status}</p>
    </form>
  );
};

export default ReactForm;
```

## 2. Entry point
Bundle the component from `src/index.js`:

```js
import React from 'react';
import { createRoot } from 'react-dom/client';
import ReactForm from './components/ReactForm';

const container = document.getElementById('react-form-root');
if (container) {
  const root = createRoot(container);
  root.render(<ReactForm />);
}
```

## 3. Rollup
Add the following entry to `rollup.config.js` and run `npx rollup -c` to build
`dist/react-form.js`:

```js
createConfig('src/index.js', 'dist/react-form.js', 'APReactForm', {
  react: 'React',
  'react-dom/client': 'ReactDOM',
});
```

Ensure `react` and `react-dom` are installed and `@babel/preset-react` is
available for JSX support.

## 4. Enqueue and Shortcode
In `artpulse-management.php` enqueue the compiled script and register a shortcode
that outputs a container for the React root:

```php
function artpulse_enqueue_react_form() {
    wp_enqueue_script(
        'artpulse-react-form',
        plugin_dir_url(__FILE__) . 'dist/react-form.js',
        ['wp-element'],
        '1.0.0',
        true
    );
    wp_localize_script(
        'artpulse-react-form',
        'apReactForm',
        [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('ap_react_form'),
        ]
    );
}
add_action('wp_enqueue_scripts', 'artpulse_enqueue_react_form');

function artpulse_render_react_form() {
    return '<div id="react-form-root"></div>';
}
\ArtPulse\Core\ShortcodeRegistry::register('react_form', 'React Form', 'artpulse_render_react_form');
```

The localized `apReactForm` object exposes `ajaxurl` and a security
`nonce` that must be sent with each AJAX request.

Use `[react_form]` in any post or page to display the form.

## 5. AJAX handler
Add an admin‑ajax endpoint that processes the form values. The handler
verifies the `ap_react_form` nonce sent by the component:

```php
function artpulse_handle_react_form() {
    check_ajax_referer('ap_react_form');
    $name  = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');

    // Process the data (save to DB, send email, etc.)

    wp_send_json_success(['message' => 'Form submitted successfully!']);
}
add_action('wp_ajax_submit_react_form', 'artpulse_handle_react_form');
add_action('wp_ajax_nopriv_submit_react_form', 'artpulse_handle_react_form');
```

You may alternatively register a REST route instead of using admin‑ajax. The
React component can fetch `/wp-json/your-namespace/v1/submit-form` for improved
performance and structure.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
