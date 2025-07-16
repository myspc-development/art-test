# Codex: Sprint J3 – AI Grant Assistant

## Overview
This sprint introduces a lightweight AI toolkit that generates grant-ready language and exportable templates from existing ArtPulse data. The goal is to help artists, curators and organizations speak the language of funders with minimal effort.

## Goals
| Tool | Use |
|------|-----|
| **Grant Language Generator** | Create base narratives for project summaries, artist statements and public benefit copy |
| **AI Rewrite Tool** | Adjust tone for public, curatorial or grant applications |
| **Export Templates** | Download generated text in structured formats |
| **Update from Events** | Pull tags and descriptions directly into proposals |

## Data Model
The tools reuse existing tables:
- `ap_events`
- `ap_org_profiles`
- `user_meta`

An optional table `ap_generated_grant_copy` may store drafts per organization or user.

```sql
CREATE TABLE ap_generated_grant_copy (
  id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT,
  user_id BIGINT,
  type VARCHAR(40),
  tone VARCHAR(20),
  source TEXT,
  draft TEXT,
  created_at DATETIME
);
```

## 1. Grant Language Generator
Page: `/tools/grant-ai`

Fields:
- **Type** – "Project Summary", "Public Benefit", "Mission Alignment"
- **Source** – Org or user profile, with optional event selection
- **Tone** – "Formal", "Public", "Curatorial"

Submit via `POST /artpulse/v1/ai/generate-grant-copy`.

Example output:

> "This multi-week installation engages the local immigrant community through collaborative sculpture, emphasizing storytelling and healing. Our team of artists and facilitators bring trauma-informed practice and free access to underrepresented participants in East Brooklyn."

## 2. Rewrite Tool
A rewrite option appears next to any `<textarea>` field.

Modes:
- Public voice
- Grant application
- Curatorial review

Request body:
```http
POST /artpulse/v1/ai/rewrite
{
  "text": "This show explores...",
  "mode": "grant"
}
```

## 3. Grant Export Templates
Button **Download as Grant Template** offers:
- Artist Statement
- Public Program Summary
- Project Budget Outline (J4)

Formats: Markdown, PDF (Dompdf) or copyable plain text.

## 4. Generation Logic
If using an external AI backend, input fields include event title, tags and descriptions along with organization mission and user bio. A prompt scaffold might be:

```
Write a 150-word public benefit summary for a community arts grant. Include themes from this project: [tags]. Tone: grant application.
```

## QA Checklist
- Select events → generate summary
- Rewrite paragraph with grant tone
- Save and re-edit draft output
- Download template as PDF
- Limit use to logged-in users

## Developer Sprint Checklist
- [x] Grant generation UI page
- [ ] API endpoints `/generate-grant-copy` and `/rewrite`
- [ ] Output rendering with copy/download
- [ ] Optional draft storage table
- [ ] Prompt templates & tone support

## Codex Docs to Update
- AI Tools
- Grant Copy Generator
- Tone Rewriter API
- Partner & Artist Docs – "How to Apply for Grants Using ArtPulse AI"
- Export Formats – sample public benefit and artist statement outputs
- Security & Limits – rate limiting or upgrade tier requirement
