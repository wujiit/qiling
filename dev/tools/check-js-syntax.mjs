#!/usr/bin/env node

import { readdirSync, statSync } from 'node:fs';
import { spawnSync } from 'node:child_process';
import { dirname, extname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDir = dirname(fileURLToPath(import.meta.url));
const rootDir = resolve(scriptDir, '../..');
const targets = [
  join(rootDir, 'assets', 'js'),
  join(rootDir, 'tools'),
  join(rootDir, 'dev', 'tests'),
  join(rootDir, 'dev', 'tools'),
];
const allowedExtensions = new Set(['.js', '.mjs']);

function collectFiles(dir) {
  const files = [];
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    if (entry.name === 'node_modules' || entry.name === 'vendor' || entry.name === '.git') {
      continue;
    }

    const fullPath = join(dir, entry.name);
    if (entry.isDirectory()) {
      files.push(...collectFiles(fullPath));
      continue;
    }

    if (!allowedExtensions.has(extname(entry.name))) {
      continue;
    }

    files.push(fullPath);
  }

  return files;
}

const files = targets
  .filter((target) => {
    try {
      return statSync(target).isDirectory();
    } catch {
      return false;
    }
  })
  .flatMap((target) => collectFiles(target))
  .sort();

if (files.length === 0) {
  console.log('No JavaScript files found to check.');
  process.exit(0);
}

const failures = [];

for (const file of files) {
  const result = spawnSync(process.execPath, ['--check', file], {
    encoding: 'utf8',
  });

  if (result.status !== 0) {
    failures.push({
      file,
      output: (result.stderr || result.stdout || '').trim(),
    });
  }
}

if (failures.length > 0) {
  console.error(`JavaScript syntax check failed for ${failures.length} file(s):`);
  for (const failure of failures) {
    console.error(`- ${failure.file}`);
    if (failure.output) {
      console.error(failure.output);
    }
  }
  process.exit(1);
}

console.log(`JavaScript syntax check passed for ${files.length} file(s).`);
