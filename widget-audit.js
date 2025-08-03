#!/usr/bin/env node
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { createRequire } from 'module';

const require = createRequire(import.meta.url);
let chalk = null;
try {
  chalk = require('chalk');
} catch (err) {
  chalk = null;
}

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const pluginDir = __dirname;

function color(type, message) {
  if (!chalk) return message;
  return chalk[type] ? chalk[type](message) : message;
}

function loadWidgets() {
  const widgetsPath = path.join(pluginDir, 'available-widgets.json');
  try {
    const raw = fs.readFileSync(widgetsPath, 'utf8');
    return JSON.parse(raw);
  } catch (err) {
    console.error(color('red', `Failed to read available-widgets.json: ${err.message}`));
    process.exit(1);
  }
}

function collectFiles(startPath, files) {
  if (!fs.existsSync(startPath)) return;
  const stat = fs.statSync(startPath);
  if (stat.isFile()) {
    files.push(startPath);
    return;
  }
  const entries = fs.readdirSync(startPath, { withFileTypes: true });
  for (const entry of entries) {
    const full = path.join(startPath, entry.name);
    if (entry.isDirectory()) {
      collectFiles(full, files);
    } else if (entry.isFile()) {
      files.push(full);
    }
  }
}

function buildSearchIndex() {
  const searchRoots = [
    path.join(pluginDir, 'artpulse.php'),
    path.join(pluginDir, 'dashboard'),
    path.join(pluginDir, 'widgets'),
  ];
  const files = [];
  for (const rootPath of searchRoots) {
    collectFiles(rootPath, files);
  }
  const index = files.map((file) => ({ file, content: fs.readFileSync(file, 'utf8') }));
  return index;
}

function isWidgetRegistered(id, index) {
  return index.some(({ content }) => content.includes(id));
}

const widgets = loadWidgets();
const searchIndex = buildSearchIndex();
const allowedRoles = new Set(['artist', 'member', 'organization']);

const missingFiles = [];
const invalidRoleWidgets = [];
const unregisteredWidgets = [];

for (const widget of widgets) {
  const cb = widget.callback;
  const ext = typeof cb === 'string' ? path.extname(cb) : '';
  const isFileCallback = ['.php', '.js', '.jsx', '.ts', '.tsx', '.mjs', '.cjs'].includes(ext);
  if (isFileCallback) {
    const candidates = [
      path.join(pluginDir, cb),
      path.join(pluginDir, 'widgets', cb),
      path.join(pluginDir, 'assets/js/widgets', cb),
    ];
    const filePath = candidates.find((p) => fs.existsSync(p));
    if (!filePath) {
      missingFiles.push(widget.id);
      console.warn(color('yellow', `Missing callback file for widget "${widget.id}": ${cb}`));
    }
  }

  if (!Array.isArray(widget.allowed_roles) || widget.allowed_roles.some((r) => !allowedRoles.has(r))) {
    invalidRoleWidgets.push(widget.id);
    console.warn(
      color('yellow', `Invalid allowed_roles for widget "${widget.id}": ${JSON.stringify(widget.allowed_roles)}`)
    );
  }

  if (!isWidgetRegistered(widget.id, searchIndex)) {
    unregisteredWidgets.push(widget.id);
    console.warn(color('yellow', `Widget defined but not registered: "${widget.id}"`));
  }
}

console.log('\n--- Widget Audit Summary ---');
console.log(`Total widgets: ${widgets.length}`);
console.log(`Missing widget files: ${missingFiles.length}`);
console.log(`Widgets with invalid roles: ${invalidRoleWidgets.length}`);
console.log(`Widgets not registered: ${unregisteredWidgets.length}`);

if (missingFiles.length || invalidRoleWidgets.length || unregisteredWidgets.length) {
  process.exit(1);
}
