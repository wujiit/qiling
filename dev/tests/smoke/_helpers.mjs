import { existsSync, readFileSync, readdirSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const testsDir = dirname(fileURLToPath(import.meta.url));
export const themeRoot = resolve(testsDir, '../../..');

export function themePath(relativePath) {
  return resolve(themeRoot, relativePath);
}

export function assertFileExists(relativePath, message) {
  const fullPath = themePath(relativePath);
  if (!existsSync(fullPath)) {
    throw new Error(message || `Expected file to exist: ${relativePath}`);
  }
}

export function readThemeFile(relativePath) {
  return readFileSync(themePath(relativePath), 'utf8');
}

export function readAdminSettingsFieldRenderSources() {
  const aggregator = readThemeFile('inc/admin/traits/class-admin-settings-field-render-trait.php');
  const splitDir = themePath('inc/admin/traits/field-render');
  const splitSources = existsSync(splitDir)
    ? readdirSync(splitDir)
      .filter((file) => file.endsWith('.php'))
      .sort()
      .map((file) => readThemeFile(`inc/admin/traits/field-render/${file}`))
    : [];

  return [aggregator, ...splitSources].join('\n');
}

export function assertContains(haystack, needle, message) {
  if (!haystack.includes(needle)) {
    throw new Error(message || `Expected content to include: ${needle}`);
  }
}

export function assertMatches(haystack, pattern, message) {
  if (!pattern.test(haystack)) {
    throw new Error(message || `Expected content to match: ${pattern}`);
  }
}
