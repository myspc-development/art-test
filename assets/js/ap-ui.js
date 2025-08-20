import { __ } from './ap-core.js';

export const Toast = {
  show({ type = 'info', message }) {
    const el = document.createElement('div');
    el.className = `ap-toast ap-toast-${type}`;
    el.setAttribute('role', 'alert');
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 4000);
  },
};

export const Modal = {
  open({ title = '', content = '', actions = [] }) {
    const overlay = document.createElement('div');
    overlay.className = 'ap-modal-overlay';
    overlay.tabIndex = -1;

    const modal = document.createElement('div');
    modal.className = 'ap-modal';
    const h = document.createElement('h2');
    h.id = 'ap-modal-title';
    h.textContent = title;
    modal.appendChild(h);

    const body = document.createElement('div');
    body.className = 'ap-modal-content';
    if (typeof content === 'string') {
      body.textContent = content;
    } else {
      body.appendChild(content);
    }
    modal.appendChild(body);

    const footer = document.createElement('div');
    footer.className = 'ap-modal-actions';
    actions.forEach((a) => {
      const btn = document.createElement('button');
      btn.textContent = a.label;
      btn.className = a.className || '';
      btn.addEventListener('click', () => {
        if (a.onClick) a.onClick();
        overlay.remove();
      });
      footer.appendChild(btn);
    });
    modal.appendChild(footer);

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    const first = modal.querySelector('button') || modal;
    first.focus();

    function onKey(e) {
      if (e.key === 'Escape') overlay.remove();
    }
    overlay.addEventListener('keydown', onKey);

    return overlay;
  },
};

export const Confirm = {
  show({ message, onConfirm }) {
    Modal.open({
      title: __('Confirm'),
      content: message,
      actions: [
        { label: __('Cancel') },
        { label: __('OK'), onClick: onConfirm },
      ],
    });
  },
};
