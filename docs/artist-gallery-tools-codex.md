# ArtPulse Codex: Artist & Gallery Tools

This guide describes optional tools empowering artists and galleries on the ArtPulse platform. The modules allow creators to manage events, gain insights, and reduce administrative overhead.

## 1. Self-Serve Portals

Enable registered artist and gallery users to:

- Create and manage events
- Edit their profile
- Upload works
- Invite collaborators

**User Roles**

- `ap_role_artist`
- `ap_role_gallery_admin`

**Dashboard UI**

Menu options: **My Shows**, **Profile**, **Invites** via the `[artpulse_artist_dashboard]` shortcode.

**Capabilities**

```php
'ap_role_artist' => [
  'edit_artpulse_event' => true,
  'upload_files'        => true
]
```

Use the `artpulse_event` custom post type with the author as the artist. Restrict the list view to their own posts.

## 2. Auto-tagging & AI Descriptions

Auto-tagging is partially implemented in `auto-tagger.php`.

Codex additions:

- Expose REST route `/artpulse/v1/ai/describe-event`.
- Trigger on `save_post_artpulse_event`.
- Example test snippet:

```php
$response = rest_do_request('/artpulse/v1/ai/describe-event?post_id=123');
$this->assertArrayHasKey('summary', $response->get_data());
```

Document which tags the system recognizes (genre, medium, mood).

## 3. Real-time Insights Dashboard

Show artists and galleries:

- Views per show
- Saves & follows
- Demographics or time-series data

**Backend Tables**

- `ap_event_stats` – aggregate views/saves/follows
- `ap_event_logs` – raw logs for deeper analytics

**Frontend**

React widget loaded via `/wp-json/artpulse/v1/stats?event_id=123` with Chart.js or WordPress components.

## 4. Extend Org Role Matrix to CRM Features

Use the existing Role Matrix as a lightweight CRM tool:

- Invite contacts and assign roles: curator, collector, sponsor
- Track interactions

Implementation notes:

- Extend `org-roles.php` with new role types: `collector`, `curator`, `sponsor`
- Add email field and invite status
- New UI tabs: **Invite Contact** and **CRM View**
- Optional table: `ap_org_contacts` (`user_id`, `email`, `role`, `org_id`, `invited_by`)

CRM-related roles must not have default WordPress caps unless explicitly assigned.

## 5. Automated Grant-Ready Reports

Allow organizations to export attendance, engagement and accessibility metrics for grant reporting.

**Report Includes**

- Monthly visitor count
- Events with accessibility features
- Demographic breakdown (if available)

**Export format:** CSV or PDF.

**Implementation**

REST endpoint:

```php
/artpulse/v1/orgs/{org_id}/grant-report?month=YYYY-MM
```

Use `fpdf` or `PhpSpreadsheet` for PDF/CSV generation and add a download button to the admin UI.

## Donations

Allow supporters to contribute directly to artists or galleries.

- Add a `donation_url` field to user profiles.
- Show a **Support this Artist** button when a URL is configured.

**Codex QA Checklist**

- Report generates in under 5 seconds
- File contains all required fields
- Works for both galleries and festivals

## Permissions Summary

| Role         | Create events | View analytics | Use CRM | Export report |
|--------------|--------------|---------------|---------|---------------|
| Artist       | ✅           | ✅ (their own)| ❌      | ❌            |
| Gallery Admin| ✅           | ✅            | ✅      | ✅            |
| Curator      | ✳️ (invited) | ✳️           | ✅      | ❌            |

## Developer Checklist (per PR)

- Event CPT respects role capabilities
- AI tags and descriptions are testable
- Stats API returns expected shape
- CRM invites tracked in `ap_org_contacts`
- Reports export correctly for real orgs

