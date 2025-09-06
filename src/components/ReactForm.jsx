import React, { useState } from 'react';

const ReactForm = ({ type = 'default' }) => {
  const [formData, setFormData] = useState({ name: '', email: '' });
  const [status, setStatus] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setStatus('Submitting...');
    setIsSubmitting(true);
    try {
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

      if (!response.ok) {
        throw new Error('Request failed');
      }

      const result = await response.json();

      if (!result || typeof result.message !== 'string') {
        throw new Error('Unexpected response');
      }

      setStatus(result.message);
    } catch (error) {
      setStatus('There was an error submitting the form.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} data-type={type}>
      <input
        type="text"
        name="name"
        placeholder="Name"
        aria-label="Your name"
        value={formData.name}
        onChange={e => setFormData({ ...formData, name: e.target.value })}
      />
      <input
        type="email"
        name="email"
        placeholder="Email"
        aria-label="Email address"
        value={formData.email}
        onChange={e => setFormData({ ...formData, email: e.target.value })}
      />
      <button type="submit" disabled={isSubmitting}>Submit</button>
      <p>{status} {type !== 'default' ? `(${type})` : ''}</p>
    </form>
  );
};

export default ReactForm;
