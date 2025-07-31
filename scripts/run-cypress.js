try {
  require.resolve('cypress');
} catch (err) {
  console.log('Cypress not installed; skipping UI tests.');
  process.exit(0);
}
const { spawn } = require('child_process');
const proc = spawn('npx', ['cypress', 'run', ...process.argv.slice(2)], { stdio: 'inherit' });
proc.on('exit', code => process.exit(code));
