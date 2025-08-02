const fs = require('fs');
const path = require('path');
const phpFile = path.join(__dirname, '../src/Core/DashboardWidgetRegistry.php');
const lines = fs.readFileSync(phpFile, 'utf8').split(/\r?\n/);
const widgets = [];
for (let i = 0; i < lines.length; i++) {
  if (lines[i].includes('$register(')) {
    let block = [];
    for (let j = i+1; j < lines.length; j++) {
      if (lines[j].includes(');')) {
        block.push(lines[j]);
        i = j;
        break;
      }
      block.push(lines[j]);
    }
    const widget = {};
    block.forEach(l => {
      let m = l.match(/'id'\s*=>\s*'([^']+)'/);
      if (m) widget.id = m[1];
      m = l.match(/'label'\s*=>\s*__\( '([^']+)'/);
      if (m) widget.label = m[1];
      m = l.match(/'description'\s*=>\s*__\( '([^']+)'/);
      if (m) widget.desc = m[1];
      m = l.match(/'roles'\s*=>\s*\[([^\]]+)\]/);
      if (m) widget.roles = m[1].replace(/'/g,'').trim();
    });
    if (widget.id) widgets.push(widget);
  }
}
const docsDir = path.join(__dirname, '../docs/widgets');
if (!fs.existsSync(docsDir)) fs.mkdirSync(docsDir, { recursive: true });
const date = new Date().toISOString().split('T')[0];
widgets.forEach(w => {
  const file = path.join(docsDir, `${w.id}.md`);
  const content = `---\ntitle: ${w.label}\ncategory: widgets\nrole: developer\nlast_updated: ${date}\nstatus: draft\n---\n\n# ${w.label}\n\n**Widget ID:** \`${w.id}\`\n\n**Roles:** ${w.roles || 'all'}\n\n## Description\n${w.desc}\n`;
  fs.writeFileSync(file, content);
});
