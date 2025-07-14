import React, { useState } from 'react';
import CommentForm from './CommentForm';
import ReportDialog from './ReportDialog';

function Post({ post, onReply }) {
  const [showReply, setShowReply] = useState(false);
  return (
    <div className="border-b py-4">
      <div className="flex items-start gap-3">
        <img src={post.avatar} alt="" className="w-8 h-8 rounded-full" />
        <div className="flex-1">
          <div className="flex justify-between">
            <span className="font-semibold">{post.author}</span>
            <span className="text-sm text-gray-500">{post.date}</span>
          </div>
          <div className="mt-1 whitespace-pre-wrap">{post.content}</div>
          <div className="flex gap-4 mt-2 text-sm">
            <button className="text-blue-600" onClick={() => setShowReply(!showReply)}>
              Reply
            </button>
            <button aria-label="Report" onClick={() => onReply('report', post)}>
              <span className="sr-only">Report</span>🚩
            </button>
          </div>
          {showReply && (
            <div className="mt-2 ml-8">
              <CommentForm onSubmit={text => onReply('reply', post, text)} autoFocus />
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default function ForumThread({ title, posts = [] }) {
  const [activeReport, setActiveReport] = useState(null);
  const handle = (type, post, text) => {
    if (type === 'report') setActiveReport(post);
    else if (type === 'reply') console.log('reply to', post.id, text);
  };
  return (
    <div className="ap-forum-thread space-y-4">
      <h2 className="text-2xl font-bold mb-4">{title}</h2>
      {posts.map(p => (
        <Post key={p.id} post={p} onReply={handle} />
      ))}
      <div className="mt-4">
        <CommentForm onSubmit={text => handle('reply', { id: null }, text)} />
      </div>
      {activeReport && (
        <ReportDialog onClose={() => setActiveReport(null)} />
      )}
    </div>
  );
}
