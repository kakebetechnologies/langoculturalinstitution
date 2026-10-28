// Screenshots the public site, the admin panel and the PWA into ./shots (JPEG). Usage: node capture.js site|admin|app
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer-core');

const SITE = 'http://localhost/langoculturalinstitution';
const OUT = path.join(__dirname, 'shots');
fs.mkdirSync(OUT, { recursive: true });
const EDGE = 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const PUBLIC = [
  ['home', '/'], ['about', '/about'], ['leadership', '/leadership'], ['history', '/history'],
  ['districts', '/districts'], ['district-lira-city', '/districts/lira-city'], ['people', '/people'], ['person', '/people/apollo-milton-obote'],
  ['clans', '/clans'], ['clan', '/clans/atek'], ['heritage', '/heritage'], ['heritage-item', '/heritage/music'],
  ['dictionary', '/language/dictionary'], ['translator', '/language/translate'], ['assistant', '/assistant'],
  ['news', '/news'], ['events', '/events'], ['event', '/events/annual-clans-convention'], ['programs', '/programs'],
  ['contribute', '/contribute'], ['donate', '/donate'], ['transparency', '/transparency'], ['professionals', '/professionals'],
  ['search', '/search?q=lira'], ['developers', '/developers'], ['contact', '/contact'], ['diaspora', '/diaspora'], ['library', '/library'],
];

const ADMIN = [
  ['dashboard', ''], ['notifications', '?m=notifications'], ['contributions', '?m=contributions'], ['words', '?m=words'],
  ['clans', '?m=clans'], ['clan-quick', '?m=clan_quick'], ['dictionary', '?m=dictionary'], ['translations', '?m=translations'],
  ['districts', '?m=districts'], ['notables', '?m=notables'], ['history', '?m=history_sections'], ['posts', '?m=posts'],
  ['events', '?m=events'], ['registrations', '?m=event_registrations'], ['programs', '?m=programs'], ['donations', '?m=donations'],
  ['users', '?m=users'], ['settings', '?m=settings'], ['audit', '?m=audit'], ['backup', '?m=backup'],
];

// Scrolls the whole page once so scroll-reveal sections, lazy images and the animated counters all fire, then returns to the top.
async function settle(page) {
  await page.evaluate(async () => {
    const step = 500;
    for (let y = 0; y < document.body.scrollHeight + step; y += step) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 140)); }
  });
  await sleep(1600);
  await page.evaluate(() => window.scrollTo(0, 0));
  await sleep(400);
}

// Keeps menus closed and hides the floating "Ask the Assistant" button, so a full-page capture shows only page content.
const CLEAN_CSS = '.nav ul{opacity:0!important;visibility:hidden!important;transition:none!important}.assistant-fab,.to-top,#toTop{display:none!important}';

async function shot(page, name, { full = true, quality = 80 } = {}) {
  await page.addStyleTag({ content: CLEAN_CSS }).catch(() => {});
  if (full) await settle(page);
  await sleep(500);
  await page.screenshot({ path: path.join(OUT, name + '.jpg'), type: 'jpeg', quality, fullPage: full });
  console.log('saved', name);
}

(async () => {
  const mode = process.argv[2] || 'site';
  const browser = await puppeteer.launch({ executablePath: EDGE, headless: 'new', args: ['--no-sandbox', '--hide-scrollbars'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1 });

  if (mode === 'site') {
    for (const [name, p] of PUBLIC) {
      const res = await page.goto(SITE + p, { waitUntil: 'networkidle2', timeout: 60000 }).catch((e) => ({ status: () => 'ERR ' + e.message }));
      console.log(name, res.status());
      await shot(page, 'site-' + name);
    }
  }

  if (mode === 'admin') {
    await page.setViewport({ width: 1440, height: 1440, deviceScaleFactor: 1 });   // taller window: the admin screens are shown in square frames
    const creds = JSON.parse(fs.readFileSync(path.join(__dirname, 'creds.json'), 'utf8'));
    await page.goto(SITE + '/admin/', { waitUntil: 'networkidle2' });
    await shot(page, 'admin-login', { full: false });
    await page.type('input[name=email]', creds.email);
    await page.type('input[name=password]', creds.password);
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('button.btn--primary')]);
    const ONLY = process.env.ONLY ? process.env.ONLY.split(',') : null;   // e.g. ONLY=dashboard,contributions
    for (const [name, q] of ADMIN.filter(([n]) => !ONLY || ONLY.includes(n))) {
      const res = await page.goto(SITE + '/admin/' + q, { waitUntil: 'networkidle2', timeout: 60000 });
      console.log(name, res.status(), page.url().replace(SITE, ''));
      await shot(page, 'admin-' + name, { full: false });
    }
    // The review screen of the first sample submission, found from the moderation list.
    if (ONLY && !ONLY.includes('review')) { await browser.close(); return; }
    await page.goto(SITE + '/admin/?m=contributions', { waitUntil: 'networkidle2' });
    const links = await page.$$eval('a', (as) => as.map((a) => a.getAttribute('href')).filter((h) => h && /contributions/.test(h) && /id=/.test(h)));
    console.log('contribution links', links.slice(0, 4));
    if (links[0]) {
      await page.goto(new URL(links[0], SITE + '/admin/').href, { waitUntil: 'networkidle2' });
      await shot(page, 'admin-contribution-review', { full: false });
    }
  }

  await browser.close();
})();
