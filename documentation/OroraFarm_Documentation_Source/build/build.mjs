// Builds the OroraFarm manuals: Markdown chapters -> HTML -> (Paged.js + Chrome) -> PDF.
// Usage: node build.mjs [end-user|admin|technical|complete|all]
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { marked } from 'marked';
import puppeteer from 'puppeteer-core';

const here = path.dirname(fileURLToPath(import.meta.url));
const src = path.resolve(here, '..');
const outDir = path.resolve(src, '../output');
const tmpDir = path.join(here, 'tmp');
fs.mkdirSync(outDir, { recursive: true });
fs.mkdirSync(tmpDir, { recursive: true });

const VERSION = process.env.DOC_VERSION || '1.0';
const DATE = process.env.DOC_DATE || new Date().toISOString().slice(0, 10);
const CHROME = process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

const MANUALS = {
  'end-user': {
    dir: 'end-user', file: 'OroraFarm_End_User_Guide.pdf', label: 'End-User Guide',
    title: 'End-User Guide', subtitle: 'Data Entry User Manual',
    audience: 'Farm data-entry staff and everyday users', cls: 'eu',
  },
  admin: {
    dir: 'admin', file: 'OroraFarm_Administrator_Guide.pdf', label: 'Administrator Guide',
    title: 'Administrator Guide', subtitle: 'Administration and Management Manual',
    audience: 'Farm owners, workspace administrators and managers', cls: 'ad',
  },
  technical: {
    dir: 'technical', file: 'OroraFarm_Technical_Documentation.pdf', label: 'Technical Documentation',
    title: 'Technical Documentation', subtitle: 'System Architecture and Developer Reference',
    audience: 'Developers, IT personnel and system maintainers', cls: 'te',
  },
};

const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const slug = (s) => s.toLowerCase().replace(/<[^>]+>/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');

function chapterFiles(dir) {
  const d = path.join(src, dir);
  return fs.readdirSync(d).filter((f) => /^\d+.*\.md$/.test(f)).sort();
}

function calloutize(html) {
  return html.replace(/<blockquote>\s*<p><strong>(Note|Warning|Tip|Example|Important|Known limitation|Known limitations)[:：]?<\/strong>:?\s*/gi, (m, kind) => {
    const k = kind.toLowerCase().startsWith('known') ? 'limit' : kind.toLowerCase() === 'important' ? 'warning' : kind.toLowerCase();
    return `<blockquote class="callout ${k}"><p><span class="callout-title">${kind.replace(/s$/, '')}</span> `;
  });
}

const missing = [];

// Copies each built PDF into the app's Help Center folder and records version/date/pages in manifest.json.
const appDocDir = path.resolve(src, '../../resources/documentation');
function publish(file, pages) {
  if (!fs.existsSync(path.dirname(appDocDir))) return;
  fs.mkdirSync(appDocDir, { recursive: true });
  fs.copyFileSync(path.join(outDir, file), path.join(appDocDir, file));
  const mf = path.join(appDocDir, 'manifest.json');
  const manifest = fs.existsSync(mf) ? JSON.parse(fs.readFileSync(mf, 'utf8')) : {};
  manifest[file] = { version: VERSION, updated: DATE, pages };
  fs.writeFileSync(mf, JSON.stringify(manifest, null, 2) + '\n');
}

function renderChapters(key, m, prefix) {
  const toc = [];
  let body = '';
  let chNo = 0;
  for (const f of chapterFiles(m.dir)) {
    const md = fs.readFileSync(path.join(src, m.dir, f), 'utf8');
    let h2 = 0, h3 = 0;
    const renderer = new marked.Renderer();
    renderer.heading = (text, level, raw) => {
      if (level === 1) {
        chNo++; h2 = 0; h3 = 0;
        const id = `${prefix}ch${chNo}`;
        toc.push({ level: 1, num: `${chNo}`, text: raw, id });
        return `<h1 id="${id}" class="chapter" data-t="${esc(raw).replace(/"/g,'&quot;')}"><span class="num">${chNo}</span><span class="ttl">${text}</span></h1>\n`;
      }
      if (level === 2) {
        h2++; h3 = 0;
        const id = `${prefix}ch${chNo}-${h2}`;
        toc.push({ level: 2, num: `${chNo}.${h2}`, text: raw, id });
        return `<h2 id="${id}"><span class="num">${chNo}.${h2}</span> ${text}</h2>\n`;
      }
      if (level === 3) {
        h3++;
        const id = `${prefix}ch${chNo}-${h2}-${h3}`;
        return `<h3 id="${id}"><span class="num">${chNo}.${h2}.${h3}</span> ${text}</h3>\n`;
      }
      return `<h${level}>${text}</h${level}>\n`;
    };
    renderer.code = (code, lang) => {
      if ((lang || '').startsWith('mermaid')) return `<div class="diagram"><pre class="mermaid" data-src="${m.dir}/${f}">${esc(code)}</pre></div>\n`;
      return `<pre class="code"><code>${esc(code)}</code></pre>\n`;
    };
    renderer.table = (header, bodyHtml) => `<div class="tablewrap"><table><thead>${header}</thead><tbody>${bodyHtml}</tbody></table></div>\n`;
    renderer.image = (href, title, text) => {
      const abs = path.resolve(src, m.dir, href);
      if (!fs.existsSync(abs)) {
        missing.push(`${m.dir}/${f}: ${href}`);
        return '';
      }
      return `<figure><img src="${pathToFileURL(abs).href}" alt="${esc(text)}"><figcaption>${esc(text)}</figcaption></figure>`;
    };
    // Heading ids: replace the "Chapter" word inside inline anchors with nothing; links to #id stay intact.
    body += `<section class="chapter-wrap">${calloutize(marked.parse(md, { renderer, gfm: true, breaks: false }))}</section>\n`;
  }
  return { toc, body };
}

function tocHtml(toc, heading = 'Contents') {
  const rows = toc
    .filter((t) => t.level <= 2)
    .map((t) => `<li class="toc-l${t.level}"><a href="#${t.id}"><span class="tn">${t.num}</span><span class="tt">${t.text}</span></a></li>`)
    .join('\n');
  return `<section class="toc"><h1 class="toc-title">${heading}</h1><ul>${rows}</ul></section>`;
}

function cover(m, extra = '') {
  return `<section class="cover ${m.cls || ''}">
  <div class="cover-top"><img src="${pathToFileURL(path.join(src, 'assets/logo.png')).href}" class="cover-logo"></div>
  <div class="cover-band">
    <div class="cover-kicker">OroraFarm</div>
    <h1 class="cover-title">${m.title}</h1>
    <div class="cover-sub">${m.subtitle}</div>
    <div class="cover-lime"></div>
    <div class="cover-meta">
      <div><span>Audience</span>${m.audience}</div>
      <div><span>Version</span>${VERSION}</div>
      <div><span>Last updated</span>${DATE}</div>
    </div>
  </div>
  <div class="cover-foot">Orora Investment Group &middot; OroraFarm Farm Management System ${extra}</div>
</section>`;
}

function manualHtml(key, prefix = '') {
  const m = MANUALS[key];
  const { toc, body } = renderChapters(key, m, prefix);
  return { toc, html: `<div class="manual" data-title="OroraFarm ${m.title}">${cover(m)}${tocHtml(toc)}${body}</div>`, m };
}

const css = fs.readFileSync(path.join(here, 'style.css'), 'utf8');

function page(content, docTitle) {
  const mermaidJs = fs.readFileSync(path.join(here, 'node_modules/mermaid/dist/mermaid.min.js'), 'utf8').replace(/<\/script/g, '<\\/script');
  const pagedJs = fs.readFileSync(path.join(here, 'node_modules/pagedjs/dist/paged.polyfill.js'), 'utf8').replace(/<\/script/g, '<\\/script');
  return `<!doctype html><html lang="en"><head><meta charset="utf-8"><title>${esc(docTitle)}</title>
<style>${css}</style>
<script>window.PagedConfig = { auto: false };</script>
<script>${mermaidJs}</script>
<script>${pagedJs}</script>
</head><body>${content}
<script>
window.__done = false;
(async () => {
  try {
    mermaid.initialize({ startOnLoad: false, theme: 'base', securityLevel: 'loose', flowchart: { htmlLabels: true, useMaxWidth: true, curve: 'basis' },
      themeVariables: { primaryColor: '#E6F1E0', primaryBorderColor: '#002B2B', primaryTextColor: '#0b2b2b', lineColor: '#4a6a6a', secondaryColor: '#F3F8E3', tertiaryColor: '#ffffff', fontFamily: 'Helvetica Neue, Arial, sans-serif', fontSize: '13px' } });
    const nodes = Array.from(document.querySelectorAll('pre.mermaid'));
    for (const n of nodes) {
      try { const id = 'm' + Math.random().toString(36).slice(2); const { svg } = await mermaid.render(id, n.textContent); n.innerHTML = svg; n.classList.add('rendered'); }
      catch (e) { n.innerHTML = '<div class="diagram-error">Diagram could not be rendered: ' + String(e.message || e).slice(0,160) + '</div>'; window.__diagramErrors = (window.__diagramErrors||0)+1; console.log('MERMAID_ERROR ' + n.dataset.src + ' :: ' + String(e.message||e).split('\\n').slice(0,3).join(' | ').slice(0,200)); }
    }
    await new Promise(r => setTimeout(r, 200));
    const previewer = new Paged.Previewer();
    await previewer.preview();
  } catch (e) { console.log('BUILD_ERROR ' + e); }
  window.__done = true;
})();
</script></body></html>`;
}

async function toPdf(htmlFile, pdfFile) {
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox', '--allow-file-access-from-files'] });
  const p = await browser.newPage();
  p.on('console', (msg) => { const t = msg.text(); if (/ERROR/.test(t)) console.log('  [page]', t); });
  await p.goto(pathToFileURL(htmlFile).href, { waitUntil: 'load', timeout: 0 });
  await p.waitForFunction('window.__done === true', { timeout: 0, polling: 500 });
  const pages = await p.evaluate(() => document.querySelectorAll('.pagedjs_page').length);
  const diagErr = await p.evaluate(() => window.__diagramErrors || 0);
  await p.pdf({ path: pdfFile, preferCSSPageSize: true, printBackground: true });
  await browser.close();
  return { pages, diagErr };
}

async function buildOne(key) {
  const { html, m } = manualHtml(key);
  const htmlFile = path.join(tmpDir, `${key}.html`);
  fs.writeFileSync(htmlFile, page(html, `OroraFarm ${m.title}`));
  const r = await toPdf(htmlFile, path.join(outDir, m.file));
  console.log(`built ${m.file}: ${r.pages} pages, ${r.diagErr} diagram errors`);
  publish(m.file, r.pages);
}

async function buildComplete() {
  const parts = ['end-user', 'admin', 'technical'].map((k, i) => ({ k, ...manualHtml(k, `p${i + 1}-`) }));
  const master = {
    title: 'Complete Documentation', subtitle: 'End-User Guide &middot; Administrator Guide &middot; Technical Documentation',
    audience: 'All OroraFarm staff, administrators and developers', cls: 'all',
  };
  const roman = ['I', 'II', 'III'];
  const contents = `<section class="toc master-toc"><h1 class="toc-title">Contents</h1><ul>${parts.map((p, i) =>
    `<li class="toc-l0"><a href="#part${i + 1}"><span class="tn">Part ${roman[i]}</span><span class="tt">${p.m.title} &mdash; ${p.m.subtitle}</span></a></li>` +
    p.toc.filter((t) => t.level === 1).map((t) => `<li class="toc-l1"><a href="#${t.id}"><span class="tn">${t.num}</span><span class="tt">${t.text}</span></a></li>`).join('')).join('')}</ul></section>`;
  const body = parts.map((p, i) => `<div id="part${i + 1}" class="part-anchor"></div>` + p.html.replace(/^<div class="manual"[^>]*>/, `<div class="manual" data-title="OroraFarm ${p.m.title}">`)).join('\n');
  const htmlFile = path.join(tmpDir, 'complete.html');
  fs.writeFileSync(htmlFile, page(cover(master) + contents + body, 'OroraFarm Complete Documentation'));
  const r = await toPdf(htmlFile, path.join(outDir, 'OroraFarm_Complete_Documentation.pdf'));
  console.log(`built OroraFarm_Complete_Documentation.pdf: ${r.pages} pages, ${r.diagErr} diagram errors`);
  publish('OroraFarm_Complete_Documentation.pdf', r.pages);
}

const target = process.argv[2] || 'all';
const keys = target === 'all' ? ['end-user', 'admin', 'technical'] : MANUALS[target] ? [target] : [];
for (const k of keys) await buildOne(k);
if (target === 'all' || target === 'complete') await buildComplete();
if (missing.length) console.log('MISSING IMAGES (omitted):\n  ' + [...new Set(missing)].join('\n  '));
