import fs from 'fs';
import path from 'path';
import { glob } from 'glob';

const root = process.cwd();
const widgetsPath = path.join(root, 'available-widgets.json');
const widgets = JSON.parse(fs.readFileSync(widgetsPath, 'utf8'));

const missing = [];
const roleMismatches = [];
const callbackSet = new Set();

for (const widget of widgets) {
  const cb = widget.callback;
  if (cb.includes('::')) continue;
  if (cb.endsWith('.php') || cb.endsWith('.jsx')) {
    const candidates = [
      path.join(root, cb),
      path.join(root, 'widgets', cb),
      path.join(root, 'assets/js/widgets', cb),
    ];
    const filePath = candidates.find((p) => fs.existsSync(p));
    if (!filePath) {
      const baseDir = cb.endsWith('.php') ? 'widgets' : 'assets/js/widgets';
      missing.push({
        id: widget.id,
        file: path.join(baseDir, cb).replace(/\\/g, '/'),
        type: cb.endsWith('.php') ? 'PHP' : 'JSX',
      });
    } else {
      const relPath = path.relative(root, filePath).replace(/\\/g, '/');
      callbackSet.add(relPath);
      const content = fs.readFileSync(filePath, 'utf8');
      for (const role of widget.allowed_roles || []) {
        if (!content.includes(role)) {
          roleMismatches.push({ id: widget.id, file: relPath, role });
        }
      }
    }
  }
}

const allWidgetFiles = await glob('**/*Widget.@(php|jsx)', {
  ignore: ['node_modules/**', 'vendor/**'],
});

const orphaned = allWidgetFiles.filter((f) => {
  const rel = f.replace(/^\.\//, '');
  return !callbackSet.has(rel);
});

const unregistered = [];
for (const file of await glob('**/*.php', {
  ignore: ['node_modules/**', 'vendor/**'],
})) {
  const content = fs.readFileSync(file, 'utf8');
  if (
    (content.includes('register_widget') || content.includes('add_meta_box')) &&
    !callbackSet.has(file.replace(/^\.\//, ''))
  ) {
    unregistered.push(file);
  }
}

function stubFor(file, type) {
  const base = path.basename(file, path.extname(file));
  if (type === 'JSX') {
    return `export default function ${base}() {\n  return <div>${base} Widget</div>;\n}`;
  }
  return `<?php\nclass ${base} {\n    public static function render() {\n        echo '<div>${base} Widget</div>';\n    }\n}`;
}

console.log('--- Widget Audit Report ---');
if (missing.length) {
  console.log('Missing callback files:');
  for (const m of missing) {
    console.log(`- ${m.id}: ${m.file} (expected ${m.type})`);
    console.log('  Suggested stub:');
    console.log(stubFor(m.file, m.type));
  }
} else {
  console.log('No missing callback files found.');
}

if (roleMismatches.length) {
  console.log('\nPossible role mismatches:');
  for (const r of roleMismatches) {
    console.log(`- ${r.id}: role "${r.role}" not referenced in ${r.file}`);
  }
} else {
  console.log('\nNo role mismatches detected.');
}

if (orphaned.length) {
  console.log('\nOrphaned widget files (not in available-widgets.json):');
  for (const o of orphaned) {
    console.log(`- ${o}`);
  }
} else {
  console.log('\nNo orphaned widget files detected.');
}

if (unregistered.length) {
  console.log('\nUnregistered widgets (registration code but missing from available-widgets.json):');
  for (const u of unregistered) {
    console.log(`- ${u}`);
  }
} else {
  console.log('\nNo unregistered widgets detected.');
}
