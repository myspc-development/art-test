import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';

export function OrgBrandingSettingsPanel({ apiRoot, nonce, orgId }) {
  const [settings, setSettings] = useState({ logo: '', color: '#000000', footer: '' });

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/org/${orgId}/meta`, {
      headers: { 'X-WP-Nonce': nonce }
    })
      .then(r => r.json())
      .then(data => setSettings({
        logo: data.logo || '',
        color: data.color || '#000000',
        footer: data.footer || ''
      }));
  }, [orgId]);

  const save = () => {
    fetch(`${apiRoot}artpulse/v1/org/${orgId}/meta`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
      body: JSON.stringify(settings)
    });
  };

  return (
    <div className="ap-org-branding-settings">
      <p>
        <label>Logo URL
          <input type="text" value={settings.logo} onChange={e => setSettings({ ...settings, logo: e.target.value })} />
        </label>
      </p>
      <p>
        <label>Brand Color
          <input type="color" value={settings.color} onChange={e => setSettings({ ...settings, color: e.target.value })} />
        </label>
      </p>
      <p>
        <label>Footer Text
          <input type="text" value={settings.footer} onChange={e => setSettings({ ...settings, footer: e.target.value })} />
        </label>
      </p>
      <button type="button" onClick={save}>Save Branding</button>
    </div>
  );
}

export default function initOrgBrandingSettingsPanel(el) {
  const root = createRoot(el);
  const { apiRoot, nonce, orgId } = el.dataset;
  root.render(<OrgBrandingSettingsPanel apiRoot={apiRoot} nonce={nonce} orgId={orgId} />);
}
