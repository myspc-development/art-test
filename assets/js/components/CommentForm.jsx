import React, { useState, useRef, useEffect } from 'react';

export default function CommentForm({ onSubmit, autoFocus = false }) {
  const [content, setContent] = useState('');
  const textareaRef = useRef(null);

  useEffect(() => {
    const el = textareaRef.current;
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
  }, [content]);
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
        ref={textareaRef}
        className="w-full p-2 border rounded resize-none overflow-hidden focus:ring focus:border-blue-500"
        placeholder="Write a comment..."
        value={content}
        autoFocus={autoFocus}
        onChange={e => setContent(e.target.value)}
        rows={3}
      />
      <button
        type="submit"
        disabled={!content.trim()}
        className="bg-blue-600 text-white px-3 py-1 rounded disabled:opacity-50"
      >
        Submit
      </button>
    </form>
  );
}
