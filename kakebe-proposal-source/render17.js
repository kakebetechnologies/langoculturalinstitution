// Two-pass render (17-page edition): pass 1 prints the PDF to read each heading's page, pass 2 prints again with the contents page numbers filled in.
// Usage: node render.js [out.pdf]
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const puppeteer = require('puppeteer-core');

const dir = __dirname;
const out = process.argv[2] || path.join(dir, 'proposal17.pdf');
const EDGE = 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const PY = process.env.PY || 'python';

async function print(file, pdf) {
  const browser = await puppeteer.launch({ executablePath: EDGE, headless: 'new', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.goto('file:///' + file.replace(/\\/g, '/'), { waitUntil: 'load', timeout: 120000 });
  await page.evaluate(() => Promise.race([document.fonts.ready, new Promise((r) => setTimeout(r, 10000))]));
  await new Promise((r) => setTimeout(r, 1500));
  await page.pdf({ path: pdf, preferCSSPageSize: true, printBackground: true, outline: true, tagged: true, margin: { top: 0, right: 0, bottom: 0, left: 0 } });
  await browser.close();
}

(async () => {
  const html = path.join(dir, 'proposal17.html');
  const pass1 = path.join(dir, 'pass17.pdf');
  execFileSync('node', [path.join(dir, 'build17.js')], { stdio: 'inherit' });
  await print(html, pass1);
  // read headings -> pages from the PDF text (the outline of pass 1 is used as a cross-check)
  execFileSync(PY, ['-X', 'utf8', '-I', path.join(dir, 'tocpages.py'), pass1, path.join(dir, 'toc-keys17.json'), path.join(dir, 'toc17.json')], { stdio: 'inherit' });
  execFileSync('node', [path.join(dir, 'build17.js'), path.join(dir, 'toc17.json')], { stdio: 'inherit' });
  await print(html, out);
  console.log('PDF written', out);
})();
