# ArtPulse Plugin Developer Codex: Full Settings Page with Tabs

This guide shows how to build a comprehensive settings page in the WordPress dashboard. Administrators can manage multiple feature areas using tabs.

## 1. Hook into the Admin Menu

```php
add_action('admin_menu', 'artpulse_register_settings_page');

function artpulse_register_settings_page() {
    add_menu_page(
        'ArtPulse Settings',
        'ArtPulse',
        'manage_options',
        'artpulse-settings',
        'artpulse_render_settings_page',
        'dashicons-art',
        60
    );
}
```

## 2. Render the Settings Page with Tabs

```php
function artpulse_render_settings_page() {
    $tabs = [
        'general'  => 'General',
        'tagging'  => 'Auto-Tagging',
        'sync'     => 'Woo Sync',
        'widgets'  => 'Widgets',
        'api'      => 'API Keys'
    ];

    $active_tab = $_GET['tab'] ?? 'general';

    echo '<div class="wrap">';
    echo '<h1>ArtPulse Settings</h1>';
    echo '<h2 class="nav-tab-wrapper">';

    foreach ($tabs as $slug => $label) {
        $class = ($active_tab === $slug) ? ' nav-tab-active' : '';
        echo "<a href='?page=artpulse-settings&tab=$slug' class='nav-tab$class'>$label</a>";
    }

    echo '</h2>';

    switch ($active_tab) {
        case 'general':
            include plugin_dir_path(__FILE__) . 'admin/tabs/general.php';
            break;
        case 'tagging':
            include plugin_dir_path(__FILE__) . 'admin/tabs/tagging.php';
            break;
        case 'sync':
            include plugin_dir_path(__FILE__) . 'admin/tabs/sync.php';
            break;
        case 'widgets':
            include plugin_dir_path(__FILE__) . 'admin/tabs/widgets.php';
            break;
        case 'api':
            include plugin_dir_path(__FILE__) . 'admin/tabs/api.php';
            break;
    }

    echo '</div>';
}
```

## 3. Create Tab Files

Each tab file lives under `admin/tabs/` and outputs its own form.
Example for `admin/tabs/general.php`:

```php
<form method="post" action="options.php">
    <?php
        settings_fields('artpulse_general');
        do_settings_sections('artpulse_general');
        submit_button();
    ?>
</form>
```

## 4. Register Settings

Create a registration file such as `includes/settings-register.php` and hook into `admin_init` to register fields:

```php
add_action('admin_init', 'artpulse_register_general_settings');

function artpulse_register_general_settings() {
    register_setting('artpulse_general', 'artpulse_option_site_mode');

    add_settings_section(
        'artpulse_general_section',
        'General Configuration',
        null,
        'artpulse_general'
    );

    add_settings_field(
        'site_mode',
        'Site Mode',
        'artpulse_render_site_mode_field',
        'artpulse_general',
        'artpulse_general_section'
    );
}

function artpulse_render_site_mode_field() {
    $value = get_option('artpulse_option_site_mode', 'default');
    echo "<input type='text' name='artpulse_option_site_mode' value='" . esc_attr($value) . "' />";
}
```

## 5. File Organization

```
artpulse-management.php
includes/
└── settings-register.php
admin/
└── tabs/
    ├── general.php
    ├── tagging.php
    ├── sync.php
    ├── widgets.php
    └── api.php
```

### Best Practices

* Sanitize all inputs with `sanitize_text_field()` or other appropriate functions.
* Use nonces if switching to custom POST handlers.
* Wrap settings logic in role checks using `current_user_can()`.
* Group related options into arrays if you have many fields.

### Dynamic Tab Hooks

When the settings page loads the plugin triggers a dynamic action based on the
active tab. Use this to render custom tab content or enqueue assets. The hook
name follows the pattern `artpulse_render_settings_tab_{slug}` where `{slug}` is
the tab identifier.

```php
add_action( 'artpulse_render_settings_tab_widgets', function () {
    echo '<p>Widgets tab content</p>';
} );

// DashboardWidgetTools loads its admin interface when this hook fires
// and includes `templates/admin/settings-tab-widgets.php`.
```
