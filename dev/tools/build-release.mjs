#!/usr/bin/env node

import {
  copyFileSync,
  existsSync,
  mkdirSync,
  readFileSync,
  readdirSync,
  rmSync,
  writeFileSync,
} from 'node:fs';
import { basename, dirname, isAbsolute, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawnSync } from 'node:child_process';

const scriptDir = dirname(fileURLToPath(import.meta.url));
const themeRoot = resolve(scriptDir, '../..');
const themeSlug = basename(themeRoot);
const distRoot = resolveReleaseOutputRoot();
const releaseDir = join(distRoot, themeSlug);

function toPosixPath(input) {
  return input.split('\\').join('/');
}

function resolveReleaseOutputRoot() {
  const configuredRoot = (process.env.QILING_RELEASE_DIR || '').trim();

  if (configuredRoot !== '') {
    return resolve(themeRoot, configuredRoot);
  }

  return resolve(themeRoot, '..', 'dist', themeSlug);
}

function assertDistRootOutsideTheme() {
  const relativeDistRoot = relative(themeRoot, distRoot);

  if (relativeDistRoot === '' || (!relativeDistRoot.startsWith('..') && !isAbsolute(relativeDistRoot))) {
    throw new Error('Release output must stay outside the theme source tree. Set QILING_RELEASE_DIR to a path outside qiling/.');
  }
}

function globToRegExp(pattern) {
  const escaped = pattern.replace(/[.+^${}()|[\]\\]/g, '\\$&');
  const source = escaped.replace(/\*/g, '[^/]*').replace(/\?/g, '[^/]');
  return new RegExp(`^${source}$`);
}

function readVersion() {
  const styleCss = readFileSync(join(themeRoot, 'style.css'), 'utf8');
  const match = styleCss.match(/^Version:\s*(.+)$/m);

  if (!match) {
    throw new Error('Unable to read theme version from style.css');
  }

  return match[1].trim();
}

function readIgnoreRules() {
  const ignoreFile = join(themeRoot, '.distignore');
  const rules = [];

  if (!existsSync(ignoreFile)) {
    return rules;
  }

  const lines = readFileSync(ignoreFile, 'utf8').split(/\r?\n/);
  for (const rawLine of lines) {
    const line = rawLine.trim();
    if (!line || line.startsWith('#')) {
      continue;
    }

    rules.push(line);
  }

  return rules;
}

function shouldIgnorePath(relativePath, rules) {
  const normalized = toPosixPath(relativePath).replace(/^\/+/, '');
  const segments = normalized.split('/').filter(Boolean);
  const baseName = segments.length > 0 ? segments[segments.length - 1] : normalized;

  for (const rule of rules) {
    const normalizedRule = toPosixPath(rule).replace(/^\/+/, '');
    const isAnyDepthRule = normalizedRule.startsWith('**/');
    const effectiveRule = isAnyDepthRule ? normalizedRule.slice(3) : normalizedRule;

    if (effectiveRule.includes('*') || effectiveRule.includes('?')) {
      if (effectiveRule.includes('/')) {
        if (globToRegExp(effectiveRule).test(normalized)) {
          return true;
        }
      } else if (globToRegExp(effectiveRule).test(baseName)) {
        return true;
      }
      continue;
    }

    if (effectiveRule.endsWith('/')) {
      const prefix = effectiveRule.replace(/\/+$/, '');
      if (isAnyDepthRule) {
        if (normalized === prefix || normalized.endsWith('/' + prefix) || normalized.includes('/' + prefix + '/')) {
          return true;
        }
      } else if (normalized === prefix || normalized.startsWith(prefix + '/')) {
        return true;
      }
      continue;
    }

    if (effectiveRule.includes('/')) {
      if (isAnyDepthRule) {
        if (normalized === effectiveRule || normalized.endsWith('/' + effectiveRule)) {
          return true;
        }
      } else if (normalized === effectiveRule || normalized.startsWith(effectiveRule + '/')) {
        return true;
      }
      continue;
    }

    if (isAnyDepthRule) {
      if (baseName === effectiveRule) {
        return true;
      }
      continue;
    }

    if (normalized === effectiveRule) {
      return true;
    }
  }

  return false;
}

function ensureCleanDistRoot() {
  mkdirSync(distRoot, { recursive: true });
  rmSync(releaseDir, { recursive: true, force: true });

  const existingEntries = readdirSync(distRoot, { withFileTypes: true });
  for (const entry of existingEntries) {
    if (!entry.isFile() || !entry.name.endsWith('.zip')) {
      continue;
    }

    if (entry.name.startsWith(`${themeSlug}-`)) {
      rmSync(join(distRoot, entry.name), { force: true });
    }
  }
}

function runPreReleaseChecks() {
  const result = spawnSync(process.execPath, [join(scriptDir, 'check-eol.mjs')], {
    cwd: themeRoot,
    encoding: 'utf8',
  });

  if (result.stdout.trim() !== '') {
    console.log(result.stdout.trim());
  }

  if (result.status !== 0) {
    throw new Error((result.stderr || result.stdout || 'EOL pre-release check failed').trim());
  }
}

function copyReleaseTree(sourceDir, targetDir, rules, summary) {
  mkdirSync(targetDir, { recursive: true });

  const entries = readdirSync(sourceDir, { withFileTypes: true });
  for (const entry of entries) {
    const sourcePath = join(sourceDir, entry.name);
    const relPath = toPosixPath(relative(themeRoot, sourcePath));

    if (shouldIgnorePath(relPath, rules)) {
      summary.skipped.push(relPath);
      continue;
    }

    const targetPath = join(targetDir, entry.name);

    if (entry.isDirectory()) {
      copyReleaseTree(sourcePath, targetPath, rules, summary);
      continue;
    }

    if (entry.isSymbolicLink()) {
      summary.skipped.push(relPath);
      continue;
    }

    copyFileSync(sourcePath, targetPath);
    summary.copied.push(relPath);
  }
}

function createZipArchive(version) {
  const zipFileName = `${themeSlug}-${version}.zip`;
  const zipAbsolutePath = join(distRoot, zipFileName);
  const result = spawnSync('zip', ['-rq', zipFileName, themeSlug], {
    cwd: distRoot,
    encoding: 'utf8',
  });

  if (result.status !== 0) {
    throw new Error((result.stderr || result.stdout || 'zip command failed').trim());
  }

  return zipAbsolutePath;
}

function writeManifest(version, summary, zipPath) {
  const manifestPath = join(distRoot, 'release-manifest.json');
  const manifest = {
    theme: themeSlug,
    version,
    generatedAt: new Date().toISOString(),
    releaseDir: toPosixPath(relative(themeRoot, releaseDir)),
    zipFile: toPosixPath(relative(themeRoot, zipPath)),
    copiedCount: summary.copied.length,
    skippedCount: summary.skipped.length,
    skipped: summary.skipped.sort(),
  };

  writeFileSync(manifestPath, `${JSON.stringify(manifest, null, 2)}\n`, 'utf8');
}

function main() {
  const version = readVersion();
  const ignoreRules = readIgnoreRules();
  const summary = {
    copied: [],
    skipped: [],
  };

  runPreReleaseChecks();
  assertDistRootOutsideTheme();
  ensureCleanDistRoot();
  copyReleaseTree(themeRoot, releaseDir, ignoreRules, summary);

  const zipPath = createZipArchive(version);
  writeManifest(version, summary, zipPath);

  console.log(`Release directory created: ${releaseDir}`);
  console.log(`Release zip created: ${zipPath}`);
  console.log(`Copied files: ${summary.copied.length}`);
  console.log(`Skipped entries: ${summary.skipped.length}`);
}

main();
