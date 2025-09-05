#!/usr/bin/env node
import { spawnSync } from 'node:child_process';
import path from 'node:path';
import { existsSync } from 'node:fs';

let phpunit = path.join('vendor', 'bin', 'phpunit');
if (!existsSync(phpunit)) {
  phpunit = path.join('vendor', 'phpunit', 'phpunit', 'phpunit');
}

const args = [
  '-c', 'phpunit.wp.xml.dist',
  '--log-junit', 'build/junit-phpunit-unit.xml',
  '--coverage-clover', 'build/coverage-phpunit-unit.xml',
];

const result = spawnSync(phpunit, args, { stdio: 'inherit' });
if (result.error) {
  console.error(result.error);
  process.exit(result.status ?? 1);
}
process.exit(result.status ?? 0);
