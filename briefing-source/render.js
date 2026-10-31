// Renders briefing.html to an A4 PDF (and, with "png", to one image per page for checking).
// NODE_PATH=<folder holding puppeteer-core>/node_modules node render.js <out.pdf> [png <folder>]
const path = require('path');
const fs = require('fs');
const puppeteer = require('puppeteer-core');
const [, , out, mode, pngDir] = process.argv;
(async () => {
  const browser = await puppeteer.launch({ executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: 'new', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  await page.goto('file:///' + path.join(__dirname, 'briefing.html').replace(/\\/g, '/'), { waitUntil: 'networkidle0', timeout: 120000 });
  await page.evaluate(() => document.fonts.ready);
  if (mode === 'png') {
    fs.mkdirSync(pngDir, { recursive: true });
    await page.setViewport({ width: 794, height: 1123, deviceScaleFactor: 2 });
    const pages = await page.$$('.page');
    for (let i = 0; i < pages.length; i++) await pages[i].screenshot({ path: path.join(pngDir, 'p' + (i + 1) + '.png') });
  }
  await page.pdf({ path: out, format: 'A4', printBackground: true, preferCSSPageSize: true });
  await browser.close();
  console.log('saved', out);
})().catch((e) => { console.error(e); process.exit(1); });
