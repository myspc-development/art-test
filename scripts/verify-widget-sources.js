#!/usr/bin/env node
import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';

const ROOT = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const PHP_CONFIG = path.join(ROOT, 'config', 'dashboard-widgets.php');
const JSON_FILE = path.join(ROOT, 'available-widgets.json');
const JS_FILE = path.join(ROOT, 'assets', 'js', 'widgets', 'index.js');

function loadPhpWidgets() {
  try {
    const cmd = `php -r "echo json_encode(include '${PHP_CONFIG}');"`;
    const output = execSync(cmd, { encoding: 'utf8' });
    const data = JSON.parse(output);
    const map = {};
    Object.entries(data).forEach(([id, cfg]) => {
      map[id] = (cfg.roles || []).map(r => r.toLowerCase());
    });
    return map;
  } catch (err) {
    console.error('Failed to read PHP widget config:', err.message);
    return {};
  }
}

function loadJsonWidgets() {
  try {
    const data = JSON.parse(fs.readFileSync(JSON_FILE, 'utf8'));
    const map = {};
    data.forEach(w => {
      map[w.id] = (w.allowed_roles || []).map(r => r.toLowerCase());
    });
    return map;
  } catch (err) {
    console.error('Failed to read available-widgets.json:', err.message);
    return {};
  }
}

function loadJsWidgets() {
  const map = {};
  try {
    const content = fs.readFileSync(JS_FILE, 'utf8');
    const regex = /{\s*id:\s*'([^']+)',[\s\S]*?roles:\s*\[([^\]]*)\]/g;
    let match;
    while ((match = regex.exec(content))) {
      const id = match[1];
      const roles = match[2]
        .split(',')
        .map(s => s.replace(/['"\s]/g, ''))
        .filter(Boolean)
        .map(r => r.toLowerCase());
      map[id] = roles;
    }
  } catch (err) {
    console.error('Failed to read React widget index:', err.message);
  }
  return map;
}

function arraysEqual(a, b) {
  const sa = [...a].sort().join(',');
  const sb = [...b].sort().join(',');
  return sa === sb;
}

function main() {
  const phpMap = loadPhpWidgets();
  const jsonMap = loadJsonWidgets();
  const jsMap = loadJsWidgets();
  const ids = new Set([...Object.keys(phpMap), ...Object.keys(jsonMap), ...Object.keys(jsMap)]);
  let ok = true;
  ids.forEach(id => {
    const phpRoles = phpMap[id];
    const jsonRoles = jsonMap[id];
    const jsRoles = jsMap[id];
    const sources = [phpRoles, jsonRoles, jsRoles].filter(r => r !== undefined);
    if (sources.length > 1) {
      const base = sources[0];
      if (!sources.slice(1).every(r => arraysEqual(base, r))) {
        console.error(`Role mismatch for widget ${id}: PHP=${phpRoles || []} JSON=${jsonRoles || []} JS=${jsRoles || []}`);
        ok = false;
      }
    }
  });
  if (!ok) {
    console.error('Widget sources are out of sync.');
    process.exit(1);
  } else {
    console.log('Widget role definitions are synchronized.');
  }
}

main();
