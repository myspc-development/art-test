---
title: ArtPulse Codex: Implementing WordPress Menus with Shortcodes
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Implementing WordPress Menus with Shortcodes

This guide explains how to use WordPress' built‑in menu system to navigate pages that display ArtPulse shortcodes.

## Prerequisites

- ArtPulse plugin is installed and active
- WordPress administrator access
- **Recommended:** install the [User Menus plugin](https://wordpress.org/plugins/user-menus/) if you want role‑based visibility

## 1. Create Pages for Shortcodes

Create a WordPress page for each ArtPulse shortcode and paste the shortcode into the editor. Publish the page.

| Page Title | Shortcode |
|------------|-----------|
| Member Dashboard | `[ap_user_dashboard]` |
| Artist Dashboard | `[ap_artist_dashboard]` |
| Organization Dashboard | `[ap_org_dashboard]` |
| Submit Event | `[ap_submit_event]` |
| Edit Profile | `[ap_profile_edit]` |
| Notifications | `[ap_notifications]` |

## 2. Build a WordPress Navigation Menu

1. Go to **Appearance → Menus**.
2. Create a new menu, for example “Sidebar Navigation.”
3. Add the pages from Step&nbsp;1 to the menu and arrange them as needed.
4. Save the menu.

## 3. Display the Menu in the Sidebar

### Classic themes
1. Navigate to **Appearance → Widgets**.
2. Add a **Navigation Menu** widget to your sidebar.
3. Select the menu you created.

### Block themes (FSE)
1. Open **Appearance → Editor → Templates → Sidebar**.
2. Insert a **Navigation** block.
3. Choose the menu from the dropdown.

## 4. Restrict Menu Items by Role *(Optional)*

If you installed the User Menus plugin, each menu item has a **Who can see this link?** option. Use it to limit visibility to logged‑in users or specific roles such as **Artist**, **Organization** or **Subscriber**.

## Optional Cleanup

To hide the plugin’s own menu entry from non‑administrators, place this snippet in a child theme or small custom plugin:

```php
function hide_artpulse_plugin_menu() {
    if ( ! current_user_can( 'manage_options' ) ) {
        remove_menu_page( 'artpulse-settings' );
    }
}
add_action( 'admin_menu', 'hide_artpulse_plugin_menu', 99 );
```

## Switch to WordPress Menus

The plugin normally loads a React sidebar menu. To disable it and rely solely on
your WordPress navigation menu you can enable **Use WordPress Navigation Menu**
under **ArtPulse → Settings → General**. The same effect can be achieved with
this filter in a small plugin or `functions.php`:

```php
add_filter( 'ap_use_wp_nav_menu', '__return_true' );
```

When the filter returns `true`, `SidebarMenu.jsx` is not enqueued and the React
dashboard receives `useWpNavMenu` set to `true`.

## Result

- Dashboards and forms still use ArtPulse shortcodes.
- Navigation is powered by WordPress menus.
- Role‑based visibility works via the User Menus plugin.
