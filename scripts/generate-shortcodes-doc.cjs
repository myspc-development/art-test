const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, '../src/Admin/ShortcodePages.php');
const php = fs.readFileSync(file, 'utf8');
const regex = /'\[(ap_[^\]]+)\]'\s*=>\s*__\('([^']+)'/g;
let match;
const rows = [];
while ((match = regex.exec(php))) {
  rows.push({ tag: `[${match[1]}]`, label: match[2] });
}
const extras = {
  '[ap_favorite_portfolio]': { attrs: 'category, limit, sort, page', ex: '[ap_favorite_portfolio limit="12"]' },
  '[ap_favorites_analytics]': { attrs: 'type, user_id, admin_only, roles', ex: '[ap_favorites_analytics type="detailed"]' },
  '[react_form]': { attrs: 'type', ex: '[react_form type="submission"]' }
};
const lines = [
  '# Shortcodes Reference',
  '',
  'This table was auto-generated from `ShortcodePages::get_shortcode_map()`.',
  '',
  '| Shortcode | Attributes | Example |',
  '|-----------|------------|---------|'
];
rows.forEach(r => {
  const e = extras[r.tag] || { attrs: '—', ex: r.tag };
  lines.push(`| \`${r.tag}\` | ${e.attrs} | ${e.ex} |`);
});
fs.writeFileSync(path.join(__dirname, '../docs/shortcodes.md'), lines.join('\n'));
