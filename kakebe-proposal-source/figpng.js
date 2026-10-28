// Renders every figure in ./figures to a 2x PNG in ./figures/png (used for visual review and for the editable Doc).
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer-core');

(async () => {
  const dir = path.join(__dirname, 'figures');
  const out = path.join(dir, 'png');
  fs.mkdirSync(out, { recursive: true });
  const browser = await puppeteer.launch({ executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: 'new', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  for (const f of fs.readdirSync(dir).filter((x) => x.endsWith('.svg'))) {
    const svg = fs.readFileSync(path.join(dir, f), 'utf8');
    const m = svg.match(/viewBox="0 0 (\d+) (\d+)"/);
    const w = +m[1], h = +m[2];
    await page.setViewport({ width: w, height: h, deviceScaleFactor: 2 });
    await page.setContent(`<!doctype html><html><head><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"><style>html,body{margin:0;background:#fff}svg{display:block;width:${w}px;height:${h}px}</style></head><body>${svg}</body></html>`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.evaluate(() => Promise.race([document.fonts.ready, new Promise((r) => setTimeout(r, 8000))]));
    await new Promise((r) => setTimeout(r, 600));
    await page.screenshot({ path: path.join(out, f.replace('.svg', '.png')), clip: { x: 0, y: 0, width: w, height: h } });
    console.log('png', f);
  }
  await browser.close();
})();
