import React, { useState } from 'react';

const ReactForm = ({ type = 'default' }) => {
  const [formData, setFormData] = useState({ name: '', email: '' });
  const [status, setStatus] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setStatus('Submitting...');

    const { ajaxurl, nonce } = window.apReactForm || {};

    const response = await fetch(ajaxurl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'submit_react_form',
        _ajax_nonce: nonce,
        ...formData,
      }),
    });

    const result = await response.json();
    setStatus(result.message || 'Done!');
  };

  return (
    <form onSubmit={handleSubmit} data-type={type}>
      <input
        type="text"
        name="name"
        placeholder="Name"
        value={formData.name}
        onChange={e => setFormData({ ...formData, name: e.target.value })}
      />
      <input
        type="email"
        name="email"
        placeholder="Email"
        value={formData.email}
        onChange={e => setFormData({ ...formData, email: e.target.value })}
      />
      <button type="submit">Submit</button>
      <p>{status} {type !== 'default' ? `(${type})` : ''}</p>
    </form>
  );
};

export default ReactForm;
