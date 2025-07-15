# Updates Tab Codex

This guide outlines how the plugin's Updates tab downloads new versions from GitHub. Updates now always use the release ZIP and no longer execute git commands.

## Settings
- **GitHub Repo URL** – full repository link such as
  `https://github.com/your-org/artpulse-plugin`.
- **Auto-Update** – if enabled, a daily cron job calls the updater.
  Leaving the URL blank disables update checks.

## Update Flow
1. `UpdatesTab::run_update()` calls a private `do_update()` helper.
2. `do_update()` simply calls `zip_release_update()` to download the latest GitHub release ZIP.
3. `zip_release_update()` fetches the latest release from the GitHub API, downloads the ZIP and replaces the plugin files using the WordPress filesystem API.

```php
private static function do_update(): bool|\WP_Error {
    $repo = get_option('ap_github_repo_url');
    return self::zip_release_update($repo);
}
```

The method returns `true` on success or `WP_Error` on failure. Results are written to the `ap_update_log` option and surfaced in the Updates tab UI.
