import { useState } from 'react';

function WidgetEditorApp() {
    const [items, setItems] = useState([
        { id: 1, visible: true },
        { id: 2, visible: true }
    ]);
    const [notice, setNotice] = useState('');

    const showNotice = (msg) => {
        setNotice(msg);
        setTimeout(() => setNotice(''), 3000);
    };

    const save = (nextItems) => {
        if (!window.wp || !wp.ajax) {
            return;
        }
        wp.ajax.send('ap_save_role_layout', {
            data: {
                nonce: window.APWidgetEditor?.nonce,
                role: window.APWidgetEditor?.role,
                layout: JSON.stringify(nextItems)
            }
        }).then(() => {
            showNotice('Saved');
        }).catch(() => {
            showNotice('Failed to save');
        });
    };

    const toggle = (id) => {
        const updated = items.map(item => item.id === id ? { ...item, visible: !item.visible } : item);
        setItems(updated);
        save(updated);
    };

    return (
        <div>
            {notice && <div id="ap-widget-notice">{notice}</div>}
            <div id="ap-widget-items">
                {items.map(item => (
                    <div key={item.id} className="ap-widget-item" data-id={item.id}>
                        <span className="title">Widget {item.id}</span>
                        <button type="button" className="toggle" onClick={() => toggle(item.id)}>
                            {item.visible ? (window.APWidgetEditor?.hide || 'Hide') : (window.APWidgetEditor?.show || 'Show')}
                        </button>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default WidgetEditorApp;
