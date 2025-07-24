import React from 'react';
const { __ } = wp.i18n;

export default function ForumBoard({ threads = [], categories = [], canCreate = false }) {
  return (
    <div className="ap-forum-board space-y-6">
      <div className="flex flex-wrap items-center gap-2">
        {categories.map(cat => (
          <button
            key={cat}
            className="px-3 py-1 bg-gray-200 rounded text-sm hover:bg-gray-300"
          >
            {cat}
          </button>
        ))}
        {canCreate && (
          <button className="ml-auto px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            {__('Start New Thread', 'artpulse')}
          </button>
        )}
      </div>
      <div className="grid gap-4 md:grid-cols-2">
        {threads.map(t => (
          <a
            key={t.id}
            href={t.link}
            className="block border p-4 rounded hover:bg-gray-50"
          >
            <h4 className="font-semibold">{t.title}</h4>
            <div className="text-sm text-gray-500 flex justify-between mt-1">
              <span>{__('by', 'artpulse')} {t.author}</span>
              <span>{t.replies} {__('replies', 'artpulse')}</span>
            </div>
              <span className="text-xs text-gray-400">
              {__('Last activity', 'artpulse')} {t.lastActivity}
            </span>
          </a>
        ))}
      </div>
    </div>
  );
}
