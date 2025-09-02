import fs from 'fs';
import path from 'path';

const inputPath = path.resolve('build', 'test-summary.json');
const outputPath = path.resolve('out', 'test-summary.md');

if (!fs.existsSync(inputPath)) {
  console.error('test-summary.json not found at', inputPath);
  process.exit(1);
}

const data = JSON.parse(fs.readFileSync(inputPath, 'utf8'));
let md = '# Test Summary\n\n';

if (Array.isArray(data.suites)) {
  md += '## Suite Statuses\n';
  for (const suite of data.suites) {
    md += `- ${suite.name}: ${suite.status}\n`;
  }
  md += '\n';
}

const failures = [];
if (Array.isArray(data.suites)) {
  for (const suite of data.suites) {
    if (Array.isArray(suite.tests)) {
      for (const test of suite.tests) {
        if (test.status && test.status !== 'passed') {
          const msg = test.failureMessage || test.error || '';
          failures.push(`- ${suite.name} › ${test.name}${msg ? ': ' + msg : ''}`);
        }
      }
    }
  }
}

if (failures.length) {
  md += '## Failing Tests\n';
  md += failures.join('\n') + '\n\n';
}

if (data.coverage && typeof data.coverage === 'object') {
  md += '## Coverage Deltas\n';
  for (const [key, value] of Object.entries(data.coverage)) {
    if (value && typeof value === 'object') {
      const pct = value.pct ?? value.percent ?? 'n/a';
      const delta = value.delta ?? value.diff ?? 'n/a';
      const pctStr = typeof pct === 'number' ? `${pct}%` : pct;
      const deltaStr = typeof delta === 'number' ? `${delta >= 0 ? '+' : ''}${delta}%` : delta;
      md += `- ${key}: ${pctStr} (Δ ${deltaStr})\n`;
    }
  }
  md += '\n';
}

fs.mkdirSync(path.dirname(outputPath), { recursive: true });
fs.writeFileSync(outputPath, md);
console.log(`Generated ${outputPath}`);
