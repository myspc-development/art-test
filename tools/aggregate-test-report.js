import { readdirSync, readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';

const buildDir = join(process.cwd(), 'build');
const e2eDir = join(buildDir, 'e2e');

const summary = {
  timestamp: new Date().toISOString(),
  suites: {},
  coverage: { php: { lines: 0, classes: 0 }, js: { lines: 0, branches: 0 } },
};

// ---- JUnit results ----
function ingestJUnit(filePath, prefix) {
  const xml = readFileSync(filePath, 'utf8');
  const regex = /<testsuite\b[^>]*>/g;
  let match;
  while ((match = regex.exec(xml))) {
    const tag = match[0];
    const nameMatch = tag.match(/name="([^"]*)"/);
    const name = nameMatch ? nameMatch[1] : '';
    const key = prefix || mapSuiteName(name);
    if (!key) continue;
    const testsMatch = tag.match(/tests="(\d+)"/);
    const failuresMatch = tag.match(/failures="(\d+)"/);
    const errorsMatch = tag.match(/errors="(\d+)"/);
    const skippedMatch = tag.match(/skipped="(\d+)"/);
    const tests = testsMatch ? Number(testsMatch[1]) : 0;
    const failures = (failuresMatch ? Number(failuresMatch[1]) : 0) +
                    (errorsMatch ? Number(errorsMatch[1]) : 0);
    const skipped = skippedMatch ? Number(skippedMatch[1]) : 0;
    const passed = tests - failures - skipped;
    const prev = summary.suites[key] || { passed: 0, failed: 0, skipped: 0 };
    summary.suites[key] = {
      passed: prev.passed + passed,
      failed: prev.failed + failures,
      skipped: prev.skipped + skipped,
    };
  }
}

function mapSuiteName(name) {
  const map = {
    Rest: 'phpunit:rest',
    Frontend: 'phpunit:frontend',
    Unit: 'phpunit:unit',
  };
  return map[name] || null;
}

if (existsSync(buildDir)) {
  for (const file of readdirSync(buildDir)) {
    if (file.startsWith('junit-') && file.endsWith('.xml')) {
      const prefix = file.includes('jest') ? 'jest' : undefined;
      ingestJUnit(join(buildDir, file), prefix);
    }
  }
}
if (existsSync(e2eDir)) {
  for (const file of readdirSync(e2eDir)) {
    if (file.endsWith('.xml')) {
      ingestJUnit(join(e2eDir, file), 'e2e');
    }
  }
}

// ---- Coverage: PHP ----
let phpLinesCovered = 0;
let phpLinesTotal = 0;
let phpClassesCovered = 0;
let phpClassesTotal = 0;

if (existsSync(buildDir)) {
  for (const file of readdirSync(buildDir)) {
    if (file.startsWith('coverage-phpunit') && file.endsWith('.xml')) {
      const xml = readFileSync(join(buildDir, file), 'utf8');
      const m = xml.match(/<metrics[^>]*>/);
      if (!m) continue;
      const tag = m[0];
      const get = (attr) => {
        const r = new RegExp(attr + '="(\d+)"');
        const mm = tag.match(r);
        return mm ? Number(mm[1]) : 0;
      };
      phpLinesCovered += get('lines-covered');
      phpLinesTotal += get('lines-valid');
      phpClassesCovered += get('coveredclasses');
      phpClassesTotal += get('classes');
    }
  }
}

if (phpLinesTotal > 0) {
  summary.coverage.php.lines = Math.round((phpLinesCovered / phpLinesTotal) * 100);
}
if (phpClassesTotal > 0) {
  summary.coverage.php.classes = Math.round((phpClassesCovered / phpClassesTotal) * 100);
}

// ---- Coverage: JS ----
const jsCoverageFile = join(process.cwd(), 'coverage', 'coverage-final.json');
if (existsSync(jsCoverageFile)) {
  const data = JSON.parse(readFileSync(jsCoverageFile, 'utf8'));
  let linesC = 0, linesT = 0, branchesC = 0, branchesT = 0;
  for (const file of Object.values(data)) {
    if (file.s) {
      linesT += Object.keys(file.s).length;
      linesC += Object.values(file.s).filter((n) => n > 0).length;
    }
    if (file.b) {
      for (const hits of Object.values(file.b)) {
        branchesT += hits.length;
        branchesC += hits.filter((n) => n > 0).length;
      }
    }
  }
  if (linesT > 0) {
    summary.coverage.js.lines = Math.round((linesC / linesT) * 100);
  }
  if (branchesT > 0) {
    summary.coverage.js.branches = Math.round((branchesC / branchesT) * 100);
  }
}

if (!existsSync(buildDir)) {
  mkdirSync(buildDir, { recursive: true });
}

writeFileSync(join(buildDir, 'test-summary.json'), JSON.stringify(summary, null, 2));
console.log('Wrote summary to build/test-summary.json');

