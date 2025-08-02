import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function EmbedToolWidget({ widgetId, siteUrl }) {
  const [theme, setTheme] = useState('light');
  const code = `<script src="${siteUrl}/wp-json/widgets/embed.js?id=${widgetId}&theme=${theme}"></script>`;
  return (
    <div className="ap-embed-tool-widget" data-widget-id="embed_tool">
      <p>
        <label>{__('Theme', 'artpulse')}
          <select value={theme} onChange={e => setTheme(e.target.value)}>
            <option value="light">{__('Light', 'artpulse')}</option>
            <option value="dark">{__('Dark', 'artpulse')}</option>
          </select>
        </label>
      </p>
      <textarea readOnly rows="3" style={{ width: '100%' }} value={code} />
    </div>
  );
}

export default function initEmbedToolWidget(el) {
  const root = createRoot(el);
  const { widgetId, siteUrl } = el.dataset;
  root.render(<EmbedToolWidget widgetId={widgetId} siteUrl={siteUrl} />);
}
