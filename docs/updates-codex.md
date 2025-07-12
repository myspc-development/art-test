# Updates Tab Codex

This guide outlines how the plugin's Updates tab downloads new versions from GitHub. Administrators can choose between a ZIP based update or pulling directly via git.

## Settings
- **GitHub Repo** – value in `owner/repo` format.
- **Update Method** – `zip`, `git` or `auto`.
- **Auto-Update** – if enabled, a daily cron job calls the updater.

## Update Flow
1. `UpdatesTab::run_update()` calls a private `do_update()` helper.
2. `do_update()` decides whether to call `git_pull_update()` or `zip_release_update()` based on settings and whether a `.git` folder exists.
3. `zip_release_update()` fetches the latest release from the GitHub API, downloads the ZIP and replaces the plugin files.
4. `git_pull_update()` executes `git pull` in the plugin directory.

```php
private static function do_update(): bool|\WP_Error {
    $method = get_option('artpulse_settings')['update_method'] ?? 'auto';
    $repo   = get_option('artpulse_settings')['github_repo'] ?? '';
    $is_git = is_dir(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . '.git');
    if ($method === 'git' || ($method === 'auto' && $is_git)) {
        return self::git_pull_update();
    }
    return self::zip_release_update($repo);
}
```

Both update methods return `true` on success or `WP_Error` on failure. Results are written to the `ap_update_log` option and surfaced in the Updates tab UI.
