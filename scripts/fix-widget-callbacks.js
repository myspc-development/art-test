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
const pluginDir = path.resolve(__dirname, '..');
const widgetsPath = path.join(pluginDir, 'available-widgets.json');

function color(type, message) {
  if (!chalk) return message;
  return chalk[type] ? chalk[type](message) : message;
}

function loadWidgets() {
  try {
    const raw = fs.readFileSync(widgetsPath, 'utf8');
    return JSON.parse(raw);
  } catch (err) {
    console.error(color('red', `Failed to read available-widgets.json: ${err.message}`));
    process.exit(1);
  }
}

function parseArgs() {
  const args = process.argv.slice(2);
  const opts = { role: 'member', fix: false };
  for (let i = 0; i < args.length; i++) {
    const arg = args[i];
    if (arg === '--fix') opts.fix = true;
    else if (arg === '--role') opts.role = args[++i];
  }
  return opts;
}

function resolveFile(callback) {
  const prefixes = ['', 'widgets/', 'src/', 'php/', 'includes/', 'src/widgets/', 'assets/js/widgets/'];
  for (const prefix of prefixes) {
    const candidate = path.join(pluginDir, prefix, callback);
    if (fs.existsSync(candidate)) {
      return { absolute: candidate, relative: prefix ? path.join(prefix, callback) : callback };
    }
  }
  return null;
}

function ensurePlaceholder(filePath, widgetId) {
  const ext = path.extname(filePath);
  fs.mkdirSync(path.dirname(filePath), { recursive: true });
  const base = path.basename(filePath, ext);
  if (ext === '.php') {
    const content = `<?php\nclass ${base} {\n    public static function render() {\n        echo '<div>${widgetId}</div>';\n    }\n}\n`;
    fs.writeFileSync(filePath, content, 'utf8');
  } else if (ext === '.jsx') {
    const content = `export default function ${base}() {\n  return <div>${widgetId}</div>;\n}\n`;
    fs.writeFileSync(filePath, content, 'utf8');
  }
}

function checkWidget(widget) {
  const cb = widget.callback;
  if (typeof cb !== 'string') {
    widget.status = 'callback_invalid';
    return;
  }

  if (cb.includes('::')) {
    // PHP class method callback
    const [classPart] = cb.split('::');
    const classPath = classPart.replace(/\\\\/g, '/') + '.php';
    const resolved = resolveFile(classPath);
    if (resolved) {
      widget.status = 'ok';
    } else {
      const target = path.join(pluginDir, 'widgets', classPath);
      ensurePlaceholder(target, widget.id);
      widget.callback = path.join('widgets', classPath).replace(/\\/g, '/');
      widget.status = 'file_created';
    }
    return;
  }

  const resolved = resolveFile(cb);
  if (resolved) {
    if (resolved.relative !== cb) {
      widget.callback = resolved.relative.replace(/\\/g, '/');
      widget.status = 'path_corrected';
    } else {
      widget.status = 'ok';
    }
  } else {
    // create placeholder in widgets/ or src/widgets
    const defaultDir = cb.endsWith('.php') ? 'widgets' : 'src/widgets';
    const target = path.join(pluginDir, defaultDir, cb);
    ensurePlaceholder(target, widget.id);
    widget.callback = path.join(defaultDir, cb).replace(/\\/g, '/');
    widget.status = 'file_created';
  }
}

function main() {
  const opts = parseArgs();
  const widgets = loadWidgets();
  const report = [];

  for (const widget of widgets) {
    if (!Array.isArray(widget.allowed_roles) || !widget.allowed_roles.includes(opts.role)) {
      continue;
    }
    checkWidget(widget);
    report.push({ id: widget.id, callback: widget.callback, status: widget.status });
  }

  if (opts.fix) {
    const output = widgets.map(({ status, ...rest }) => rest);
    fs.writeFileSync(widgetsPath, JSON.stringify(output, null, 4) + '\n', 'utf8');
  }

  if (report.length) {
    console.table(report);
  } else {
    console.log(color('yellow', `No widgets found for role "${opts.role}"`));
  }
}

main();
