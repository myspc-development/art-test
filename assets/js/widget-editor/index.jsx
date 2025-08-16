import { createRoot } from 'react-dom/client';
import WidgetEditorApp from './WidgetEditorApp';

document.addEventListener('DOMContentLoaded', () => {
    const rootEl = document.getElementById('artpulse-widget-editor-root');
    if (!rootEl) {
        return;
    }
    const root = createRoot(rootEl);
    root.render(<WidgetEditorApp />);
});
