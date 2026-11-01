// Renders the Google Play graphics from screens.html and feature.html into the play-store folder.
// NODE_PATH=<folder holding puppeteer-core>/node_modules node render_assets.js
const path = require('path');
const fs = require('fs');
const puppeteer = require('puppeteer-core');
const OUT = path.join(__dirname, '..');
const shots = path.join(OUT, 'screenshots');
fs.mkdirSync(shots, { recursive: true });
(async () => {
  const browser = await puppeteer.launch({ executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: 'new', args: ['--no-sandbox'] });
  const open = async (file, w, h) => {
    const page = await browser.newPage();
    await page.setViewport({ width: w, height: h, deviceScaleFactor: 1 });
    await page.goto('file:///' + path.join(__dirname, file).replace(/\\/g, '/'), { waitUntil: 'networkidle0', timeout: 120000 });
    await page.evaluate(() => document.fonts.ready);
    return page;
  };
  const sp = await open('screens.html', 1180, 2000);
  for (let i = 1; i <= 8; i++) {
    const el = await sp.$('#s' + i);
    await el.screenshot({ path: path.join(shots, 'phone-' + String(i).padStart(2, '0') + '.png') });
  }
  const fp = await open('feature.html', 1100, 560);
  await (await fp.$('#f1')).screenshot({ path: path.join(OUT, 'feature-graphic-1024x500.png') });
  await browser.close();
  console.log('rendered 8 screenshots + the feature graphic');
})().catch((e) => { console.error(e); process.exit(1); });
