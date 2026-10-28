import { readFileSync, statSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const scriptDir = dirname(fileURLToPath(import.meta.url));
const themeRoot = resolve(scriptDir, '../..');

const targets = [
  ['main', 'assets/css/main.css', 'assets/css/main.min.css'],
  ['modules', 'assets/css/modules.css', 'assets/css/modules.min.css'],
  ['modules-hero', 'assets/css/modules-hero.css', 'assets/css/modules-hero.min.css'],
];

function minifyCss(source) {
  return source
    .replace(/\/\*[^*]*\*+(?:[^/*][^*]*\*+)*\//g, '')
    .replace(/[\r\n\t]/g, '')
    .replace(/\s+/g, ' ')
    .trim();
}

function formatBytes(bytes) {
  return `${bytes}B`;
}

for (const [key, sourceRelative, targetRelative] of targets) {
  const sourcePath = join(themeRoot, sourceRelative);
  const targetPath = join(themeRoot, targetRelative);
  const source = readFileSync(sourcePath, 'utf8');
  const minified = minifyCss(source);

  writeFileSync(targetPath, minified, 'utf8');

  const sourceSize = statSync(sourcePath).size;
  const targetSize = statSync(targetPath).size;
  const saved = sourceSize > 0 ? (((sourceSize - targetSize) / sourceSize) * 100).toFixed(1) : '0.0';
  console.log(`${key}: ${formatBytes(sourceSize)} -> ${formatBytes(targetSize)} (${saved}% saved)`);
}
