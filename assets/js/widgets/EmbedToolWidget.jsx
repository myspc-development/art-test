import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';

export function EmbedToolWidget({ widgetId, siteUrl }) {
  const [theme, setTheme] = useState('light');
  const code = `<script src="${siteUrl}/wp-json/widgets/embed.js?id=${widgetId}&theme=${theme}"></script>`;
  return (
    <div className="ap-embed-tool-widget">
      <p>
        <label>Theme 
          <select value={theme} onChange={e => setTheme(e.target.value)}>
            <option value="light">Light</option>
            <option value="dark">Dark</option>
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
