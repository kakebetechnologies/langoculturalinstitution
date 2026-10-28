// Builds preview.html: the "Platform Preview" companion file (screenshots and mockups) to the Kakebe proposal. Usage: node build3.js
const fs = require('fs');
const path = require('path');

const dir = __dirname;
const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const inline = (s) => esc(s).replace(/\*\*(.+?)\*\*/g, '<b>$1</b>').replace(/\*(.+?)\*/g, '<i>$1</i>');
const CLIENT = 'Lango Cultural Foundation';
const DATE = 'October 2026';
const COPY = 'Copyright @KakebeTechnologies · Platform Preview for the Lango Cultural Foundation';
const CONTACT = { name: 'Sedrick Otolo', title: 'Director, Kakebe Technologies Limited', phone: '0777 676 206', email: 'ot.sedrick@gmail.com',
  office: 'Rm B1, Plot 43, Renzo City House, Obote Avenue, Lira City, Uganda', web: 'www.kakebe.tech' };

// ---------- building blocks ----------
// A browser/laptop frame around a screenshot. `ratio` crops it to a fixed shape (top of the screen kept); `dots` are numbered markers in % of the image.
const frame = (img, label, { ratio, sm, dots = [], style = '' } = {}) => `<div class="bf${sm ? ' sm' : ''}"${style ? ` style="${style}"` : ''}><div class="bar"><i></i><i></i><i></i><span>${esc(label)}</span></div>
  <div class="scr${ratio ? ' c' : ''}"${ratio ? ` style="--r:${ratio}"` : ''}><img src="img3/${img}.jpg" alt="${esc(label)}">${dots.map(([n, x, y]) => `<div class="dotn" style="left:${x}%;top:${y}%">${n}</div>`).join('')}</div></div>`;
const phone = (img, alt, style = '') => `<div class="dev"${style ? ` style="${style}"` : ''}><div class="scr"><img src="img3/${img}.jpg" alt="${esc(alt)}"></div></div>`;
const cap = (t, x, cls = '') => `<div class="cap2 ${cls}"><b>${esc(t)}</b>${esc(x)}</div>`;
const item = (inner, t, x, cls) => `<div>${inner}${t ? cap(t, x, cls) : ''}</div>`;
const theme = (x) => `<div class="theme">${inline(x)}</div>`;
const checks = (items, cols2) => `<ul class="checks${cols2 ? ' cols2' : ''}">${items.map((i) => `<li>${inline(i)}</li>`).join('')}</ul>`;
const key = (label, items) => `<div class="key"><div class="lab">${esc(label)}</div><div class="card">${checks(items)}</div></div>`;
const tiles = (items) => `<div class="tiles small" style="grid-template-columns:repeat(${items.length},1fr)">${items.map(([k, v]) => `<div class="tile"><div class="k">${esc(k)}</div><div class="v">${esc(v)}</div></div>`).join('')}</div>`;
const steps = (cols, items) => `<div class="steps" style="grid-template-columns:repeat(${cols},1fr)">${items.map(([t, x], i) => `<div class="step"><div class="no">${i + 1}</div><div class="t">${esc(t)}</div><div class="x">${esc(x)}</div></div>`).join('')}</div>`;
const arrow = '<div class="arrow"><svg viewBox="0 0 24 24"><path d="M4 12h15M13 5l7 7-7 7" fill="none" stroke="#E01E26" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg></div>';
const foot = (n) => `<div class="foot"><div class="pill">Page ${n}</div><div class="copy">${COPY}</div></div>`;
const bandHtml = (b) => `<div class="band" style="background-image:url('img2/${b.photo}')"><div class="qm">&ldquo;</div><div class="q">${esc(b.quote)}</div></div>`;

// laptop + laptop + phone, used on the cover and on page 3
const hero = () => `<div class="hero">
  <div class="la">${frame('w-home-hero', 'Public website', { ratio: '1440/880' })}<div class="base"></div></div>
  <div class="lb">${frame('a-dashboard', 'Admin control panel', { ratio: '1440/900' })}<div class="base"></div></div>
  <div class="pp">${phone('app-home', 'Lango app')}</div></div>`;

// ---------- pages ----------
const pages = [];
const add = (title, html, extra = {}) => pages.push({ title, html, ...extra });

add(['The Platform', 'at a Glance'], `
  <p>One platform, three connected parts. Everything below is a screen from the working build.</p>
  ${hero()}
  ${tiles([['Public website', 'Bilingual, phone-first, with the Language Hub'], ['Admin control panel', 'Roles, a review queue and records, no coding'], ['Mobile app', 'Android and iOS, offline first']])}
  ${theme('**One platform, one review queue.** A word suggested on a phone appears on the same screen staff use for the website.')}`);

add(['Website:', 'Home Page'], `
  <div style="margin-bottom:5mm">${frame('w-home-hero', 'Public website · Home')}</div>
  <div class="g2">
    ${item(frame('w-home-lang', 'Home · Language', { sm: true }), 'Language Hub', 'Dictionary search, word of the day and the translator, on the home page.')}
    ${item(frame('w-home-contrib', 'Home · Contribute', { sm: true }), 'Contribution engine', 'Four simple steps invite visitors to add to the archive.')}
  </div>
  ${theme('**Fast, bilingual and phone first.** Visitors switch between English and Leb Lango in one tap.')}`);

add(['Website:', 'Heritage and History'], `
  <div class="g2">
    ${item(frame('w-history', 'Public website · History', { sm: true }), 'History', 'Ten chapters with a timeline and sources.')}
    ${item(frame('w-districts', 'Public website · Districts', { sm: true }), 'Districts and Lira City', 'An atlas of the sub-region with maps.')}
    ${item(frame('w-clans', 'Public website · Clans', { sm: true }), 'Clan Explorer', 'A searchable page for every registered clan.')}
    ${item(frame('w-heritage', 'Public website · Heritage', { sm: true }), 'Living heritage', 'Ceremonies, crafts, music, biodiversity and sites.')}
  </div>
  ${key('Also on the website', ['Leadership and Notable Langos', 'News, events with tickets, and programmes', 'Library of documents, audio and video', 'Giving, volunteering and diaspora forms'])}`);

add(['Website:', 'Language and Community'], `
  <div class="g2">
    ${item(frame('w-dictionary', 'Public website · Dictionary', { sm: true }), 'Dictionary', 'Search by word, browse A to Z, with examples.')}
    ${item(frame('w-translator', 'Public website · Translator', { sm: true }), 'Translator', 'Built on the dictionary, not machine translation.')}
    ${item(frame('w-assistant', 'Public website · Assistant', { sm: true }), 'Cultural Assistant', 'Answers only from reviewed content, with sources.')}
    ${item(frame('w-contribute', 'Public website · Contribute', { sm: true }), 'Contribute', 'Guided forms for words, stories, sites and more.')}
  </div>
  <div class="g3">
    ${item(frame('w-events', 'Events', { sm: true }), 'Events', 'Register and get a ticket.')}
    ${item(frame('w-donate', 'Giving', { sm: true }), 'Giving', 'Pledges and mobile money.')}
    ${item(frame('w-transparency', 'Transparency', { sm: true }), 'Transparency', 'Where every gift goes.')}
  </div>`);

add(['Admin:', 'Dashboard'], `
  ${frame('a-dashboard', 'Admin control panel · Dashboard', { dots: [[1, 16.7, 30.7], [2, 73, 6.6], [3, 35.4, 20.7], [4, 56.3, 47.4], [5, 96.2, 47.4]] })}
  <div class="legend">
    <div class="lg"><div class="n">1</div><div><b>Role-based menu</b>Staff see only the screens their role allows.</div></div>
    <div class="lg"><div class="n">2</div><div><b>Notification bell</b>New contributions, words and messages appear as they arrive.</div></div>
    <div class="lg"><div class="n">3</div><div><b>Attention counters</b>What needs a decision today, at a glance.</div></div>
    <div class="lg"><div class="n">4</div><div><b>Contributions per month</b>See how the archive is growing.</div></div>
    <div class="lg"><div class="n">5</div><div><b>Archive at a glance</b>Clans, dictionary entries, heritage items and gifts.</div></div>
  </div>
  ${theme('**No coding needed.** Staff publish, review and report from one place.')}`);

add(['Admin:', 'Moderation'], `
  <div style="margin-bottom:3mm">${frame('a-list', 'Admin control panel · Contributions')}${cap('Review queue', 'Every submission from the website and the app lands in one list.')}</div>
  <div style="margin-bottom:4mm">${frame('a-review-s', 'Admin control panel · Review')}${cap('Review screen', 'Read the submission, check the place and media, set the status, then publish.')}</div>
  ${theme('**Every contribution is read by a person before it is published.**')}`);

add(['Admin:', 'Content Tools'], `
  <div class="stack">
    <div>${frame('a-dict', 'Admin control panel · Dictionary', { ratio: '1178/360' })}${cap('Dictionary', 'Add, edit and verify words, filtered by status and expert review.')}</div>
    <div>${frame('a-trans', 'Admin control panel · Translations', { ratio: '1178/360' })}${cap('Translations', 'Leb Lango wording for every phrase of the interface, entered by experts.')}</div>
    <div>${frame('a-clan', 'Admin control panel · Clans', { ratio: '1178/360' })}${cap('Clans in bulk', 'Paste many clan names at once to build the clan register quickly.')}</div>
  </div>`);

add(['Mobile App:', 'Home and Contribute'], `
  <div class="g3" style="margin:0 4mm 5mm">
    ${item(phone('app-onboarding-1', 'Welcome'), 'Welcome', 'A short introduction, then sign in or browse as a guest.', 'mid')}
    ${item(phone('app-home', 'Home'), 'Home', 'Word of the day, shortcuts and a tile for every section.', 'mid')}
    ${item(phone('app-in-contribute', 'Contribute'), 'Contribute', 'Guided forms for words, proverbs, stories and sites.', 'mid')}
  </div>
  ${tiles([['Offline first', 'Record with no signal; it sends when connected'], ['Voice, photo, GPS', 'Capture stories, sites and species in the field'], ['Points and badges', 'Levels from Bronze to Platinum']])}
  ${theme('**Five tabs, always in reach.** Home, Explore, Contribute, Learn and Me sit in one floating bar.')}
  ${key('One app, three ways', ['Android on Google Play', 'iOS on the App Store', 'An installable web app for anyone without a store'])}`);

add(['Mobile App:', 'More Screens'], `
  <div class="g4">
    ${item(phone('app-explore', 'Explore'), 'Explore', 'Clans, districts, history and heritage.')}
    ${item(phone('app-learn', 'Learn'), 'Learn', 'Dictionary, translator and proverbs.')}
    ${item(phone('app-dictionary', 'Dictionary'), 'Dictionary', 'Search or browse A to Z, offline.')}
    ${item(phone('app-translate', 'Translator'), 'Translator', 'English and Leb Lango.')}
    ${item(phone('app-clans', 'Clan Explorer'), 'Clan Explorer', 'Find your clan or register under it.')}
    ${item(phone('app-events', 'Events'), 'Events', 'Upcoming events, with registration.')}
    ${item(phone('app-in-form-word', 'Suggest a word'), 'Suggest a word', 'Reviewed before it goes live.')}
    ${item(phone('app-in-me', 'Me'), 'Me', 'Points, levels and badges.')}
  </div>`);

add(['From Contribution', 'to Archive'], `
  <p>The same flow serves the website and the app. A member records on a phone, staff review on a laptop, and the record is published for everyone.</p>
  <div class="flow">
    <div class="col" style="width:38mm">${phone('app-in-form-oral', 'Contribution form')}${cap('Capture', 'On the phone, even offline.', 'mid')}</div>
    ${arrow}
    <div class="col" style="width:58mm">${frame('a-review-s', 'Admin · Review', { sm: true })}${cap('Review', 'Staff read, edit and decide.', 'mid')}</div>
    ${arrow}
    <div class="col" style="width:58mm">${frame('w-heritage-item', 'Website · Published', { sm: true })}${cap('Publish', 'Live on the website and in the app.', 'mid')}</div>
  </div>
  ${steps(3, [['Capture', 'on the website or app, even offline'], ['Submit', 'a tracking code is issued at once'], ['Review', 'a moderator reads, edits and decides'],
    ['Specialist check', 'clan leader or review panel, where needed'], ['Publish', 'with contributor and source recorded'], ['Notify', 'in-app and push, with points and badges']])}
  ${theme('**The platform never invents culture.** Content comes only from the Foundation, published sources shown on the page, or contributions a reviewer has approved.')}`);

add(['See It', 'Live'], `
  <p>A live demonstration shows every part of the platform, on a laptop and on a phone.</p>
  ${steps(4, [['Meet', 'a demonstration and a 90-minute workshop'], ['Agree', 'scope, then sign the agreement'], ['Kick off', 'week 1 begins'], ['Launch', 'end of week 8']])}
  <h3 class="sub">About these screens</h3>
  ${checks(['Captured from the working build, with starter and sample content', 'Entries marked "Sample" and the contact details shown are placeholders', 'The Foundation\'s own name, logo, colours and photography are applied in the design phase', 'After launch: SMS and USSD fallback, one-time-code sign-in, clan groups and live mobile-money payments'])}
  <div class="contact"><div class="name">${esc(CONTACT.name)}</div><div class="ttl">${esc(CONTACT.title)}</div>
    <div class="k">Phone</div><div class="v">${esc(CONTACT.phone)}</div><div class="k">Email</div><div class="v">${esc(CONTACT.email)}</div>
    <div class="k">Office</div><div class="v">${esc(CONTACT.office)}</div><div class="k">Web</div><div class="v">${esc(CONTACT.web)}</div></div>`,
  { band: { photo: 'techcamp-graduation.jpg', quote: 'See it working, then decide.' }, bandH: 60 });

// ---------- cover and contents ----------
const cover = `<section class="pg cover">
  <img class="logo" src="img2/kakebe-logo.png" alt="Kakebe Technologies"><div class="tag">PREVIEW</div>
  <div class="title"><span class="l">Platform Preview</span><span class="b">Screenshots<br>and Mockups</span><div class="bar"></div>
    <div class="sub2">Companion to the proposal for the<br>${esc(CLIENT)}</div></div>
  <div class="panel"></div>${hero()}
  <div class="band2"><div class="big"><small>Screens from the</small><strong>Working Build</strong></div>
    <div class="meta"><div><b>Prepared for</b>${esc(CLIENT)}</div><div><b>Prepared by</b>Kakebe Technologies Limited</div><div><b>Date</b>${DATE}</div><div><b>Companion to</b>Proposal, version 2.0</div></div></div>
</section>`;

const tocEntries = pages.map((p, i) => [`${p.title[0]} ${p.title[1]}`.replace(/^The Platform at a Glance$/, 'The Platform at a Glance'), i + 3]);
const contents = `<section class="pg">
  <div class="body"><h1 class="ph"><span>Table of</span> <span class="b">Contents</span></h1><div class="ph-bar"></div>
  <div class="twocol"><ul class="toc">${tocEntries.map(([t, p], i) => `<li><span class="n">${String(i + 1).padStart(2, '0')}</span>${esc(t.replace(':', ':'))}<i></i><b>${p}</b></li>`).join('')}</ul>
  <div><div class="glance"><h4>About these screens</h4>
    <div class="row"><span>Source</span><div>The working build, 28 October 2026</div></div>
    <div class="row"><span>Content</span><div>Starter and sample entries</div></div>
    <div class="row"><span>Shown on</span><div>Laptop and phone</div></div>
    <div class="row"><span>Companion to</span><div>Proposal, version 2.0</div></div>
    <div class="row"><span>Investment</span><div>In the separate Financial Proposal</div></div></div>
  <div class="glance" style="margin-top:5mm"><h4>Contact</h4><div style="font-size:10pt"><b>${esc(CONTACT.name)}</b><br>Director<br>${esc(CONTACT.phone)}<br>${esc(CONTACT.email)}</div></div></div></div></div>
  ${foot(2)}</section>`;

const body = pages.map((p, i) => {
  const n = i + 3;
  const hasBand = !!p.band;
  const style = hasBand ? ` style="--band:${p.bandH || 60}mm"` : '';
  return `<section class="pg${hasBand ? ' hasband' : ''}"${style}>
  <div class="body"><h1 class="ph"><span>${esc(p.title[0])}</span> <span class="b">${esc(p.title[1])}</span></h1><div class="ph-bar"></div>
  ${p.html}</div>
  ${hasBand ? bandHtml(p.band) : ''}${foot(n)}</section>`;
}).join('\n');

const css = fs.readFileSync(path.join(dir, 'style2.css'), 'utf8') + '\n' + fs.readFileSync(path.join(dir, 'preview.css'), 'utf8');
const html = `<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Platform Preview: Screenshots and Mockups | Kakebe Technologies</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>${css}</style></head><body>
${cover}
${contents}
${body}
</body></html>`;
fs.writeFileSync(path.join(dir, 'preview.html'), html);
console.log('preview.html', pages.length + 2, 'pages,', (html.length / 1024).toFixed(0), 'KB');
