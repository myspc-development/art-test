## ✅ Codex Sprint Modules

- [Sprint 1: Mapping + Q&A](./codex-sprint-1-map-qa.md)
- [Sprint 2: Donations + Calendar](./codex-sprint-2-donations-calendar.md)
- [Sprint 3: Event Ranking + API](./codex-sprint-3-ranking-api.md)
- [Sprint 4: Polish + Documentation](./codex-sprint-4-polish-docs.md)
- [Sprint L1: Embeddable Widgets](./codex-sprint-l1-embeddable-widgets.md)
- [Widget Chapters](./codex-chapters/README.md)
- [User Dashboard Customization](./user-dashboard-customization.md)
- [ArtPulse Member Dashboard Roadmap](./ArtPulse_Member_Dashboard_Roadmap.md)
- [Organizer Dashboard Roadmap](./Organizer_Dashboard_Roadmap.md)
- [Admin Dashboard Widgets Editor Guide](./Admin_Dashboard_Widgets_Editor_Guide.md)
- [Dashboard Widget Editor Codex](./dashboard-widget-editor-codex.md)



- [Phase F: Launch, Feedback & Scale](./codex-phase-f-launch-feedback-scale.md)
- [Feedback & Monitoring Layer](./feedback-monitoring-layer-codex.md)
- [OpenAPI Specification](./openapi.yaml)
- [Public Beta Setup](./public-beta-setup-codex.md)
- [Phase M: Creator & Curator Module](./codex-phase-m-creator-curator-codex.md)
- [WordPress Menus with Shortcodes](./wordpress-menus-codex.md)

### Developer Checklist

| Task | Status |
| --- | --- |
| Audit script run with no failures | ✅ after setup |
| Missing items implemented or logged | ❌ |
| Markdown created with required sections | ✅ |
| Partner version exported (PDF/Google Doc) | ✅ |
| Linked in Codex index | ✅ |

### Running Codex Audit Checks

The PHP scripts under `codex/checks/` verify each sprint module. They need a
WordPress installation so that `wp-load.php` can be loaded. Run the environment
setup script from the plugin root to download WordPress and install dependencies:

```bash
./scripts/setup-env.sh
```
This script requires the `svn` command to download the WordPress test suite. Install Subversion if it is not available.

After this completes you can run a check with:

```bash
php codex/checks/sprint-1-map-qa.php
```

Each script automatically searches for `wp-load.php` relative to the plugin
directory, so the checks work both inside an existing WordPress site and in a
local environment created with the setup script.
