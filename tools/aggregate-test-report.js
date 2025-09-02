import { readdirSync, readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';

const buildDir = join(process.cwd(), 'build');
const suites = {};

if (existsSync(buildDir)) {
  const files = readdirSync(buildDir);

  for (const file of files) {
    if (/^junit-.*\.xml$/i.test(file)) {
      const name = file.replace(/^junit-|\.xml$/g, '');
      const xml = readFileSync(join(buildDir, file), 'utf8');
      const match = xml.match(/<testsuite[^>]*>/);
      if (match) {
        const tag = match[0];
        const get = (attr) => {
          const m = tag.match(new RegExp(attr + '="(\\d+)"'));
          return m ? Number(m[1]) : 0;
        };
        const tests = get('tests');
        const failures = get('failures');
        const errors = get('errors');
        const skipped = get('skipped');
        const passed = tests - failures - errors - skipped;
        const failed = failures + errors;
        const prev = suites[name] || { passed: 0, failed: 0 };
        suites[name] = { ...prev, passed: prev.passed + passed, failed: prev.failed + failed };
      }
    }
  }

  for (const file of files) {
    if (/^coverage-.*\.json$/i.test(file)) {
      const name = file.replace(/^coverage-|\.json$/g, '');
      const data = JSON.parse(readFileSync(join(buildDir, file), 'utf8'));
      const coverage = extractCoverage(data);
      suites[name] = { ...(suites[name] || {}), coverage };
    }
  }
}

function extractCoverage(data) {
  if (data?.total?.lines?.pct !== undefined) {
    return data.total.lines.pct;
  }
  if (typeof data?.lineRate === 'number') {
    return data.lineRate * 100;
  }
  if (typeof data?.coverage === 'number') {
    return data.coverage;
  }
  return null;
}

const output = { suites: [] };
for (const [name, info] of Object.entries(suites)) {
  output.suites.push({
    name,
    passed: info.passed ?? 0,
    failed: info.failed ?? 0,
    coverage: info.coverage ?? null,
  });
}

if (!existsSync(buildDir)) {
  mkdirSync(buildDir, { recursive: true });
}
writeFileSync(join(buildDir, 'test-summary.json'), JSON.stringify(output, null, 2));
console.log(`Wrote ${output.suites.length} suite summaries to build/test-summary.json`);
