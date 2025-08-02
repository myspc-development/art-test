import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
const { __ } = wp.i18n;

export function OrgTeamRosterWidget({ apiRoot, nonce, orgId }) {
  const [team, setTeam] = useState([]);

  useEffect(() => {
    fetch(`${apiRoot}artpulse/v1/orgs/${orgId}/roles`, {
      headers: { 'X-WP-Nonce': nonce },
      credentials: 'same-origin'
    })
      .then(r => r.json())
      .then(data => setTeam(Array.isArray(data) ? data : []));
  }, [orgId]);

  return (
    <div className="ap-org-team-roster-widget" data-widget-id="org_team_roster">
      <ul>
        {team.map(user => (
          <li key={user.user_id}>{user.user_id} - {user.role}</li>
        ))}
      </ul>
    </div>
  );
}

export default function initOrgTeamRosterWidget(el) {
  const root = createRoot(el);
  const { apiRoot, nonce, orgId } = el.dataset;
  root.render(
    <OrgTeamRosterWidget apiRoot={apiRoot} nonce={nonce} orgId={orgId} />
  );
}
