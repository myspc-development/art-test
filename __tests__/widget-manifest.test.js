import { describe, it, expect } from '@jest/globals';
import fs from 'fs';
import path from 'path';
import React from 'react';
import { renderToString } from 'react-dom/server';

const manifestPath = path.resolve(__dirname, '../widget-manifest.json');
const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));

// Map for widgets whose component name does not follow simple conventions.
const overrides = {
  news_feed: 'ArtPulseNewsFeedWidget',
  artist_earnings_summary: 'ArtistEarningsWidget',
  collab_requests: 'ArtistCollaborationWidget',
  audience_crm: 'AudienceCRMWidget',
  branding_settings_panel: 'OrgBrandingSettingsPanel',
  ap_donor_activity: 'DonorActivityWidget',
  artpulse_analytics_widget: 'OrgAnalyticsWidget',
  org_widget_sharing: 'OrgWidgetSharingPanel',
  sponsor_display: 'SponsorDisplayWidget',
  webhooks: 'WebhooksWidget',
  sample_donations: 'DonationsWidget',
  profile_overview: 'ProfileOverviewWidget',
};

function deriveComponentName(id, file) {
  if (overrides[id]) return overrides[id];
  if (file.startsWith('widgets/')) {
    return path.basename(file, '.php');
  }
  // fall back to transforming the id to PascalCase and appending Widget
  const pascal = id
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join('');
  return pascal.endsWith('Widget') ? pascal : `${pascal}Widget`;
}

describe('widget-manifest components', () => {
  Object.entries(manifest).forEach(([id, info]) => {
    const name = deriveComponentName(id, info.file);
    const importPath = `../assets/js/widgets/${name}.jsx`;
    it(`imports and renders ${id}`, async () => {
      // dynamic import of each component; failure should cause the test to fail
      const mod = await import(importPath);
      const Component = mod[name] || mod.default;
      const html = renderToString(React.createElement(Component));
      expect(html).toMatch(/<[^>]+>/);
    });
  });
});
