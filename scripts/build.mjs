#!/usr/bin/env node
import { build, context } from 'esbuild';
import { fileURLToPath } from 'url';
import path from 'path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');

const isWatch = process.argv.includes('--watch');

const entryPoints = [
  path.join(root, 'assets/ts/dashboard/RoleDashboard.tsx'),
  path.join(root, 'assets/js/index.ts')
];

const buildOptions = {
  entryPoints,
  bundle: true,
  outdir: path.join(root, 'assets/dist'),
  outbase: path.join(root, 'assets'),
  entryNames: '[name]',
  sourcemap: true,
  format: 'esm',
  target: 'es2019',
  loader: {
    '.ts': 'ts',
    '.tsx': 'tsx',
    '.js': 'jsx',
    '.jsx': 'jsx'
  },
  logLevel: 'info'
};

async function run() {
  if (isWatch) {
    const ctx = await context(buildOptions);
    await ctx.watch();
    console.log('watch mode started');
  } else {
    await build(buildOptions);
    console.log('build complete');
  }
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
