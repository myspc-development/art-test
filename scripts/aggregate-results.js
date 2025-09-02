import fs from 'node:fs';

function parseJUnit(path) {
  if (!fs.existsSync(path)) {
    return null;
  }
  const xml = fs.readFileSync(path, 'utf8');
  const match = xml.match(/<testsuite[^>]*tests="(\d+)"[^>]*failures="(\d+)"[^>]*errors="(\d+)"[^>]*skipped="(\d+)"/);
  if (!match) {
    return null;
  }
  const [, tests, failures, errors, skipped] = match.map(Number);
  return { tests, failures, errors, skipped };
}

function parseCoverageXml(path) {
  if (!fs.existsSync(path)) {
    return null;
  }
  const xml = fs.readFileSync(path, 'utf8');
  const match = xml.match(/<metrics[^>]*lines-covered="(\d+)"[^>]*lines-valid="(\d+)"/);
  if (!match) {
    return null;
  }
  const [, covered, total] = match.map(Number);
  return { pct: total ? covered / total : 0 };
}

function parseCoverageSummary(path) {
  if (!fs.existsSync(path)) {
    return null;
  }
  const data = JSON.parse(fs.readFileSync(path, 'utf8'));
  const pct = data.total?.lines?.pct ?? 0;
  return { pct: pct / 100 };
}

const suites = [];

// PHPUnit
const phpunit = parseJUnit('build/junit.xml');
if (!phpunit) {
  suites.push({ name: 'phpunit', status: 'missing' });
} else {
  let status = 'pass';
  if (phpunit.failures > 0 || phpunit.errors > 0 || phpunit.skipped > 0) {
    status = 'warn';
  }
  const cov = parseCoverageXml('build/coverage.xml');
  if (!cov || cov.pct < 0.7) {
    status = 'warn';
  }
  suites.push({ name: 'phpunit', status });
}

// Jest
const jest = parseJUnit('reports/junit/jest.xml');
if (!jest) {
  suites.push({ name: 'jest', status: 'missing' });
} else {
  let status = 'pass';
  if (jest.failures > 0 || jest.errors > 0 || jest.skipped > 0) {
    status = 'warn';
  }
  const cov = parseCoverageSummary('coverage/coverage-summary.json');
  if (!cov || cov.pct < 0.8) {
    status = 'warn';
  }
  suites.push({ name: 'jest', status });
}

// Playwright
if (fs.existsSync('playwright-report/index.html')) {
  suites.push({ name: 'playwright', status: 'pass' });
} else {
  suites.push({ name: 'playwright', status: 'missing' });
}

// Cypress
if (fs.existsSync('cypress/videos') || fs.existsSync('cypress/screenshots')) {
  suites.push({ name: 'cypress', status: 'pass' });
} else {
  suites.push({ name: 'cypress', status: 'missing' });
}

fs.writeFileSync('aggregated-results.json', JSON.stringify(suites, null, 2));
console.log('Aggregated results written to aggregated-results.json');
