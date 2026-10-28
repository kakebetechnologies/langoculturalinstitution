// Screenshots the installable web app (PWA) at phone size into ./shots (JPEG, 2x).
const fs = require('fs'); const path = require('path'); const puppeteer = require('puppeteer-core');
const SITE = 'http://localhost/langoculturalinstitution/pwa';
const OUT = path.join(__dirname, 'shots');
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const SCREENS = [['home', '/'], ['explore', '/explore'], ['contribute', '/contribute'], ['learn', '/learn'], ['dictionary', '/dictionary'], ['translate', '/translate'],
  ['clans', '/clans'], ['districts', '/districts'], ['people', '/people'], ['events', '/events'], ['support', '/support'], ['connect', '/connect'], ['assistant', '/assistant']];
(async () => {
  const browser = await puppeteer.launch({ executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: 'new', args: ['--no-sandbox', '--hide-scrollbars'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
  const snap = async (name) => { await sleep(3000); await page.screenshot({ path: path.join(OUT, 'app-' + name + '.jpg'), type: 'jpeg', quality: 86 }); console.log('saved', name, page.url().replace('http://localhost', '')); };

  await page.goto(SITE + '/', { waitUntil: 'networkidle2' }); await sleep(4500);
  await snap('onboarding-1');
  console.log(await page.evaluate(() => document.body.innerText.slice(0, 300)));
  await page.evaluate(() => localStorage.setItem('onboarded_v1', 'true'));
  for (const [name, p] of SCREENS) {
    await page.goto(SITE + p, { waitUntil: 'networkidle2' }); await sleep(2500);
    await snap(name);
    if (name === 'home') { await page.evaluate(() => { const s = [...document.querySelectorAll('div')].find((d) => d.scrollHeight > d.clientHeight + 200 && getComputedStyle(d).overflowY !== 'visible'); if (s) s.scrollTop = 760; }); await snap('home-2'); }
  }
  await browser.close();
})();
