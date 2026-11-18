// Photographs the current app (Expo web dev server on :8095, API on the local XAMPP site) at the exact size of an iPhone 6.3-inch
// screenshot: 402 x 874 points at 3x = 1206 x 2622 px. Needs no puppeteer: it drives Edge through the DevTools protocol with
// Node's built-in WebSocket (Node 22+).   node capture_ios.js [name,name]   ->   raw/NN-name.png
// Every screen is taken as a first-time visitor (no sign-in), so no member data appears in them.
const { spawn } = require('child_process');
const fs = require('fs');
const os = require('os');
const path = require('path');

const EDGE = 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const BASE = 'http://localhost:8095';
const PORT = 9334;
const OUT = path.join(__dirname, 'raw');
const ONLY = process.argv[2] ? process.argv[2].split(',') : null;
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
fs.mkdirSync(OUT, { recursive: true });

// each screen: file, route, wait (ms), fresh (first-run state: the intro has not been seen), js (run after the page loaded)
const SCREENS = [
  ['home', '', 9000],
  ['explore', 'explore', 7000],
  ['learn', 'learn', 7000],
  ['dictionary', 'dictionary?q=water', 7000],
  ['translate', 'translate', 6000, { type: 'water', click: 'Translate', after: 3500 }],
  ['clans', 'clans', 7000],
  ['clan', 'clans/akwaich', 7000],
  ['heritage', 'heritage', 7000],
  ['people', 'people', 7000],
  ['quizzes', 'quiz', 6000],
  ['quiz', 'quiz/famous-langos', 6000, { click: 'Start', after: 1500 }],
  ['contribute', 'contribute', 7000],
  ['history', 'history', 7000],
  ['events', 'events', 7000],
  ['culture-lango', 'onboarding', 8000, { fresh: true }],
  ['culture-acholi', 'onboarding', 8000, { fresh: true, click: 'Acholi', after: 1800 }],
  ['phrasebook', 'phrasebook', 6000],
  ['proverbs', 'proverbs', 6000],
  ['form-word', 'contribute/new?type=language&kind=word', 6000],
  ['heritage-attire', 'heritage', 7000, { click: 'Traditional Attire', after: 2500 }],
];

(async () => {
  const profile = path.join(os.tmpdir(), 'edge-ios-capture');
  const edge = spawn(EDGE, ['--headless=new', '--disable-gpu', '--no-sandbox', '--hide-scrollbars', '--remote-debugging-port=' + PORT, '--user-data-dir=' + profile, 'about:blank'], { stdio: 'ignore' });
  let target = null;
  for (let i = 0; i < 60 && !target; i++) {
    await sleep(500);
    try { target = (await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json()).find((t) => t.type === 'page'); } catch (e) { /* not up yet */ }
  }
  if (!target) throw new Error('Edge did not start');
  const ws = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((res, rej) => { ws.onopen = res; ws.onerror = rej; });
  let id = 0;
  const pending = new Map();
  ws.onmessage = (m) => {
    const d = JSON.parse(m.data);
    if (d.id && pending.has(d.id)) { const p = pending.get(d.id); pending.delete(d.id); d.error ? p.rej(new Error(d.error.message)) : p.res(d.result); }
  };
  const send = (method, params = {}) => new Promise((res, rej) => { const i = ++id; pending.set(i, { res, rej }); ws.send(JSON.stringify({ id: i, method, params })); });
  const run = (expression) => send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });

  await send('Page.enable');
  await send('Emulation.setDeviceMetricsOverride', { width: 402, height: 874, deviceScaleFactor: 3, mobile: true });
  await send('Emulation.setTouchEmulationEnabled', { enabled: true });
  try { await send('Emulation.setSafeAreaInsetsOverride', { insets: { top: 59, bottom: 34, left: 0, right: 0 } }); } catch (e) { console.log('no safe-area emulation:', e.message); }

  const go = async (route) => { await send('Page.navigate', { url: BASE + '/' + route }); };
  const clickText = (t) => run(`(() => { const want = ${JSON.stringify(t)};
    const el = [...document.querySelectorAll('[role="button"],[role="tab"]')].find((e) => (e.innerText || '').trim().split('\\n')[0] === want)
      || [...document.querySelectorAll('div,span')].find((e) => e.children.length === 0 && (e.innerText || '').trim() === want);   // a plain label: the click bubbles up to its button
    el && el.click(); return !!el; })()`);

  // a throw-away member for the signed-in screens (local database only; removed afterwards with: node ../../showcase-source/capture/clan_seed.js clean)
  const API = 'http://localhost/langoculturalinstitution/api/v1';
  const clans = (await (await fetch(API + '/clans/options')).json()).data;
  const pw = 'Ct-' + require('crypto').randomBytes(8).toString('hex');
  const reg = await (await fetch(API + '/auth/register', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify({ name: 'Friend', email: 'ctest-friend@example.test', password: pw, clan_id: String(clans.find((c) => c.name === 'Arak').id) }) })).json();
  if (!reg.token) throw new Error('could not create the test member: ' + JSON.stringify(reg).slice(0, 200));

  let n = 0;
  for (const [file, route, wait, o = {}] of SCREENS) {
    n++;
    if (ONLY && !ONLY.includes(file)) continue;
    await go('welcome');   // any page of the app, to be on the right origin before the saved state is reset
    await sleep(2500);
    await run(`localStorage.clear(); ${o.fresh ? '' : `localStorage.setItem('onboarded_v1', 'true'); localStorage.setItem('lango_auth_token', ${JSON.stringify(reg.token)});`} true`);
    await go(route);
    for (let i = 0; i < 60; i++) {   // the first load of a route can take a while (the dev server bundles it); wait until the page has text
      await sleep(1000);
      const r = await run('(document.body && document.body.innerText || "").length');
      if (r.result.value > 30) break;
    }
    await sleep(wait);
    console.log(file, 'text:', (await run('(document.body.innerText || "").replace(/\\n+/g, " | ").slice(0, 110)')).result.value);
    if (o.type) {
      await run("(() => { const t = document.querySelector('textarea') || document.querySelector('input'); t && t.focus(); return !!t; })()");
      await send('Input.insertText', { text: o.type });
      await sleep(500);
    }
    if (o.click) { const r = await clickText(o.click); console.log('click', o.click, r.result.value); await sleep(o.after || 1000); }
    // hide the test-build link and anything that names the dev server
    await run("[...document.querySelectorAll('div,span')].filter((e) => e.children.length === 0 && /^Change server address$/.test((e.innerText || '').trim())).forEach((e) => { e.style.display = 'none'; }); true");
    const shot = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
    const name = `${String(n).padStart(2, '0')}-${file}.png`;
    fs.writeFileSync(path.join(OUT, name), Buffer.from(shot.data, 'base64'));
    console.log('saved', name);
  }
  ws.close();
  edge.kill();
  process.exit(0);
})().catch((e) => { console.error(e); process.exit(1); });
