import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const themeRoot = path.resolve(__dirname, '../..');
const languagesDir = path.join(themeRoot, 'languages');
const textDomain = 'developer-starter';

const ignoredDirs = new Set([
  '.git',
  'dev',
  'languages',
  'node_modules',
  'vendor',
]);

const singularFunctions = [
  '__',
  '_e',
  'esc_html__',
  'esc_html_e',
  'esc_attr__',
  'esc_attr_e',
];

const singularPattern = new RegExp(
  `\\b(${singularFunctions.map(escapeRegExp).join('|')})\\s*\\(\\s*(["'])((?:\\\\.|(?!\\2)[\\s\\S])*?)\\2\\s*,\\s*(["'])${escapeRegExp(textDomain)}\\4`,
  'g',
);

const pluralPattern = new RegExp(
  `\\b_n\\s*\\(\\s*(["'])((?:\\\\.|(?!\\1)[\\s\\S])*?)\\1\\s*,\\s*(["'])((?:\\\\.|(?!\\3)[\\s\\S])*?)\\3\\s*,[\\s\\S]*?,\\s*(["'])${escapeRegExp(textDomain)}\\5`,
  'g',
);

function escapeRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function listPhpFiles(dir) {
  const files = [];
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.isDirectory()) {
      if (!ignoredDirs.has(entry.name)) {
        files.push(...listPhpFiles(path.join(dir, entry.name)));
      }
      continue;
    }

    if (entry.isFile() && entry.name.endsWith('.php')) {
      files.push(path.join(dir, entry.name));
    }
  }
  return files;
}

function decodePhpString(raw, quote) {
  if (quote === "'") {
    return raw.replace(/\\\\/g, '\\').replace(/\\'/g, "'");
  }

  return raw
    .replace(/\\n/g, '\n')
    .replace(/\\r/g, '\r')
    .replace(/\\t/g, '\t')
    .replace(/\\"/g, '"')
    .replace(/\\\\/g, '\\');
}

function lineNumberAt(content, index) {
  return content.slice(0, index).split(/\r\n|\r|\n/).length;
}

function addOccurrence(messages, key, data) {
  const current = messages.get(key);
  if (current) {
    current.refs.push(data.ref);
    return;
  }
  messages.set(key, { ...data, refs: [data.ref] });
}

function extractMessages() {
  const messages = new Map();
  const files = listPhpFiles(themeRoot);

  for (const file of files) {
    const content = fs.readFileSync(file, 'utf8');
    const relative = path.relative(themeRoot, file).replace(/\\/g, '/');

    for (const match of content.matchAll(singularPattern)) {
      const msgid = decodePhpString(match[3], match[2]);
      addOccurrence(messages, `s:${msgid}`, {
        type: 'singular',
        msgid,
        ref: `${relative}:${lineNumberAt(content, match.index)}`,
      });
    }

    for (const match of content.matchAll(pluralPattern)) {
      const msgid = decodePhpString(match[2], match[1]);
      const msgidPlural = decodePhpString(match[4], match[3]);
      addOccurrence(messages, `p:${msgid}\u0000${msgidPlural}`, {
        type: 'plural',
        msgid,
        msgidPlural,
        ref: `${relative}:${lineNumberAt(content, match.index)}`,
      });
    }
  }

  return [...messages.values()].sort((a, b) => a.msgid.localeCompare(b.msgid, 'zh-Hans-CN'));
}

function poQuote(value) {
  return `"${value
    .replace(/\\/g, '\\\\')
    .replace(/"/g, '\\"')
    .replace(/\r/g, '\\r')
    .replace(/\n/g, '\\n"\n"')}"`;
}

function parsePoString(line) {
  const quoted = line.match(/"((?:\\.|[^"])*)"/);
  if (!quoted) {
    return '';
  }
  return JSON.parse(`"${quoted[1]}"`);
}

function parsePo(content) {
  const entries = [];
  let current = null;
  let field = null;

  const push = () => {
    if (current) {
      entries.push(current);
    }
    current = null;
    field = null;
  };

  for (const line of content.split(/\r\n|\r|\n/)) {
    if (line.trim() === '') {
      push();
      continue;
    }

    if (!current) {
      current = {
        comments: [],
        msgid: '',
        msgidPlural: null,
        msgstr: '',
        msgstrPlural: new Map(),
      };
    }

    if (line.startsWith('#')) {
      current.comments.push(line);
      continue;
    }

    if (line.startsWith('msgid_plural')) {
      current.msgidPlural = parsePoString(line);
      field = 'msgidPlural';
      continue;
    }

    if (line.startsWith('msgid')) {
      current.msgid = parsePoString(line);
      field = 'msgid';
      continue;
    }

    const pluralMatch = line.match(/^msgstr\[(\d+)\]/);
    if (pluralMatch) {
      const index = Number(pluralMatch[1]);
      current.msgstrPlural.set(index, parsePoString(line));
      field = `msgstr:${index}`;
      continue;
    }

    if (line.startsWith('msgstr')) {
      current.msgstr = parsePoString(line);
      field = 'msgstr';
      continue;
    }

    if (line.startsWith('"') && field) {
      const value = parsePoString(line);
      if (field === 'msgid') {
        current.msgid += value;
      } else if (field === 'msgidPlural') {
        current.msgidPlural += value;
      } else if (field === 'msgstr') {
        current.msgstr += value;
      } else if (field.startsWith('msgstr:')) {
        const index = Number(field.slice('msgstr:'.length));
        current.msgstrPlural.set(index, (current.msgstrPlural.get(index) || '') + value);
      }
    }
  }

  push();
  return entries;
}

function messageKey(entry) {
  if (entry.msgidPlural !== null && entry.msgidPlural !== undefined) {
    return `p:${entry.msgid}\u0000${entry.msgidPlural}`;
  }
  return `s:${entry.msgid}`;
}

function appendMessages(filePath, messages, options = {}) {
  const existing = fs.existsSync(filePath) ? fs.readFileSync(filePath, 'utf8') : '';
  const normalized = existing.replace(/\s*$/, '\n');
  const entries = parsePo(existing);
  const known = new Set(entries.map(messageKey));
  const additions = [];

  for (const message of messages) {
    const key = message.type === 'plural'
      ? `p:${message.msgid}\u0000${message.msgidPlural}`
      : `s:${message.msgid}`;
    if (known.has(key)) {
      continue;
    }

    additions.push(formatEntry(message, options));
  }

  if (additions.length === 0) {
    return 0;
  }

  fs.writeFileSync(filePath, `${normalized}\n${additions.join('\n\n')}\n`, 'utf8');
  return additions.length;
}

function fillEmptyTranslationsWithSource(filePath, messages) {
  let content = fs.readFileSync(filePath, 'utf8');
  let filled = 0;

  for (const message of messages) {
    if (message.type === 'plural') {
      const emptyPlural = [
        `msgid ${poQuote(message.msgid)}`,
        `msgid_plural ${poQuote(message.msgidPlural)}`,
        'msgstr[0] ""',
        'msgstr[1] ""',
      ].join('\n');
      const sourcePlural = [
        `msgid ${poQuote(message.msgid)}`,
        `msgid_plural ${poQuote(message.msgidPlural)}`,
        `msgstr[0] ${poQuote(message.msgid)}`,
        `msgstr[1] ${poQuote(message.msgidPlural)}`,
      ].join('\n');

      if (content.includes(emptyPlural)) {
        content = content.replaceAll(emptyPlural, sourcePlural);
        filled += 1;
      }
      continue;
    }

    const emptySingular = [
      `msgid ${poQuote(message.msgid)}`,
      'msgstr ""',
    ].join('\n');
    const sourceSingular = [
      `msgid ${poQuote(message.msgid)}`,
      `msgstr ${poQuote(message.msgid)}`,
    ].join('\n');

    if (content.includes(emptySingular)) {
      content = content.replaceAll(emptySingular, sourceSingular);
      filled += 1;
    }
  }

  fs.writeFileSync(filePath, content, 'utf8');
  return filled;
}

function formatEntry(message, options) {
  const refs = [...new Set(message.refs)].slice(0, 8);
  const lines = [];
  if (refs.length > 0) {
    lines.push(`#: ${refs.join(' ')}`);
  }
  lines.push(`msgid ${poQuote(message.msgid)}`);

  if (message.type === 'plural') {
    lines.push(`msgid_plural ${poQuote(message.msgidPlural)}`);
    const first = options.sourceAsTranslation ? message.msgid : '';
    const second = options.sourceAsTranslation ? message.msgidPlural : '';
    lines.push(`msgstr[0] ${poQuote(first)}`);
    lines.push(`msgstr[1] ${poQuote(second)}`);
    return lines.join('\n');
  }

  lines.push(`msgstr ${poQuote(options.sourceAsTranslation ? message.msgid : '')}`);
  return lines.join('\n');
}

function buildMo(entries) {
  const catalog = new Map();

  for (const entry of entries) {
    if (entry.msgidPlural !== null && entry.msgidPlural !== undefined) {
      const original = `${entry.msgid}\u0000${entry.msgidPlural}`;
      const indexes = [...entry.msgstrPlural.keys()].sort((a, b) => a - b);
      const translation = indexes.map((index) => entry.msgstrPlural.get(index) || '').join('\u0000');
      catalog.set(original, translation);
      continue;
    }

    catalog.set(entry.msgid, entry.msgstr || '');
  }

  const originals = [...catalog.keys()].sort();
  const translations = originals.map((original) => catalog.get(original));
  const count = originals.length;
  const tableOffset = 28;
  const originalTableOffset = tableOffset;
  const translationTableOffset = originalTableOffset + count * 8;
  let stringOffset = translationTableOffset + count * 8;

  const originalBuffers = originals.map((value) => Buffer.from(value, 'utf8'));
  const translationBuffers = translations.map((value) => Buffer.from(value, 'utf8'));
  const originalTable = [];
  const translationTable = [];

  for (const buffer of originalBuffers) {
    originalTable.push([buffer.length, stringOffset]);
    stringOffset += buffer.length + 1;
  }

  for (const buffer of translationBuffers) {
    translationTable.push([buffer.length, stringOffset]);
    stringOffset += buffer.length + 1;
  }

  const output = Buffer.alloc(stringOffset);
  output.writeUInt32LE(0x950412de, 0);
  output.writeUInt32LE(0, 4);
  output.writeUInt32LE(count, 8);
  output.writeUInt32LE(originalTableOffset, 12);
  output.writeUInt32LE(translationTableOffset, 16);
  output.writeUInt32LE(0, 20);
  output.writeUInt32LE(0, 24);

  originalTable.forEach(([length, offset], index) => {
    output.writeUInt32LE(length, originalTableOffset + index * 8);
    output.writeUInt32LE(offset, originalTableOffset + index * 8 + 4);
    originalBuffers[index].copy(output, offset);
  });

  translationTable.forEach(([length, offset], index) => {
    output.writeUInt32LE(length, translationTableOffset + index * 8);
    output.writeUInt32LE(offset, translationTableOffset + index * 8 + 4);
    translationBuffers[index].copy(output, offset);
  });

  return output;
}

function compileMo(poPath) {
  const entries = parsePo(fs.readFileSync(poPath, 'utf8'));
  const moPath = poPath.replace(/\.po$/, '.mo');
  fs.writeFileSync(moPath, buildMo(entries));
}

function main() {
  const messages = extractMessages();
  const potPath = path.join(languagesDir, `${textDomain}.pot`);
  const potAdditions = appendMessages(potPath, messages);
  const poFiles = fs.readdirSync(languagesDir)
    .filter((file) => file.startsWith(`${textDomain}-`) && file.endsWith('.po'))
    .sort();

  const result = {
    pot: potAdditions,
    po: {},
  };

  for (const file of poFiles) {
    const poPath = path.join(languagesDir, file);
    const added = appendMessages(poPath, messages, { sourceAsTranslation: true });
    const filled = fillEmptyTranslationsWithSource(poPath, messages);
    result.po[file] = { added, filled };
    compileMo(poPath);
  }

  console.log(JSON.stringify(result, null, 2));
}

main();
