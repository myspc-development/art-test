import React, { useEffect, useState } from 'react';
const { __ } = wp.i18n;
import AnalyticsCard from './AnalyticsCard';
import TopUsersTable from './TopUsersTable';
import ActivityGraph from './ActivityGraph';
import FlaggedActivityLog from './FlaggedActivityLog';

export default function CommunityAnalyticsPanel() {
  const [tab, setTab] = useState('messaging');
  const [data, setData] = useState({});
  const apiRoot = window.ArtPulseDashboardApi?.apiUrl || window.ArtPulseDashboardApi?.root || '/wp-json/';
  const nonce = window.apNonce || window.ArtPulseDashboardApi?.nonce || '';
  const token = window.ArtPulseDashboardApi?.apiToken || '';

  useEffect(() => {
    const headers = { 'X-WP-Nonce': nonce };
    if (token) headers['Authorization'] = `Bearer ${token}`;
    fetch(`${apiRoot}artpulse/v1/analytics/community/${tab}`, {
      headers,
      credentials: 'same-origin'
    })
      .then(res => (res.status === 401 || res.status === 403 || res.status === 404 ? {} : res.json()))
      .then(setData)
      .catch(() => setData({}));
  }, [tab]);
  if (Object.keys(data).length === 0) {
    return (
      <div className="ap-widget bg-white p-4 rounded shadow mb-4">
        <p>{__('No analytics available.', 'artpulse')}</p>
      </div>
    );
  }

  return (
    <div className="ap-widget bg-white p-4 rounded shadow mb-4">
      <div className="flex gap-4 mb-4">
        {['messaging','comments','forums'].map(t => (
          <button
            key={t}
            onClick={() => setTab(t)}
            className={tab === t ? 'font-semibold' : ''}
          >
            {__('' + t.charAt(0).toUpperCase() + t.slice(1), 'artpulse')}
          </button>
        ))}
      </div>
      <div className="grid gap-4 md:grid-cols-2 mb-4">
        <AnalyticsCard label={__('Total', 'artpulse')} value={data.total} />
        {data.flagged_count !== undefined && (
          <AnalyticsCard label={__('Flagged', 'artpulse')} value={data.flagged_count} />
        )}
      </div>
      {data.per_day && <ActivityGraph data={data.per_day} />}
      {tab === 'messaging' && data.top_users && <TopUsersTable users={data.top_users} />}
      {tab !== 'messaging' && data.top_posts && <FlaggedActivityLog items={data.top_posts} />}
      {tab === 'forums' && data.top_threads && <FlaggedActivityLog items={data.top_threads} />}
    </div>
  );
}
