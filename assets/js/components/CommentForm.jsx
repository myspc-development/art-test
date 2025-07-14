import React, { useState } from 'react';

export default function CommentForm({ onSubmit, autoFocus = false }) {
  const [content, setContent] = useState('');
  const handleSubmit = e => {
    e.preventDefault();
    if (onSubmit && content.trim()) {
      onSubmit(content.trim());
      setContent('');
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-2" aria-label="Comment form">
      <textarea
        className="w-full p-2 border rounded resize-y"
        placeholder="Write a comment..."
        value={content}
        autoFocus={autoFocus}
        onChange={e => setContent(e.target.value)}
        rows={3}
      />
      <button
        type="submit"
        className="bg-blue-600 text-white px-3 py-1 rounded"
      >
        Submit
      </button>
    </form>
  );
}
