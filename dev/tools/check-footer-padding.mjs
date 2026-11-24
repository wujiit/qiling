import { readFileSync, readdirSync, statSync } from 'fs';
import { join } from 'path';

function searchCss(dir) {
  for (const f of readdirSync(dir)) {
    const full = join(dir, f);
    if (statSync(full).isDirectory()) {
      searchCss(full);
    } else if (f.endsWith('.css')) {
      const content = readFileSync(full, 'utf8');
      const lines = content.split('\n');
      lines.forEach((l, i) => {
        if (l.includes('.module-footer-suite') && (l.includes('padding') || l.includes('margin'))) {
          console.log(`${f}:${i+1} -> ${l.trim().slice(0, 100)}`);
        }
      });
    }
  }
}

searchCss('qiling/assets/css');
