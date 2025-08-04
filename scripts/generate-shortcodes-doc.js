import fs from 'fs';
import path from 'path';
import { globSync } from 'glob';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const files = globSync(path.join(__dirname, '../{src,includes,shortcodes}/**/*.php'));
const regex = /ShortcodeRegistry::register\(\s*'([^']+)'\s*,\s*'([^']+)'/g;
const rows = {};
for (const file of files) {
  const php = fs.readFileSync(file, 'utf8');
  let match;
  while ((match = regex.exec(php))) {
    rows[match[1]] = match[2];
  }
}
const tags = Object.keys(rows).sort();
const data = tags.map(tag => ({ tag: `[${tag}]`, label: rows[tag] }));
const extras = {
  '[ap_favorite_portfolio]': { attrs: 'category, limit, sort, page', ex: '[ap_favorite_portfolio limit="12"]' },
  '[ap_favorites_analytics]': { attrs: 'type, user_id, admin_only, roles', ex: '[ap_favorites_analytics type="detailed"]' },
  '[react_form]': { attrs: 'type', ex: '[react_form type="submission"]' }
};
const lines = [
  '# Shortcodes Reference',
  '',
  'This table was auto-generated from registered shortcodes.',
  '',
  '| Shortcode | Attributes | Example |',
  '|-----------|------------|---------|'
];
for (const r of data) {
  const e = extras[r.tag] || { attrs: '—', ex: r.tag };
  lines.push(`| \`${r.tag}\` | ${e.attrs} | ${e.ex} |`);
}
fs.writeFileSync(path.join(__dirname, '../docs/shortcodes.md'), lines.join('\n'));
