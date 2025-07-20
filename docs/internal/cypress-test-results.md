---
title: Cypress Dashboard Integration Test Results
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Cypress Dashboard Integration Test Results

Date: 2025-07-17

An attempt was made to run the Cypress dashboard tests using:

```bash
npx cypress run
```

The command attempted to install Cypress but network access to `download.cypress.io` was blocked, so the binary could not be downloaded. The tests therefore did not run.

```
Cypress package version: 14.5.2
Cypress binary version: not installed
Electron version: not found
Bundled Node version: not found
```

Future runs require allowing access to download.cypress.io or preinstalling Cypress in the environment.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
