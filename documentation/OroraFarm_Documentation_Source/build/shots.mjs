// Captures screenshots from the RUNNING app (demo workspace) with optional numbered annotations.
// Usage: BASE=http://127.0.0.1:8765 node shots.mjs [name ...]   (no names = all listed in shots-wanted.txt files)
// Annotations: annotations.json  { "<shot-name>": [ { "sel": "css", "text": "visible text (optional)", "n": 1 } ], ... }
//   Each entry draws a numbered badge + outline on the matching element before capture.
// Special per-shot options in annotations.json under "_opts": { "<name>": { "full": true, "width": 1440, "height": 900, "click": "css or text=…", "fill": {"css":"value"}, "hover": "css", "scrollTo": "css" } }
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer-core';

const here = path.dirname(fileURLToPath(import.meta.url));
const src = path.resolve(here, '..');
const BASE = process.env.BASE || 'http://127.0.0.1:8765';
const CHROME = process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const EMAIL = process.env.SHOT_EMAIL || 'demo@ororafarm.rw';
const PASSWORD = process.env.SHOT_PASSWORD || 'password';

const annFile = path.join(here, 'annotations.json');
const ann = fs.existsSync(annFile) ? JSON.parse(fs.readFileSync(annFile, 'utf8')) : {};
const opts = ann._opts || {};

const wanted = new Map();
for (const d of ['end-user', 'admin', 'technical']) {
  const f = path.join(src, d, 'shots-wanted.txt');
  if (!fs.existsSync(f)) continue;
  for (const line of fs.readFileSync(f, 'utf8').split('\n')) {
    const [name, url, note] = line.split('|').map((s) => s?.trim());
    if (name && url && url.startsWith('/') && !wanted.has(name)) wanted.set(name, { url, note });
  }
}
const only = process.argv.slice(2);
const list = [...wanted.entries()].filter(([n]) => !only.length || only.includes(n));

const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox'] });
const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });

async function login() {
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle2' });
  await page.type('input[name="email"]', EMAIL);
  await page.type('input[name="password"]', PASSWORD);
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('button[type="submit"]')]);
  if (page.url().includes('/login')) throw new Error('login failed');
}
await login();

const annotate = (items) => page.evaluate((items) => {
  document.querySelectorAll('.__ann').forEach((e) => e.remove());
  const out = [];
  for (const it of items) {
    let el = null;
    const cands = Array.from(document.querySelectorAll(it.sel));
    el = it.text ? cands.find((c) => (c.innerText || c.value || '').trim().toLowerCase().includes(it.text.toLowerCase())) : cands.find((c) => c.offsetParent !== null) || cands[0];
    if (!el) { out.push(`not found: ${it.sel} ${it.text || ''}`); continue; }
    const r = el.getBoundingClientRect();
    const box = document.createElement('div');
    box.className = '__ann';
    box.style.cssText = `position:fixed;left:${r.left - 4}px;top:${r.top - 4}px;width:${r.width + 8}px;height:${r.height + 8}px;border:3px solid #e11d48;border-radius:8px;z-index:99999;pointer-events:none;box-shadow:0 0 0 2px rgba(255,255,255,.7)`;
    const b = document.createElement('div');
    b.className = '__ann';
    b.textContent = it.n;
    const side = it.side || 'tl';
    const bx = side === 'right' ? r.right + 6 : r.left - 16;
    const by = side === 'right' ? r.top + r.height / 2 - 14 : r.top - 18;
    b.style.cssText = `position:fixed;left:${bx}px;top:${by}px;width:28px;height:28px;border-radius:50%;background:#e11d48;color:#fff;font:700 15px/28px Helvetica,Arial,sans-serif;text-align:center;z-index:100000;pointer-events:none;box-shadow:0 1px 4px rgba(0,0,0,.4)`;
    document.body.append(box, b);
  }
  return out;
}, items);

const outDir = path.join(src, 'screenshots');
fs.mkdirSync(outDir, { recursive: true });
const report = [];
for (const [name, { url }] of list) {
  const o = opts[name] || {};
  if (/^\/(login|register)/.test(url)) {
    const ctx = await browser.createBrowserContext();
    const gp = await ctx.newPage();
    await gp.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
    await gp.goto(BASE + url, { waitUntil: 'networkidle2' });
    await gp.screenshot({ path: path.join(outDir, `${name}.png`) });
    await ctx.close();
    report.push(`${name}: ok (guest)`);
    continue;
  }
  try {
    await page.setViewport({ width: o.width || 1440, height: o.height || 900, deviceScaleFactor: 1.5 });
    await page.goto(BASE + url, { waitUntil: 'networkidle2', timeout: 60000 });
    await new Promise((r) => setTimeout(r, 700));
    if (o.fill) for (const [sel, val] of Object.entries(o.fill)) { await page.click(sel, { clickCount: 3 }).catch(() => {}); await page.type(sel, val); }
    if (o.click) { await page.evaluate((c) => { const el = c.startsWith('text=') ? [...document.querySelectorAll('button,a,summary')].find((e) => e.innerText.trim().includes(c.slice(5))) : document.querySelector(c); el && el.click(); }, o.click); await new Promise((r) => setTimeout(r, 600)); }
    if (o.scrollTo) await page.evaluate((s) => document.querySelector(s)?.scrollIntoView({ block: 'center' }), o.scrollTo);
    const status = page.url().replace(BASE, '');
    const notes = ann[name] ? await annotate(ann[name]) : [];
    await page.screenshot({ path: path.join(outDir, `${name}.png`), fullPage: !!o.full });
    report.push(`${name}: ok (${status})${notes.length ? ' WARN ' + notes.join('; ') : ''}`);
  } catch (e) {
    report.push(`${name}: FAILED ${e.message.slice(0, 100)}`);
  }
}
await browser.close();
console.log(report.join('\n'));
