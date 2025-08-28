import React, { useState } from 'react';
const { __ } = wp.i18n;
import CommentForm from './CommentForm';
import ReportDialog from './ReportDialog';

function Comment({ comment, onReply }) {
  const [showReply, setShowReply] = useState(false);
  return (
    <li className="border-b py-4">
      <div className="flex items-start gap-3">
        <img src={comment.avatar} alt="" className="w-8 h-8 rounded-full" />
        <div className="flex-1">
          <div className="flex justify-between">
            <span className="font-semibold">{comment.author}</span>
            <span className="text-sm text-gray-500">{comment.date}</span>
          </div>
          <p className="mt-1 whitespace-pre-wrap">{comment.content}</p>
          <div className="flex items-center gap-4 mt-2 text-sm">
            <button
              className="text-blue-600"
              onClick={() => setShowReply(!showReply)}
            >
              {__('Reply', 'artpulse')}
            </button>
            <button aria-label={__('Report', 'artpulse')} onClick={() => onReply('report', comment)}>
              <span className="sr-only">{__('Report', 'artpulse')}</span>🚩
            </button>
          </div>
          {showReply && (
            <div className="mt-2 ml-8">
              <CommentForm onSubmit={text => onReply('reply', comment, text)} autoFocus />
            </div>
          )}
          {comment.replies?.length > 0 && (
            <ul className="mt-4 ml-8 space-y-4">
              {comment.replies.map(r => (
                <Comment key={r.id} comment={r} onReply={onReply} />
              ))}
            </ul>
          )}
        </div>
      </div>
    </li>
  );
}

export default function CommentThread({ comments = [], onReport, onReply }) {
  const [activeReport, setActiveReport] = useState(null);
  const handleAction = (type, comment, text) => {
    if (type === 'report') {
      setActiveReport(comment);
    } else if (type === 'reply' && typeof onReply === 'function') {
      onReply(comment, text);
    }
  };
  const handleReportSubmit = (reason, notes) => {
    if (onReport && activeReport) {
      onReport(activeReport, reason, notes);
    }
    setActiveReport(null);
  };

  return (
    <div className="ap-comment-thread">
      <h3 className="text-xl font-semibold mb-4">{__('Comments', 'artpulse')} ({comments.length})</h3>
      <ul className="space-y-4">
        {comments.map(c => (
          <Comment key={c.id} comment={c} onReply={handleAction} />
        ))}
      </ul>
      {activeReport && (
        <ReportDialog
          onSubmit={handleReportSubmit}
          onClose={() => setActiveReport(null)}
        />
      )}
    </div>
  );
}
