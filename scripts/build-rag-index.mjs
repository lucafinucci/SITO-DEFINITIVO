#!/usr/bin/env node
/**
 * Build-time RAG indexer for finch-ai.it
 *
 *   Carica i contenuti del sito (i18n IT/EN, articoli HTML, moduli prodotto),
 *   li spezza in chunk, calcola embeddings via OpenAI e salva un indice statico
 *   in public/rag/chunks.json che il backend PHP usa per similarity search.
 *
 * Uso:
 *   OPENAI_API_KEY=sk-... npm run rag:build
 */

import { readFile, writeFile, mkdir, readdir } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { dirname, join, resolve } from 'node:path';
import { parse as parseHTML } from 'node-html-parser';
import 'dotenv/config';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = resolve(__dirname, '..');
const LOCALES_DIR = join(ROOT, 'src', 'i18n', 'locales');
const BLOG_DIR = join(ROOT, 'public', 'blog');
const MODULES_FILE = join(ROOT, 'src', 'data', 'modules.js');
const OUT_DIR = join(ROOT, 'public', 'rag');
const OUT_FILE = join(OUT_DIR, 'chunks.json');

const EMBED_MODEL = 'text-embedding-3-small';
const EMBED_DIM = 1536;
const CHUNK_CHAR_SIZE = 2000;   // ~500 tokens
const CHUNK_OVERLAP = 200;      // ~50 tokens
const BATCH_SIZE = 96;          // OpenAI accetta fino a 2048; 96 = sicuro
const BATCH_CONCURRENCY = 3;    // batch in volo simultanei

const SOLUTION_SLUGS = {
  finance: 'finance-intelligence',
  document: 'document-intelligence',
  warehouse: 'warehouse-intelligence',
  synapse: 'synapse',
  aps: 'aps',
};

const SOLUTION_TITLES = {
  finance: 'Finance Intelligence',
  document: 'Document Intelligence',
  warehouse: 'Warehouse Intelligence (OmniFlow)',
  synapse: 'Synapse',
  aps: 'APS — Advanced Planning System',
};

function log(...args) {
  console.log('[rag]', ...args);
}

function urlFor(lang, path) {
  const prefix = lang === 'en' ? '/en' : '';
  return `${prefix}${path}`;
}

/** Estrae tutte le stringhe non-banali da un JSON i18n, mantenendole nell'ordine. */
function extractStringsFromJson(node, acc = []) {
  if (typeof node === 'string') {
    const s = node.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
    if (s.length >= 3) acc.push(s);
  } else if (Array.isArray(node)) {
    node.forEach((n) => extractStringsFromJson(n, acc));
  } else if (node && typeof node === 'object') {
    for (const v of Object.values(node)) extractStringsFromJson(v, acc);
  }
  return acc;
}

/** Estrae il testo da un articolo HTML, prendendo solo il contenuto editoriale. */
function extractArticleText(html) {
  const root = parseHTML(html, {
    blockTextElements: { script: false, style: false, noscript: false },
  });
  root.querySelectorAll('script, style, noscript, nav, .navbar, .footer, .toc').forEach((n) => n.remove());
  const article = root.querySelector('article') || root.querySelector('main') || root;
  const hero = root.querySelector('.hero h1');
  const heroSub = root.querySelector('.hero__subtitle');
  const title = hero?.text?.trim() || root.querySelector('title')?.text?.trim() || '';
  const intro = heroSub?.text?.trim() || '';
  const body = article.text.replace(/\s+/g, ' ').trim();
  const combined = [title, intro, body].filter(Boolean).join('\n\n');
  return { title, text: combined };
}

/** Spezza un testo in chunk con overlap, rispettando i confini di frase quando possibile. */
function chunkText(text, size = CHUNK_CHAR_SIZE, overlap = CHUNK_OVERLAP) {
  const clean = text.replace(/\s+/g, ' ').trim();
  if (clean.length <= size) return [clean];
  const chunks = [];
  let start = 0;
  while (start < clean.length) {
    let end = Math.min(start + size, clean.length);
    if (end < clean.length) {
      const slice = clean.slice(start, end);
      const lastStop = Math.max(slice.lastIndexOf('. '), slice.lastIndexOf('! '), slice.lastIndexOf('? '));
      if (lastStop > size * 0.6) end = start + lastStop + 1;
    }
    chunks.push(clean.slice(start, end).trim());
    if (end >= clean.length) break;
    start = Math.max(end - overlap, start + 1);
  }
  return chunks.filter((c) => c.length >= 40);
}

async function loadJsonIfExists(path) {
  if (!existsSync(path)) return null;
  return JSON.parse(await readFile(path, 'utf8'));
}

/** Genera i documenti grezzi (prima del chunking) per tutte le sorgenti. */
async function collectDocuments() {
  const docs = [];

  for (const lang of ['it', 'en']) {
    const home = await loadJsonIfExists(join(LOCALES_DIR, lang, 'home.json'));
    if (home) {
      docs.push({
        source: 'home',
        lang,
        title: 'Homepage Finch-AI',
        url: urlFor(lang, '/'),
        text: extractStringsFromJson(home).join('\n'),
      });
    }

    for (const [key, slug] of Object.entries(SOLUTION_SLUGS)) {
      const sol = await loadJsonIfExists(join(LOCALES_DIR, lang, 'solutions', `${key}.json`));
      if (sol) {
        docs.push({
          source: `solution:${key}`,
          lang,
          title: SOLUTION_TITLES[key],
          url: urlFor(lang, `/soluzioni/${slug}`),
          text: extractStringsFromJson(sol).join('\n'),
        });
      }
    }

    const legal = await loadJsonIfExists(join(LOCALES_DIR, lang, 'legal.json'));
    if (legal) {
      if (legal.privacy) {
        docs.push({
          source: 'legal:privacy',
          lang,
          title: lang === 'en' ? 'Privacy Policy' : 'Informativa Privacy',
          url: urlFor(lang, '/privacy-policy'),
          text: extractStringsFromJson(legal.privacy).join('\n'),
        });
      }
      if (legal.cookie) {
        docs.push({
          source: 'legal:cookie',
          lang,
          title: lang === 'en' ? 'Cookie Policy' : 'Informativa Cookie',
          url: urlFor(lang, '/cookie-policy'),
          text: extractStringsFromJson(legal.cookie).join('\n'),
        });
      }
    }
  }

  try {
    const modulesMod = await import(pathToFileURL(MODULES_FILE).href);
    const modules = modulesMod.modules || [];
    const text = modules
      .map((m) => `${m.title}\n${m.desc}\nOutput: ${m.output}`)
      .join('\n\n');
    docs.push({
      source: 'modules',
      lang: 'it',
      title: 'Moduli AI Finch-AI',
      url: urlFor('it', '/#moduli'),
      text,
    });
  } catch (e) {
    log('warn: impossibile caricare modules.js:', e.message);
  }

  const itBlogFiles = (await readdir(BLOG_DIR)).filter((f) => f.endsWith('.html'));
  for (const file of itBlogFiles) {
    const slug = file.replace(/\.html$/, '');
    const html = await readFile(join(BLOG_DIR, file), 'utf8');
    const { title, text } = extractArticleText(html);
    if (text.length < 200) continue;
    docs.push({
      source: `article:${slug}`,
      lang: 'it',
      title: title || slug,
      url: urlFor('it', `/blog/${slug}`),
      text,
    });
  }

  const enBlogDir = join(BLOG_DIR, 'en');
  if (existsSync(enBlogDir)) {
    const enFiles = (await readdir(enBlogDir)).filter((f) => f.endsWith('.html'));
    for (const file of enFiles) {
      const slug = file.replace(/\.html$/, '');
      const html = await readFile(join(enBlogDir, file), 'utf8');
      const { title, text } = extractArticleText(html);
      if (text.length < 200) continue;
      docs.push({
        source: `article:${slug}`,
        lang: 'en',
        title: title || slug,
        url: urlFor('en', `/blog/${slug}`),
        text,
      });
    }
  }

  return docs;
}

function buildChunks(docs) {
  const chunks = [];
  let id = 0;
  for (const doc of docs) {
    const parts = chunkText(doc.text);
    parts.forEach((part, idx) => {
      chunks.push({
        id: `c${id++}`,
        text: `${doc.title}\n\n${part}`,
        meta: {
          source: doc.source,
          lang: doc.lang,
          title: doc.title,
          url: doc.url,
          chunk_index: idx,
          total_chunks: parts.length,
        },
      });
    });
  }
  return chunks;
}

async function embedBatch(texts, apiKey) {
  const res = await fetch('https://api.openai.com/v1/embeddings', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${apiKey}`,
    },
    body: JSON.stringify({ model: EMBED_MODEL, input: texts }),
  });
  if (!res.ok) {
    const errBody = await res.text();
    throw new Error(`OpenAI embeddings ${res.status}: ${errBody.slice(0, 500)}`);
  }
  const json = await res.json();
  return json.data.map((d) => d.embedding);
}

async function main() {
  const dryRun = process.env.RAG_DRY_RUN === '1' || process.argv.includes('--dry-run');
  const apiKey = process.env.OPENAI_API_KEY;
  if (!apiKey && !dryRun) {
    console.error('[rag] ERROR: OPENAI_API_KEY non impostata (usa --dry-run per testare senza embeddings)');
    process.exit(1);
  }

  log('Raccolta contenuti...');
  const docs = await collectDocuments();
  log(`Documenti raccolti: ${docs.length}`);
  for (const d of docs) {
    log(`  · [${d.lang}] ${d.source.padEnd(28)} ${d.text.length} char  ${d.url}`);
  }

  const chunks = buildChunks(docs);
  log(`Chunk generati: ${chunks.length}`);

  if (dryRun) {
    log('DRY RUN: skip embedding API, scrivo indice senza vettori');
    chunks.forEach((c) => { c.vector = []; c.norm = 0; });
  } else {
    log(`Embedding via ${EMBED_MODEL} (batch=${BATCH_SIZE}, concurrency=${BATCH_CONCURRENCY})...`);
    const batches = [];
    for (let i = 0; i < chunks.length; i += BATCH_SIZE) {
      batches.push({ start: i, slice: chunks.slice(i, i + BATCH_SIZE) });
    }
    let done = 0;
    let cursor = 0;
    const worker = async () => {
      while (cursor < batches.length) {
        const my = batches[cursor++];
        const vectors = await embedBatch(my.slice.map((c) => c.text), apiKey);
        my.slice.forEach((c, idx) => {
          c.vector = vectors[idx];
          // Pre-calcola la norma: a runtime PHP fa solo dot product.
          let n = 0;
          for (let k = 0; k < vectors[idx].length; k++) n += vectors[idx][k] * vectors[idx][k];
          c.norm = Math.sqrt(n);
        });
        done += my.slice.length;
        log(`  ${done}/${chunks.length}`);
      }
    };
    await Promise.all(Array.from({ length: BATCH_CONCURRENCY }, worker));
  }

  if (!existsSync(OUT_DIR)) await mkdir(OUT_DIR, { recursive: true });
  const payload = {
    version: 1,
    model: EMBED_MODEL,
    dim: EMBED_DIM,
    generated_at: new Date().toISOString(),
    chunk_count: chunks.length,
    chunks,
  };
  await writeFile(OUT_FILE, JSON.stringify(payload));
  const sizeMB = ((await readFile(OUT_FILE)).length / 1024 / 1024).toFixed(2);
  log(`Salvato ${OUT_FILE} (${sizeMB} MB, ${chunks.length} chunk)`);
}

main().catch((e) => {
  console.error('[rag] FATAL:', e);
  process.exit(1);
});
