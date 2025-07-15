const fs = require('fs');
const glob = require('glob');

const patterns = ['**/*.php', '**/*.js', '**/*.jsx', '**/*.html'];

patterns.forEach(pattern => {
  const files = glob.sync(pattern, { ignore: ['node_modules/**', 'vendor/**', 'tools/convert-to-ap-card.js'] });
  files.forEach(file => {
    if (fs.statSync(file).isDirectory()) return;
    const content = fs.readFileSync(file, 'utf8');
    let modified = content;
    modified = modified.replace(/dashboard-overview-card|salient-widget-card/g, 'ap-card');
    modified = modified.replace(/\sstyle="[^"]*"/g, '');
    modified = modified.replace(/<h2(?![^>]*class=)([^>]*)>/g, '<h2 class="ap-card__title"$1>');
    if (modified !== content) {
      fs.writeFileSync(file + '.bak', content, 'utf8');
      fs.writeFileSync(file, modified, 'utf8');
      console.log('Updated', file);
    }
  });
});
