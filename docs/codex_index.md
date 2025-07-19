# 📚 Codex Index

> Central directory for all ArtPulse Codex documents — organized by phase, feature module, and sprint scope.

---

## ✅ Legend

- 🟢 = Ready for development
- 🟡 = Drafted, needs review/spec clarification
- 🔴 = Not yet started or placeholder

---

### Summary Documents
- [Completion Plan](./completion-plan.md)
- [Codex Documentation Workflow](./codex_documentation_workflow.md)
- [Codex Sprint Readiness](./codex_sprint_readiness.md)
- [Partner PDFs](./partner-pdf/)
- [API Reference Chapters](./api-chapters)


## ⚙️ Core Infrastructure & Foundation (Phase A)

| Document | Sprints Covered | Status | Audience |
|----------|-----------------|--------|----------|
| [`codex-phase-a-core-foundation.md`](./codex-phase-a-core-foundation.md) | 1, H2, J1 | 🟢 | Dev, PM |

---

## 📦 Growth & Distribution (Phase L)

| Document | Sprints Covered | Status | Audience |
|----------|------------------|--------|----------|
| [`growth-stack-overview.md`](./growth-stack-overview.md) | L1–L3 (Embeds, Digests, Feeds) | 🟢 | PM, Partners |
| [`embed-widgets.md`](./embed-widgets.md) | L1 | 🟢 | Dev, Partners |
| [`email-digests.md`](./email-digests.md) | L2 | 🟢 | Dev, PM |
| [`feeds-reference.md`](./feeds-reference.md) | L3 | 🟢 Ready | Dev |
| [`analytics-pilot.md`](./analytics-pilot.md) | L3 | 🟢 | Partners |

## 🧩 Dashboard Widget Manager

| Document | Purpose | Audience |
|----------|---------|----------|
| [`widget-manager-data-layer-guide.md`](./widget-manager-data-layer-guide.md) | Layout storage & retrieval | Dev |
| [`dashboard-editor-developer-guide.md`](./dashboard-editor-developer-guide.md) | Admin drag-and-drop editor | Dev |
| [`widget-registry-reference.md`](./widget-registry-reference.md) | Widget registration metadata | Dev |
| [`rest-api-and-ajax-endpoint-spec.md`](./rest-api-and-ajax-endpoint-spec.md) | REST and AJAX endpoints | Dev |
| [`widget-layout-import-export-guide.md`](./widget-layout-import-export-guide.md) | Import/export JSON | Dev |
| [`widget-manager-testing-checklist.md`](./widget-manager-testing-checklist.md) | Testing & access control | QA, Dev |
| [Widget Editor Codex Instructions](./Widget_Editor_Codex_Instructions.md) | How to document new widgets | Dev |

---

## 🎨 Creator & Curator Tools (Phase M)

| Document | Sprints Covered | Status | Audience |
|----------|------------------|--------|----------|
| [`codex-phase-m-creator-curator-codex.md`](./codex-phase-m-creator-curator-codex.md) | M1–M4 (Artist collab, threads, curator tools) | 🟢 | Dev, PM, Curators |
| [`artist-collab.md`](./artist-collab.md) | M1 | 🟢 | Dev |
| [`thread-formatting.md`](./thread-formatting.md) | M2 | 🟢 Ready | PM, Curators |
| [`curator-tools.md`](./curator-tools.md) | M3–M4 | 🟢 Ready | Dev, Admins |

---

## 🧰 Field Tools (Phase K)

| Document | Sprints Covered | Status | Audience |
|----------|------------------|--------|----------|
| [`mobile-artwalk.md`](./mobile-artwalk.md) | K1–K3 (PWA, check-ins, curator logs) | 🟢 | Dev, Institutional |

---

## 🧪 Platform Infrastructure (Phase J, I)

| Document | Sprints Covered | Status | Audience |
|----------|------------------|--------|----------|
| `codex_webhooks.md` | J1 | 🟢 Ready | Dev |
| `codex_grants_ai.md` | J3 | 🟢 Ready | Dev, PM |
| [`codex-sprint-i1-pricing-engine.md`](./codex-sprint-i1-pricing-engine.md) | I1–I4 | 🟢 Ready | Admin, Dev |
| [`codex-sprint-i3-crm-donor-tools.md`](./codex-sprint-i3-crm-donor-tools.md) | I3 | 🟢 Ready | Dev, Org Admin |

---

## ✳️ Planned / Next-Up Docs

| Planned File | Target Sprints | Status | Notes |
|--------------|----------------|--------|-------|
| `codex_institutional_toolkit.md` | N1–N3 | 🟢 Ready | Class visits, programs, RSVP upgrades |
| `codex_reporting_exports.md` | O1–O2 | 🟢 | Grant-ready CSVs, dashboard snapshots |
| `codex_curation_badges.md` | M6–M7 | 🟢 Ready | Curator trust, creator reputation |

---

## 🗂 Suggested Org Structure

```plaintext
/docs/
  codex_index.md
  growth-stack-overview.md
  codex-phase-m-creator-curator-codex.md
  embed-widgets.md
  email-digests.md
  feeds-reference.md
  artist-collab.md
  curator-tools.md
  thread-formatting.md
  mobile-artwalk.md
  codex_webhooks.md
  codex_grants_ai.md
  codex-sprint-i1-pricing-engine.md
  codex-sprint-i3-crm-donor-tools.md
```
📌 Next Steps
✅ Review 🟡 documents for readiness or updates

🛠 Merge/rename duplicates with consistent naming (codex_*.md)

📤 Export codex_index.md to partner wiki / Notion / GitHub

🚀 Begin implementation sprint on next-ready doc (recommend: codex_institutional_toolkit.md)

## Dev Checklist
- Update status icons after each release
- Highlight docs requiring [🚧 Sprint Needed]
