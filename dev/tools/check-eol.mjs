#!/usr/bin/env node

import { dirname, extname, join, relative, resolve } from 'node:path';
import { existsSync, readFileSync, readdirSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

const scriptDir = dirname(fileURLToPath(import.meta.url));
const themeRoot = resolve(scriptDir, '../..');
const shouldFix = process.argv.includes('--fix');
const ignoredDirs = new Set([
  '.git',
  'dist',
  'node_modules',
  'sms',
  'translate',
  'vendor',
]);
const binaryExtensions = new Set([
  '.7z',
  '.avif',
  '.bmp',
  '.eot',
  '.gif',
  '.gz',
  '.ico',
  '.jpeg',
  '.jpg',
  '.mo',
  '.otf',
  '.pdf',
  '.png',
  '.rar',
  '.svgz',
  '.tar',
  '.ttf',
  '.webp',
  '.woff',
  '.woff2',
  '.zip',
]);

function toPosixPath(input) {
  return input.split('\\').join('/');
}

function isLikelyBinary(buffer, file) {
  if (binaryExtensions.has(extname(file).toLowerCase())) {
    return true;
  }

  return buffer.includes(0);
}

function inspectLineEndings(text) {
  const crlf = (text.match(/\r\n/g) || []).length;
  const lf = (text.match(/(?<!\r)\n/g) || []).length;
  const cr = (text.match(/\r(?!\n)/g) || []).length;
  const hasFinalNewline = text === '' || text.endsWith('\n') || text.endsWith('\r');
  let type = '';

  if ((crlf > 0 && (lf > 0 || cr > 0)) || (cr > 0 && lf > 0)) {
    type = 'mixed';
  } else if (crlf > 0) {
    type = 'crlf';
  } else if (cr > 0) {
    type = 'cr';
  } else if (!hasFinalNewline) {
    type = 'missing-final-newline';
  }

  return {
    cr,
    crlf,
    lf,
    hasFinalNewline,
    type,
  };
}

function normalizeText(text) {
  let normalized = text.replace(/\r\n?/g, '\n');
  if (normalized !== '' && !normalized.endsWith('\n')) {
    normalized += '\n';
  }

  return normalized;
}

function walk(dir, files) {
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    if (ignoredDirs.has(entry.name)) {
      continue;
    }

    const absolutePath = join(dir, entry.name);
    if (entry.isDirectory()) {
      walk(absolutePath, files);
      continue;
    }

    if (entry.isFile()) {
      files.push(absolutePath);
    }
  }
}

function run() {
  if (!existsSync(themeRoot)) {
    throw new Error(`Theme root does not exist: ${themeRoot}`);
  }

  const files = [];
  const offenders = [];
  let scanned = 0;
  let fixed = 0;

  walk(themeRoot, files);

  for (const file of files) {
    const buffer = readFileSync(file);
    if (isLikelyBinary(buffer, file)) {
      continue;
    }

    scanned += 1;
    const text = buffer.toString('utf8');
    const result = inspectLineEndings(text);
    if (!result.type) {
      continue;
    }

    const relPath = toPosixPath(relative(themeRoot, file));
    if (shouldFix) {
      const normalized = normalizeText(text);
      if (normalized !== text) {
        writeFileSync(file, normalized, 'utf8');
        fixed += 1;
      }
      continue;
    }

    offenders.push({
      relPath,
      ...result,
    });
  }

  if (shouldFix) {
    console.log(`EOL normalized ${fixed} file(s); scanned ${scanned} text file(s).`);
    return;
  }

  if (offenders.length === 0) {
    console.log(`EOL check passed for ${scanned} text file(s).`);
    return;
  }

  const counts = offenders.reduce(
    (acc, item) => {
      acc[item.type] = (acc[item.type] || 0) + 1;
      return acc;
    },
    {}
  );
  console.error('EOL check failed. Expected LF-only line endings and a final newline.');
  console.error(
    `Offenders: mixed=${counts.mixed || 0}, crlf=${counts.crlf || 0}, cr=${counts.cr || 0}, missing_final_newline=${counts['missing-final-newline'] || 0}`
  );
  offenders.slice(0, 80).forEach((item) => {
    console.error(`- ${item.relPath} (${item.type}; CRLF=${item.crlf}, LF=${item.lf}, CR=${item.cr})`);
  });
  if (offenders.length > 80) {
    console.error(`...and ${offenders.length - 80} more file(s).`);
  }
  console.error('Run `npm run fix:eol` from qiling/dev to normalize text files.');
  process.exitCode = 1;
}

run();
