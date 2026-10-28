// Signed-in app screens for the preview file: uses the throw-away member created by seed_member.js (token in member.json) on the LOCAL dev site and
// captures the PWA at phone size. Delete the member afterwards. Usage: node capture_member.js   (prints no credentials)
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const puppeteer = require('puppeteer-core');

const ORIGIN = 'http://localhost';
const API = ORIGIN + '/langoculturalinstitution/api/v1';
const PWA = ORIGIN + '/langoculturalinstitution/pwa';
const OUT = path.join(__dirname, 'shots');
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const EMAIL = 'preview-member@example.test';

(async () => {
  const body = JSON.parse(fs.readFileSync(path.join(__dirname, 'member.json'), 'utf8'));   // written by seed_member.js

  const browser = await puppeteer.launch({ executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: 'new', args: ['--no-sandbox', '--hide-scrollbars'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
  await page.goto(PWA + '/', { waitUntil: 'networkidle2' });
  await page.evaluate((t) => { localStorage.setItem('lango_auth_token', t); localStorage.setItem('onboarded_v1', 'true'); }, body.token);
  const snap = async (name) => { await sleep(3200); await page.screenshot({ path: path.join(OUT, 'app-in-' + name + '.jpg'), type: 'jpeg', quality: 86 }); console.log('saved', name, page.url().replace(ORIGIN, '')); };
  for (const [name, p] of [['contribute', '/contribute'], ['form-oral', '/contribute/new?type=oral_history'], ['form-word', '/contribute/new?type=language&kind=word'], ['me', '/me'], ['home', '/']]) {
    await page.goto(PWA + p, { waitUntil: 'networkidle2' });
    await snap(name);
  }
  await browser.close();
})().catch((e) => { console.error(e); process.exit(1); });
