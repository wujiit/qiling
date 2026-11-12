#!/usr/bin/env node

import { readdirSync, readFileSync, statSync } from 'node:fs';
import { dirname, extname, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const args = new Set(process.argv.slice(2));
const jsonMode = args.has('--json');
const strictMode = args.has('--strict');

const targetDirs = [
  'assets/css',
  'template-parts',
  'templates',
  'inc/modules',
  'inc/core',
];

const allowedExtensions = new Set(['.css', '.php', '.js']);

const ignoredRelativePrefixes = [
  'dev/tests/',
  'dev/tools/',
  'inc/admin/',
  'tools/',
];

const ignoredRelativeFiles = new Set([
  'assets/css/admin.css',
  'inc/core/class-design-tokens.php',
  'inc/core/class-page-performance-a11y-auditor.php',
]);

const colorLiteralPattern = /#(?:[0-9a-f]{3}|[0-9a-f]{6})\b|(?:rgba?|hsla?)\([^)]*\)/gi;
const sizeLiteralPattern = /\b(font-size|line-height|letter-spacing|max-width|min-width|gap|column-gap|row-gap|padding(?:-[a-z]+)?|margin(?:-[a-z]+)?)\s*:\s*([^;]+)/gi;

function toRelative(fullPath) {
  return relative(rootDir, fullPath).replace(/\\/g, '/');
}

function shouldIgnore(fullPath) {
  const rel = toRelative(fullPath);

  if (ignoredRelativeFiles.has(rel)) {
    return true;
  }

  return ignoredRelativePrefixes.some((prefix) => rel.startsWith(prefix));
}

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

    if (!allowedExtensions.has(extname(entry.name)) || shouldIgnore(fullPath)) {
      continue;
    }

    files.push(fullPath);
  }

  return files;
}

function matchColorFindings(line, file, lineNumber) {
  const findings = [];
  colorLiteralPattern.lastIndex = 0;

  for (const match of line.matchAll(colorLiteralPattern)) {
    const value = match[0];

    if (!value || value.includes('var(')) {
      continue;
    }

    findings.push({
      type: 'color_literal',
      file,
      line: lineNumber,
      value,
      snippet: line.trim(),
    });
  }

  return findings;
}

function matchSizeFindings(line, file, lineNumber) {
  const findings = [];
  const trimmedLine = line.trim();

  if (/^@(media|container|supports)\b/i.test(trimmedLine)) {
    return findings;
  }

  sizeLiteralPattern.lastIndex = 0;

  for (const match of line.matchAll(sizeLiteralPattern)) {
    const property = (match[1] || '').toLowerCase();
    const rawValue = (match[2] || '').trim();

    if (!property || !rawValue) {
      continue;
    }

    if (/(?:var|clamp|calc|min|max)\(/i.test(rawValue)) {
      continue;
    }

    if (!/-?(?:\d+|\d*\.\d+)(?:px|rem|em|%|vh|vw)/i.test(rawValue)) {
      continue;
    }

    findings.push({
      type: 'size_literal',
      file,
      line: lineNumber,
      value: `${property}: ${rawValue}`,
      property,
      snippet: line.trim(),
    });
  }

  return findings;
}

function scanFile(fullPath) {
  const rel = toRelative(fullPath);
  const contents = readFileSync(fullPath, 'utf8');
  const lines = contents.split(/\r?\n/);
  const findings = [];

  lines.forEach((line, index) => {
    if (!line.trim()) {
      return;
    }

    findings.push(...matchColorFindings(line, rel, index + 1));
    findings.push(...matchSizeFindings(line, rel, index + 1));
  });

  return findings;
}

const files = targetDirs
  .map((dir) => join(rootDir, dir))
  .filter((dir) => {
    try {
      return statSync(dir).isDirectory();
    } catch {
      return false;
    }
  })
  .flatMap((dir) => collectFiles(dir))
  .sort();

const findings = files.flatMap((file) => scanFile(file));

const summary = {
  scannedFiles: files.length,
  findings: findings.length,
  color_literals: findings.filter((item) => item.type === 'color_literal').length,
  size_literals: findings.filter((item) => item.type === 'size_literal').length,
};

const findingsByFile = Array.from(
  findings.reduce((map, finding) => {
    const current = map.get(finding.file) || { file: finding.file, count: 0, colors: 0, sizes: 0 };
    current.count += 1;
    if (finding.type === 'color_literal') {
      current.colors += 1;
    } else {
      current.sizes += 1;
    }
    map.set(finding.file, current);
    return map;
  }, new Map()).values()
).sort((a, b) => b.count - a.count || a.file.localeCompare(b.file));

const result = {
  summary,
  topFiles: findingsByFile.slice(0, 12),
  findings: findings.slice(0, 80),
};

if (jsonMode) {
  console.log(JSON.stringify(result, null, 2));
} else {
  console.log('QiLing design token coverage audit');
  console.log(`- Scanned files: ${summary.scannedFiles}`);
  console.log(`- Literal colors: ${summary.color_literals}`);
  console.log(`- Literal size/layout values: ${summary.size_literals}`);
  console.log(`- Total findings: ${summary.findings}`);

  if (result.topFiles.length > 0) {
    console.log('\nTop files to review:');
    for (const item of result.topFiles) {
      console.log(`- ${item.file} (${item.count} findings, ${item.colors} colors, ${item.sizes} sizes)`);
    }
  }

  if (result.findings.length > 0) {
    console.log('\nSample findings:');
    for (const finding of result.findings) {
      console.log(`- [${finding.type}] ${finding.file}:${finding.line} -> ${finding.value}`);
    }
  } else {
    console.log('\nNo obvious hardcoded style literals were found in the scanned front-end surfaces.');
  }

  console.log('\nTips:');
  console.log('- Prefer color tokens such as var(--color-primary) or var(--color-text).');
  console.log('- Prefer responsive layout tokens such as var(--qiling-container-width) and var(--qiling-grid-gap).');
  console.log('- Use --json for machine-readable output and --strict to fail when findings exist.');
}

if (strictMode && findings.length > 0) {
  process.exitCode = 1;
}
