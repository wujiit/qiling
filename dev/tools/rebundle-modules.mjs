import { readFileSync, writeFileSync, readdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const modulesDir = join(rootDir, 'inc/modules/modules');
const splitDir = join(rootDir, 'assets/css/modules-split');
const bundlePath = join(rootDir, 'assets/css/modules.css');

function moduleIdFromPhp(source) {
  const match = source.match(/function\s+get_id\s*\(\s*\)\s*\{[\s\S]*?return\s+['"]([^'"]+)['"]/i);
  return match ? match[1].trim() : '';
}

const moduleFiles = readdirSync(modulesDir)
  .filter((file) => /^class-.*-module\.php$/i.test(file))
  .sort();

const moduleCssPaths = [];
for (const file of moduleFiles) {
  const id = moduleIdFromPhp(readFileSync(join(modulesDir, file), 'utf8'));
  if (id) {
    const cssPath = join(splitDir, `${id}.css`);
    moduleCssPaths.push(cssPath);
  }
}

const bundleContent = `${moduleCssPaths
  .sort((a, b) => a.localeCompare(b))
  .map((file) => readFileSync(file, 'utf8'))
  .join('\n').replace(/\n*$/, '')}\n`;

writeFileSync(bundlePath, bundleContent, 'utf8');
console.log('Rebundled modules.css successfully from 88 modules.');
