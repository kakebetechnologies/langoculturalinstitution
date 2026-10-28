// Renders preview.html to PDF with Edge, and reports any page whose content overflows its box. Usage: node render2.js [out.pdf]
const path = require('path');
const url = require('url');
const { execFileSync } = require('child_process');
const puppeteer = require('puppeteer-core');

const dir = __dirname;
const out = process.argv[2] || path.join(dir, 'preview.pdf');

(async () => {
  
  execFileSync('node', [path.join(dir, 'build3.js')], { stdio: 'inherit' });
  const browser = await puppeteer.launch({ executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: 'new', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.goto(url.pathToFileURL(path.join(dir, 'preview.html')).href, { waitUntil: 'load', timeout: 120000 });
  await page.evaluate(() => Promise.race([document.fonts.ready, new Promise((r) => setTimeout(r, 10000))]));
  await new Promise((r) => setTimeout(r, 1500));
  const over = await page.evaluate(() => [...document.querySelectorAll('.pg')].map((p, i) => {
    const b = p.querySelector('.body');
    if (!b) return null;
    const top = b.getBoundingClientRect().top;
    const last = [...b.children].reduce((m, c) => Math.max(m, c.getBoundingClientRect().bottom), 0);
    const used = last - top, box = b.clientHeight;
    return { page: i + 1, used: Math.round(used), box: Math.round(box), spare: Math.round(box - used) };
  }).filter(Boolean));
  console.log('page fit (px; spare < 0 means overflow):');
  console.log(over.map((o) => `p${o.page}: spare ${o.spare}`).join(' | '));
  await page.pdf({ path: out, preferCSSPageSize: true, printBackground: true, outline: false, tagged: true, margin: { top: 0, right: 0, bottom: 0, left: 0 } });
  await browser.close();
  console.log('PDF written', out);
})();
