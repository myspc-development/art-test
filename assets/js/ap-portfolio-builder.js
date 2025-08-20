import { __ } from './ap-core.js';
import { Toast } from './ap-ui.js';

export default async function render(container) {
  const form = document.createElement('div');
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.multiple = true;
  const list = document.createElement('ul');
  form.appendChild(input);
  form.appendChild(list);
  container.appendChild(form);

  input.addEventListener('change', () => {
    list.textContent = '';
    Array.from(input.files).forEach((file) => {
      if (!/^image\//.test(file.type)) {
        Toast.show({ type: 'error', message: __('Invalid file type') });
        return;
      }
      const li = document.createElement('li');
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      img.alt = '';
      li.appendChild(img);
      const alt = document.createElement('input');
      alt.type = 'text';
      alt.placeholder = __('Alt text');
      alt.required = true;
      li.appendChild(alt);
      list.appendChild(li);
    });
  });
}
