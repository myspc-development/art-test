#!/usr/bin/env node
import fs from 'fs';
import path from 'path';
import mysql from 'mysql2/promise';
import { unserialize } from 'php-serialize';

const ROOT = process.cwd();
const widgetsPath = path.join(ROOT, 'available-widgets.json');
const widgets = JSON.parse(fs.readFileSync(widgetsPath, 'utf8'));
const widgetMap = new Map(widgets.map(w => [w.id, w]));
const allowedRoles = new Set(['artist', 'member', 'organization']);

function arraysEqual(a, b) {
  const sa = [...a].sort().join(',');
  const sb = [...b].sort().join(',');
  return sa === sb;
}

const missingComponents = [];
const roleMismatches = [];

for (const widget of widgets) {
  const cb = widget.callback || '';
  if (!cb.match(/\.(jsx|tsx|js)$/)) continue;
  const candidates = [
    path.join(ROOT, cb),
    path.join(ROOT, 'assets/js/widgets', cb),
  ];
  const file = candidates.find(p => fs.existsSync(p));
  if (!file) {
    missingComponents.push({ id: widget.id, file: cb });
    continue;
  }
  const content = fs.readFileSync(file, 'utf8');
  const match =
    content.match(/export\s+const\s+roles\s*=\s*\[([^\]]*)\]/) ||
    content.match(/static\s+roles\s*=\s*\[([^\]]*)\]/);
  const componentRoles = match
    ? match[1]
        .split(',')
        .map(r => r.replace(/['"\s]/g, ''))
        .filter(Boolean)
    : [];
  const allowed = widget.allowed_roles || [];
  if (!arraysEqual(componentRoles, allowed)) {
    roleMismatches.push({
      id: widget.id,
      file: path.relative(ROOT, file),
      componentRoles,
      allowed,
    });
  }
  if (allowed.some(r => !allowedRoles.has(r))) {
    roleMismatches.push({
      id: widget.id,
      file: path.relative(ROOT, file),
      componentRoles,
      allowed,
    });
  }
}

async function checkLayouts() {
  const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    port: process.env.DB_PORT,
  };
  if (!dbConfig.host) {
    console.warn('\nSkipping layout checks: DB_HOST not set.');
    return;
  }
  let conn;
  try {
    conn = await mysql.createConnection(dbConfig);
  } catch (err) {
    console.warn(`\nSkipping layout checks: ${err.message}`);
    return;
  }
  const prefix = process.env.DB_PREFIX || 'wp_';
  const [layoutRows] = await conn.execute(
    `SELECT user_id, meta_value FROM ${prefix}usermeta WHERE meta_key='ap_dashboard_layout'`
  );
  const [roleRows] = await conn.execute(
    `SELECT user_id, meta_value FROM ${prefix}usermeta WHERE meta_key='${prefix}capabilities'`
  );
  const roles = {};
  for (const row of roleRows) {
    try {
      const data = unserialize(row.meta_value);
      const role = Object.keys(data).find(k => data[k]);
      if (role) roles[row.user_id] = role;
    } catch {}
  }
  const orphaned = [];
  const misconfigured = [];
  for (const row of layoutRows) {
    let layout;
    try {
      layout = JSON.parse(row.meta_value);
    } catch {
      continue;
    }
    const userRole = roles[row.user_id] || 'unknown';
    for (const item of layout) {
      const id = item.id;
      const widget = widgetMap.get(id);
      if (!widget) {
        orphaned.push({ user: row.user_id, id });
      } else if (!widget.allowed_roles || !widget.allowed_roles.includes(userRole)) {
        misconfigured.push({ user: row.user_id, id, role: userRole });
      }
    }
  }
  if (orphaned.length) {
    console.log('\nOrphaned widgets found in layouts:');
    for (const o of orphaned) {
      console.log(`- user ${o.user} -> ${o.id} (suggest removal)`);
    }
  }
  if (misconfigured.length) {
    console.log('\nWidgets not permitted for user role:');
    for (const m of misconfigured) {
      console.log(`- user ${m.user} (${m.role}) -> ${m.id} (suggest removal)`);
    }
  }
  if (!orphaned.length && !misconfigured.length) {
    console.log('\nNo layout issues detected.');
  }
  await conn.end();
}

function report() {
  console.log('--- Widget Diagnostics ---');
  if (missingComponents.length) {
    console.log('Missing React components:');
    for (const m of missingComponents) {
      console.log(`- ${m.id}: ${m.file}`);
    }
  } else {
    console.log('All widget components found.');
  }
  if (roleMismatches.length) {
    console.log('\nRole mismatches:');
    for (const r of roleMismatches) {
      console.log(
        `- ${r.id} (${r.file}) component roles [${r.componentRoles.join(
          ', '
        )}] vs allowed [${r.allowed.join(', ')}]`
      );
    }
  } else {
    console.log('\nAll widget roles match.');
  }
}

report();
await checkLayouts();
