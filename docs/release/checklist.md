# Release Checklist

- [ ] Version bump
- [ ] Update changelog
- [ ] Database migration notes
- [ ] Smoke tests
- [ ] Build release artifacts

## How to cut a release

1. Tag the commit with `vX.Y.Z` and push.
2. CI runs tests, codex checks, and builds the plugin ZIP.
3. Download the artifact from the GitHub Release page.
