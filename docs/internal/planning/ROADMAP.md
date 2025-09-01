---
title: ArtPulse Task Roadmap
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# ArtPulse Task Roadmap

A living document to track major tasks across the project.

### 🔐 Security & Stability – HIGH PRIORITY

| Task ID | Task Title | Description | Status |
|--------|-------------|-------------|--------|
| S1 | Implement Nonce Validation | Add `wp_nonce_field()` and `check_admin_referer()` to all admin forms. | ✅ |
| S2 | Hardening REST Permissions | Verify `permission_callback` for each endpoint. | ✅ |

---

### 🧠 Auto-Tagger NLP – MEDIUM PRIORITY

| Task ID | Task Title | Description | Status |
|--------|-------------|-------------|--------|
| NLP1 | Improve Tag Suggestions | Refine algorithm for style and genre detection. | ✅ |
| NLP2 | Multilingual Support | Train models for additional languages. | ✅ |

---

### 🖥️ Widget UI Editor – MEDIUM PRIORITY

| Task ID | Task Title | Description | Status |
|--------|-------------|-------------|--------|
| UI1 | Validate Dashboard Widgets | Ensure unique ID and labels for each widget. | ✅ |
| UI2 | React Panel Integration | Integrate ArtistOverviewPanel and others. | ✅ |

---

### 🧪 Testing Suite – MEDIUM PRIORITY

| Task ID | Task Title | Description | Status |
|--------|-------------|-------------|--------|
| TE1 | Setup PHPUnit & Bootstrap | Create `phpunit.unit.xml.dist` and `phpunit.wp.xml.dist` and bootstrap WordPress testing framework. | ✅ |
| TE2 | Write Unit Tests | Add tests for post tagging and plugin activation. | ✅ |

---

### 🌍 i18n + ♿ Accessibility – LOW PRIORITY

| Task ID | Task Title | Description | Status |
|--------|-------------|-------------|--------|
| I1 | Add Language Files | Include `.pot` and translation files. | ✅ |
| A1 | Keyboard Navigation | Ensure admin pages are fully keyboard-accessible. | ✅ |

---

### 🚀 Release Prep – MEDIUM PRIORITY

| Task ID | Task Title | Description | Status |
|--------|-------------|-------------|--------|
| R1 | CHANGELOG Updates | Document all notable changes for release. | ✅ |
| R2 | Uninstall Cleanup | Remove plugin data on uninstall. | ✅ |

Last updated: July 18, 2025

> 💬 *Found something outdated? [Submit Feedback](../../feedback.md)*
