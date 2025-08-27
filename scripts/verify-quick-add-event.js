import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export function verify() {
  const base = path.resolve(__dirname, '..');
  const results = {
    registered: false,
    callable: false,
    visible: false,
    selectors: false,
    jsSelectors: false,
    messages: [],
  };

  const widgetsPath = path.join(base, 'available-widgets.json');
  try {
    const widgets = JSON.parse(fs.readFileSync(widgetsPath, 'utf8'));
    const entry = widgets.find(w => w.id === 'org_event_quick_add');
    if (entry) {
      results.registered = true;
      if (!entry.callback || !entry.callback.endsWith('OrgQuickAddEventWidget.php')) {
        results.messages.push('callback must end with OrgQuickAddEventWidget.php');
      }
      if (!Array.isArray(entry.allowed_roles) || !entry.allowed_roles.includes('organization')) {
        results.messages.push('allowed_roles must include "organization"');
      }
    } else {
      results.messages.push('Widget id org_event_quick_add missing from available-widgets.json');
    }
  } catch (e) {
    results.messages.push('Error reading available-widgets.json: ' + e.message);
  }

  const presetPath = path.join(base, 'data', 'preset-organization.json');
  try {
    const preset = JSON.parse(fs.readFileSync(presetPath, 'utf8'));
    if (preset.some(w => w.id === 'widget_org_event_quick_add')) {
      results.visible = true;
    } else {
      results.messages.push('widget_org_event_quick_add missing from data/preset-organization.json');
    }
  } catch (e) {
    results.messages.push('Error reading preset-organization.json: ' + e.message);
  }

  const widgetFile = path.join(base, 'widgets', 'OrgQuickAddEventWidget.php');
  if (!fs.existsSync(widgetFile)) {
    results.messages.push('widgets/OrgQuickAddEventWidget.php missing');
  } else {
    const php = fs.readFileSync(widgetFile, 'utf8');
    const hasNamespace = php.includes('namespace ArtPulse\\Widgets');
    const hasClass = /class\s+OrgQuickAddEventWidget/.test(php);
    const hasRender = /public\s+static\s+function\s+render\s*\(int\s+\$user_id\s*=\s*0\)\s*:\s*string/.test(php);
    const ids = ['ap-add-event-btn', 'ap-org-modal', 'ap-org-event-form'];
    const hasIds = ids.every(id => php.includes(id));
    results.callable = hasNamespace && hasClass && hasRender;
    results.selectors = hasIds;
    if (!hasNamespace) results.messages.push('Namespace ArtPulse\\Widgets missing in widget file');
    if (!hasClass) results.messages.push('Class OrgQuickAddEventWidget missing');
    if (!hasRender) results.messages.push('render method signature incorrect');
    ids.forEach(id => {
      if (!php.includes(id)) results.messages.push(`Missing ${id} in widget markup`);
    });
  }

  const composerPath = path.join(base, 'composer.json');
  try {
    const composer = JSON.parse(fs.readFileSync(composerPath, 'utf8'));
    const psr4 = composer.autoload && composer.autoload['psr-4'];
    if (!psr4 || psr4['ArtPulse\\Widgets\\'] !== 'widgets/') {
      results.messages.push('composer.json missing PSR-4 mapping "ArtPulse\\Widgets\\" => "widgets/"');
    }
  } catch (e) {
    results.messages.push('Error reading composer.json: ' + e.message);
  }

  const jsPath = path.join(base, 'assets', 'js', 'ap-org-dashboard.js');
  if (fs.existsSync(jsPath)) {
    const js = fs.readFileSync(jsPath, 'utf8');
    const ids = ['ap-add-event-btn', 'ap-org-modal', 'ap-org-event-form'];
    results.jsSelectors = ids.every(id => js.includes(id));
    if (!results.jsSelectors) {
      ids.forEach(id => {
        if (!js.includes(id)) results.messages.push(`JS missing selector ${id}`);
      });
    }
  } else {
    results.messages.push('assets/js/ap-org-dashboard.js missing');
  }

  return results;
}

if (import.meta.url === `file://${process.argv[1]}`) {
  const res = verify();
  if (res.messages.length) {
    console.error('FAIL');
    res.messages.forEach(m => console.error('- ' + m));
    process.exit(1);
  } else {
    console.log('PASS: org_event_quick_add verified');
  }
}
