/**
 * ap-dashboard.js
 * Requires: wp-element, wp-api-fetch (already exposed in the admin)
 * Localised via wp_localize_script as ArtPulseDashboardData:
 *   { nonce: '', rest_url: '' }
 */

import { render, useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

// Attach nonce to all REST requests
apiFetch.use(apiFetch.createNonceMiddleware(ArtPulseDashboardData.nonce));

/* ----------------  Shared Components ---------------- */

const Box = ({ title, children }) => (
  <div style={{
    margin: '16px 0', padding: '16px',
    border: '1px solid #ccd0d4', borderRadius: '6px',
    background: '#fff'
  }}>
    <h2 style={{ marginTop: 0 }}>{title}</h2>
    {children}
  </div>
);

const Loading = () => <p>Loading…</p>;
const ErrorMsg = ({ msg }) => <p style={{ color: 'red' }}>Error: {msg}</p>;

/* ----------------  Artist Overview Panel ------------- */

const ArtistOverviewPanel = () => {
  const [data, setData] = useState();
  const [err, setErr] = useState();

  useEffect(() => {
    apiFetch({ path: ArtPulseDashboardData.rest_url + 'artist' })
      .then(setData)
      .catch((e) => setErr(e.message));
  }, []);

  return (
    <Box title="Artist Overview">
      {!data && !err && <Loading />}
      {err && <ErrorMsg msg={err} />}
      {data && (
        <ul>
          <li>Followers: {data.followers}</li>
          <li>Sales: {data.sales}</li>
          <li>Artworks: {data.artworks}</li>
        </ul>
      )}
    </Box>
  );
};

/* ----------------  Organization Roles Panel ---------- */

const OrgRolesPanel = () => {
  const [roles, setRoles] = useState();
  const [err, setErr] = useState();

  useEffect(() => {
    apiFetch({ path: ArtPulseDashboardData.rest_url + 'org-roles' })
      .then(setRoles)
      .catch((e) => setErr(e.message));
  }, []);

  return (
    <Box title="Organization Roles">
      {!roles && !err && <Loading />}
      {err && <ErrorMsg msg={err} />}
      {roles && (
        <ul>
          {roles.map((role) => (
            <li key={role.id}>{role.label}</li>
          ))}
        </ul>
      )}
    </Box>
  );
};

/* ----------------  System Status Panel --------------- */

const SystemStatusPanel = () => {
  const [status, setStatus] = useState();
  const [err, setErr] = useState();

  useEffect(() => {
    apiFetch({ path: ArtPulseDashboardData.rest_url + 'status' })
      .then(setStatus)
      .catch((e) => setErr(e.message));
  }, []);

  return (
    <Box title="System Status">
      {!status && !err && <Loading />}
      {err && <ErrorMsg msg={err} />}
      {status && (
        <ul>
          <li>Plugin Version: {status.version}</li>
          <li>Cache: {status.cache}</li>
          <li>Debug Mode: {status.debug ? 'On' : 'Off'}</li>
        </ul>
      )}
    </Box>
  );
};

/* ----------------  Render Dashboard ------------------ */

render(
  <>
    <ArtistOverviewPanel />
    <OrgRolesPanel />
    <SystemStatusPanel />
  </>,
  document.getElementById('ap-dashboard-root')
);
