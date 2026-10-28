// Generates the proposal figures as standalone SVG files in ./figures (they are inlined into the PDF so the page fonts apply).
const fs = require('fs');
const path = require('path');

const INK = '#111111', RED = '#E01E26', BLUE = '#1F5FA8', GREY = '#5c5c5c', LINE = '#cfcfcf', SOFT = '#f6f6f6', REDT = '#fdecee', BLUET = '#e8f0fa';
const FONT = "font-family=\"Inter, 'Segoe UI', sans-serif\"";

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;');
const wrap = (w, h, body, label) => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${w} ${h}" role="img" aria-label="${esc(label)}" ${FONT}>
<defs>
<marker id="ar" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0 0L10 5L0 10z" fill="${GREY}"/></marker>
<marker id="arr" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0 0L10 5L0 10z" fill="${RED}"/></marker>
</defs>
${body}
</svg>`;

const text = (x, y, s, { size = 11.5, weight = 400, fill = INK, anchor = 'start', italic = false } = {}) =>
  `<text x="${x}" y="${y}" font-size="${size}" font-weight="${weight}" fill="${fill}" text-anchor="${anchor}"${italic ? ' font-style="italic"' : ''}>${esc(s)}</text>`;
const rect = (x, y, w, h, { fill = '#fff', stroke = LINE, sw = 1.2, rx = 8, dash = '' } = {}) =>
  `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${rx}" fill="${fill}" stroke="${stroke}" stroke-width="${sw}"${dash ? ` stroke-dasharray="${dash}"` : ''}/>`;
const line = (d, { stroke = GREY, sw = 1.4, marker = 'ar', dash = '' } = {}) =>
  `<path d="${d}" fill="none" stroke="${stroke}" stroke-width="${sw}"${marker ? ` marker-end="url(#${marker})"` : ''}${dash ? ` stroke-dasharray="${dash}"` : ''}/>`;

// A card with a coloured header strip, up to three body lines and an optional grey note.
function card(x, y, w, h, { head, color, lines = [], note = '', tint = false }) {
  let s = rect(x, y, w, h, { stroke: color, sw: 1.4 });
  s += `<path d="M${x} ${y + 8}a8 8 0 0 1 8 -8h${w - 16}a8 8 0 0 1 8 8v26h-${w}z" fill="${color}"/>`;
  s += text(x + w / 2, y + 22, head, { size: 13, weight: 700, fill: '#fff', anchor: 'middle' });
  lines.forEach((l, i) => { s += text(x + 14, y + 54 + i * 18, l, { size: 11.5 }); });
  if (note) s += text(x + 14, y + h - 12, note, { size: 10.5, fill: GREY, italic: true });
  return s;
}
function plain(x, y, w, h, { head, lines = [], fill = '#fff', stroke = LINE, headFill = INK }) {
  let s = rect(x, y, w, h, { fill, stroke });
  s += text(x + w / 2, y + 22, head, { size: 12.5, weight: 700, fill: headFill, anchor: 'middle' });
  lines.forEach((l, i) => { s += text(x + w / 2, y + 42 + i * 16, l, { size: 11, fill: GREY, anchor: 'middle' }); });
  return s;
}

const figs = {};

/* ---------- Figure 3.1: one platform, three surfaces ---------- */
{
  let b = '';
  const xs = [10, 265, 520], w = 230, cols = [RED, INK, BLUE];
  b += card(xs[0], 10, w, 132, { head: 'Public website', color: RED, lines: ['History, districts and clans', 'Language Hub and translator', 'News, events, giving, forms'], note: 'For everyone, on any device' });
  b += card(xs[1], 10, w, 132, { head: 'Admin control panel', color: INK, lines: ['Publish, review and translate', 'Six roles, audit log, backups', 'Moderation queue and reports'], note: 'For Foundation staff' });
  b += card(xs[2], 10, w, 132, { head: 'Lango Mobile App', color: BLUE, lines: ['Offline dictionary, translator', 'Record with GPS, photo, voice', 'Push alerts, points, badges'], note: 'Android, iOS and web app' });
  xs.forEach((x, i) => { b += line(`M${x + w / 2} 142V190`, { stroke: cols[i], marker: i === 0 ? 'arr' : 'ar' }); });
  b += rect(10, 190, 740, 58, { fill: SOFT, stroke: INK, sw: 1.6 });
  b += text(380, 215, 'One JSON API', { size: 15, weight: 700, anchor: 'middle' });
  b += text(380, 235, 'Accounts · roles · secure tokens · rate limits · English and Leb Lango', { size: 11.5, fill: GREY, anchor: 'middle' });
  xs.forEach((x) => { b += line(`M${x + w / 2} 248V292`); });
  b += plain(xs[0], 292, w, 92, { head: 'Content and language', lines: ['Clans, districts, history', 'Dictionary, proverbs, lessons', 'Members, points, badges'] });
  b += plain(xs[1], 292, w, 92, { head: 'Media and documents', lines: ['Photos, audio and video', 'Scans and documents', 'Backed up off the server'] });
  b += plain(xs[2], 292, w, 92, { head: 'One review queue', lines: ['Web and app contributions', 'Notes, decisions, archive', 'Alerts back to contributors'], fill: REDT, stroke: RED, headFill: RED });
  b += rect(10, 408, 740, 38, { fill: '#fff', stroke: LINE, dash: '5 4' });
  b += text(380, 432, 'Connected services: email · SMS · push notifications · maps · mobile money and cards (optional) · analytics', { size: 11, fill: GREY, anchor: 'middle' });
  b += line('M380 384V408', { dash: '3 3' });
  figs['fig-platform'] = wrap(760, 456, b, 'One platform with three surfaces sharing one API, one database and one review queue');
}

/* ---------- Figure 8.1: architecture layers ---------- */
{
  let b = '';
  const band = (y, h, label, color) => rect(10, y, 108, h, { fill: color, stroke: color, rx: 8 }) + text(64, y + h / 2 + 4, label, { size: 12.5, weight: 700, fill: '#fff', anchor: 'middle' });
  const box = (x, y, w, h, t1, t2, o = {}) => rect(x, y, w, h, { fill: o.fill || '#fff', stroke: o.stroke || LINE }) + text(x + w / 2, y + h / 2 - 2, t1, { size: 12, weight: 700, anchor: 'middle' }) + text(x + w / 2, y + h / 2 + 14, t2, { size: 10.5, fill: GREY, anchor: 'middle' });
  // clients
  b += band(10, 82, 'Clients', RED);
  [['Web browsers', 'desktop and mobile'], ['Lango App', 'Android and iOS'], ['Web app (PWA)', 'installed from a link'], ['Open data users', 'schools, researchers']]
    .forEach((c, i) => { b += box(130 + i * 158, 10, 146, 82, c[0], c[1]); });
  [229, 440, 651].forEach((x) => { b += line(`M${x} 92V124`); }); b += text(449, 112, 'HTTPS', { size: 10.5, fill: GREY });
  // application
  b += band(124, 82, 'Application', INK);
  [['Public website', 'PHP 8.2 · no build step'], ['Admin control panel', 'six roles · audit log'], ['JSON API v1', 'tokens · rate limits']]
    .forEach((c, i) => { b += box(130 + i * 211, 124, 198, 82, c[0], c[1], i === 2 ? { fill: REDT, stroke: RED } : {}); });
  [229, 440, 651].forEach((x) => { b += line(`M${x} 206V238`); });
  // data
  b += band(238, 82, 'Data', BLUE);
  [['MySQL / MariaDB', 'content, members, review queue'], ['File storage', 'photos, audio, documents'], ['Backups', 'daily, kept off the server']]
    .forEach((c, i) => { b += box(130 + i * 211, 238, 198, 82, c[0], c[1]); });
  [229, 440, 651].forEach((x) => { b += line(`M${x} 320V352`, { dash: '3 3' }); });
  // services
  b += band(352, 82, 'Services', GREY);
  [['Email', 'SMTP'], ['SMS', 'local gateway'], ['Push', 'FCM · Apple'], ['Maps', 'districts, sites'], ['Payments', 'optional upgrade']]
    .forEach((c, i) => { b += box(130 + i * 126, 352, 116, 82, c[0], c[1], i === 4 ? { stroke: LINE } : {}); });
  figs['fig-architecture'] = wrap(760, 444, b, 'Platform architecture in four layers: clients, application, data and services');
}

/* ---------- Figure 6.1: offline-first behaviour ---------- */
{
  let b = '';
  const step = (x, y, w, h, n, t1, t2, o = {}) => rect(x, y, w, h, { fill: o.fill || '#fff', stroke: o.stroke || LINE, sw: o.sw || 1.3 }) +
    (n ? `<circle cx="${x + 16}" cy="${y + 16}" r="10" fill="${o.num || INK}"/>` + text(x + 16, y + 20, String(n), { size: 11, weight: 700, fill: '#fff', anchor: 'middle' }) : '') +
    text(x + (n ? 34 : 14), y + 21, t1, { size: 12, weight: 700 }) + (t2 ? text(x + 14, y + 42, t2, { size: 11, fill: GREY }) : '');
  b += text(10, 18, 'ON THE PHONE', { size: 10.5, weight: 700, fill: BLUE });
  b += step(10, 30, 168, 62, 1, 'Record', 'story, photo, GPS, voice', { stroke: BLUE, num: BLUE });
  b += step(206, 30, 168, 62, 2, 'Save on the phone', 'with a unique reference', { stroke: BLUE, num: BLUE });
  b += line('M178 61H204'); b += line('M374 61H402');
  b += `<polygon points="470,24 538,61 470,98 402,61" fill="#fff" stroke="${INK}" stroke-width="1.4"/>`;
  b += text(470, 58, 'Connection', { size: 11.5, weight: 700, anchor: 'middle' }) + text(470, 73, 'available?', { size: 11.5, weight: 700, anchor: 'middle' });
  b += line('M538 61H570', { stroke: RED, marker: 'arr' }); b += text(554, 52, 'yes', { size: 10.5, fill: RED, weight: 700, anchor: 'middle' });
  b += step(572, 30, 178, 62, 3, 'Send to the server', 'automatically', { stroke: RED, num: RED });
  b += line('M446 88V148'); b += text(436, 124, 'no', { size: 10.5, fill: GREY, weight: 700, anchor: 'end' });
  b += line('M494 148V88', { stroke: BLUE }); b += text(504, 124, 'retry when online', { size: 10.5, fill: BLUE, weight: 700 });
  b += step(372, 150, 196, 62, '', 'Wait in the outbox', 'banner shown, nothing is lost', { fill: SOFT });
  b += text(10, 246, 'ON THE SERVER', { size: 10.5, weight: 700, fill: RED });
  b += line('M660 92V256');
  b += step(572, 258, 178, 62, 4, 'Store it once', 'duplicates are ignored', { stroke: RED, num: RED });
  b += step(358, 258, 178, 62, 5, 'Review queue', 'moderator decides', { stroke: RED, num: RED });
  b += step(144, 258, 178, 62, 6, 'Notify the phone', 'in-app and push', { stroke: RED, num: RED });
  b += line('M572 289H538'); b += line('M358 289H324');
  figs['fig-offline'] = wrap(760, 336, b, 'Offline-first behaviour: record, save on the phone, send when connected, stored once, reviewed, then notified');
}

/* ---------- Figure 7.1: contribution lifecycle ---------- */
{
  let b = '<g transform="translate(0,18)">';
  const bx = (x, y, w, h, n, t1, t2, o = {}) => rect(x, y, w, h, { fill: o.fill || '#fff', stroke: o.stroke || LINE, sw: 1.3 }) +
    `<circle cx="${x + 15}" cy="${y + 15}" r="9.5" fill="${o.num || INK}"/>` + text(x + 15, y + 19, n, { size: 10.5, weight: 700, fill: '#fff', anchor: 'middle' }) +
    text(x + 31, y + 20, t1, { size: 12, weight: 700 }) + (t2 ? text(x + 12, y + 40, t2, { size: 10.5, fill: GREY }) : '') + (o.t3 ? text(x + 12, y + 55, o.t3, { size: 10.5, fill: GREY }) : '');
  b += bx(10, 40, 150, 60, 1, 'Capture', 'web or app, even offline', { stroke: BLUE, num: BLUE });
  b += bx(186, 40, 150, 60, 2, 'Submit', 'tracking code issued', { stroke: BLUE, num: BLUE });
  b += bx(362, 40, 150, 60, 3, 'Review queue', 'moderator opens it');
  b += line('M160 70H184'); b += line('M336 70H360');
  b += `<polygon points="620,26 700,70 620,114 540,70" fill="#fff" stroke="${INK}" stroke-width="1.4"/>`;
  b += text(620, 66, 'Moderator', { size: 11.5, weight: 700, anchor: 'middle' }) + text(620, 81, 'decides', { size: 11.5, weight: 700, anchor: 'middle' });
  b += line('M512 70H538');
  // return loop
  b += line('M620 26V12H85V38', { stroke: GREY, dash: '4 3' });
  b += text(352, 8, 'Returned: the contributor sees the reviewer\'s note and can improve it', { size: 10.5, fill: GREY, anchor: 'middle' });
  // approve branch
  b += line('M620 114V176', { stroke: RED, marker: 'arr' }); b += text(630, 150, 'approve', { size: 10.5, fill: RED, weight: 700 });
  b += bx(516, 178, 210, 70, 4, 'Specialist check', 'clan leader or Cultural Review', { stroke: RED, num: RED, t3: 'Panel, where needed' });
  b += bx(276, 178, 200, 70, 5, 'Publish and archive', 'reviewer and source recorded', { stroke: RED, num: RED });
  b += bx(36, 178, 200, 70, 6, 'Notify and recognise', 'in-app, push, points, badges', { stroke: RED, num: RED });
  b += line('M516 211H478'); b += line('M276 211H238');
  // reject branch
  b += line('M700 70H744V300H700', { stroke: GREY });
  b += text(738, 60, 'reject', { size: 10.5, fill: GREY, weight: 700, anchor: 'end' });
  b += rect(516, 276, 184, 48, { fill: SOFT, stroke: LINE });
  b += text(608, 297, 'Closed with a reason', { size: 11.5, weight: 700, anchor: 'middle' });
  b += text(608, 313, 'visible to the contributor', { size: 10.5, fill: GREY, anchor: 'middle' });
  b += '</g>';
  figs['fig-workflow'] = wrap(760, 358, b, 'Contribution lifecycle from capture to publication and notification');
}

/* ---------- Figure 11.1: four-stage process ---------- */
{
  let b = '';
  const items = [
    ['01', 'Discover', INK, ['Workshops, content audit', 'Scope, roles, plan']],
    ['02', 'Design and build', RED, ['Two-week cycles', 'Staging site and demos']],
    ['03', 'Test and launch', BLUE, ['Testing, pilot, training', 'Store release, go-live']],
    ['04', 'Support and grow', GREY, ['90-day hypercare', 'Support plan, roadmap']],
  ];
  items.forEach((it, i) => {
    const x = 10 + i * 186, w = 190, h = 66;
    b += `<path d="M${x} 10h${w - 22}l22 ${h / 2}l-22 ${h / 2}h-${w - 22}${i === 0 ? '' : `l22 -${h / 2}`}z" fill="${it[2]}"/>`;
    b += text(x + (i === 0 ? 20 : 40), 36, it[0], { size: 11, weight: 700, fill: 'rgba(255,255,255,.75)' });
    b += text(x + (i === 0 ? 20 : 40), 58, it[1], { size: 14, weight: 700, fill: '#fff' });
    it[3].forEach((l, j) => { b += text(x + 12, 104 + j * 18, '• ' + l, { size: 11.5, fill: INK }); });
  });
  figs['fig-process'] = wrap(760, 150, b, 'Kakebe four-stage process: discover, design and build, test and launch, support and grow');
}

/* ---------- Figure 11.2: Gantt ---------- */
{
  const rows = [
    ['P0', 'Discover and align', 1, 2, RED],
    ['P1', 'Brand, design, foundation', 3, 8, RED],
    ['P2', 'Culture and language', 5, 12, RED],
    ['P3', 'Community engine and API', 9, 13, RED],
    ['P4', 'Mobile app', 9, 16, BLUE],
    ['P5', 'Content, testing, pilot', 15, 18, RED],
    ['P6', 'Launch', 19, 20, INK],
    ['P7', 'Hypercare (90 days)', 21, 33, GREY],
  ];
  const X0 = 214, X1 = 748, weeks = 33, wk = (X1 - X0) / weeks, rowH = 34, Y0 = 58;
  const xw = (w) => X0 + (w - 1) * wk; // left edge of week w
  let b = '';
  b += rect(10, 10, 740, 36 + rows.length * rowH + 76, { fill: '#fff', stroke: LINE });
  for (let w = 1; w <= weeks; w++) {
    if (w % 2 === 1 || w === weeks) {
      b += text(xw(w) + wk / 2, 34, String(w), { size: 10.5, fill: GREY, anchor: 'middle' });
    }
    b += `<line x1="${xw(w)}" x2="${xw(w)}" y1="42" y2="${Y0 + rows.length * rowH}" stroke="#ececec"/>`;
  }
  b += text(24, 34, 'Week', { size: 10.5, weight: 700, fill: GREY });
  rows.forEach((r, i) => {
    const y = Y0 + i * rowH;
    if (i % 2 === 0) b += `<rect x="12" y="${y}" width="736" height="${rowH}" fill="#fafafa"/>`;
    b += text(24, y + 21, r[0], { size: 11, weight: 700, fill: r[4] });
    b += text(50, y + 21, r[1], { size: 11.5 });
    const x = xw(r[2]), w = (r[3] - r[2] + 1) * wk;
    b += `<rect x="${x}" y="${y + 8}" width="${w}" height="18" rx="9" fill="${r[4]}" ${r[0] === 'P7' ? 'fill-opacity=".35"' : ''}/>`;
  });
  const gl = Y0 + rows.length * rowH;
  // milestones
  const ms = [[8, 1, 'Design approved'], [18, 5, 'Acceptance'], [20, 6, 'Go-live']];
  ms.forEach(([w, i, label]) => {
    const cx = xw(w + 1), cy = Y0 + i * rowH + 17;
    b += `<path d="M${cx} ${cy - 9}l9 9l-9 9l-9 -9z" fill="#fff" stroke="${INK}" stroke-width="1.6"/>`;
  });
  b += `<line x1="${xw(21)}" x2="${xw(21)}" y1="42" y2="${gl}" stroke="${RED}" stroke-width="1.4" stroke-dasharray="4 3"/>`;
  b += text(xw(21) - 6, gl + 18, 'Go-live at the end of week 20', { size: 11, weight: 700, fill: RED, anchor: 'end' });
  b += `<path d="M24 ${gl + 36}l7 -7l7 7l-7 7z" fill="#fff" stroke="${INK}" stroke-width="1.6"/>`;
  b += text(46, gl + 40, 'Milestones: design approval (week 8), user acceptance (week 18), go-live (week 20)', { size: 11, fill: GREY });
  figs['fig-timeline'] = wrap(760, 36 + rows.length * rowH + 76 + 20, b, 'Indicative timeline: about 20 weeks to launch followed by 90 days of hypercare');
}


/* ---------- Figure 11.1 (17-page edition): three-month plan ---------- */
{
  const rows = [
    ['P0', 'Plan and align', 1, 2, RED],
    ['P1', 'Design and model', 2, 5, RED],
    ['P2', 'Website, admin, culture, language', 4, 9, RED],
    ['P3', 'Community engine and API', 5, 9, RED],
    ['P4', 'Mobile app', 4, 10, BLUE],
    ['P5', 'Content, testing, pilot, training', 9, 12, RED],
    ['P6', 'Launch', 12, 13, INK],
    ['P7', 'Hypercare, 90 days (included)', 14, 26, GREY],
  ];
  const X0 = 252, X1 = 748, weeks = 26, wk = (X1 - X0) / weeks, rowH = 25, Y0 = 50;
  const xw = (w) => X0 + (w - 1) * wk;
  let b = '';
  const H = 36 + rows.length * rowH + 58;
  b += rect(10, 6, 740, H, { fill: '#fff', stroke: LINE });
  for (let w = 1; w <= weeks; w++) {
    if (w % 2 === 1) b += text(xw(w) + wk / 2, 28, String(w), { size: 10.5, fill: GREY, anchor: 'middle' });
    b += `<line x1="${xw(w)}" x2="${xw(w)}" y1="36" y2="${Y0 + rows.length * rowH}" stroke="#ececec"/>`;
  }
  b += text(24, 28, 'Week', { size: 10.5, weight: 700, fill: GREY });
  rows.forEach((r, i) => {
    const y = Y0 + i * rowH;
    if (i % 2 === 0) b += `<rect x="12" y="${y}" width="736" height="${rowH}" fill="#fafafa"/>`;
    b += text(24, y + 17, r[0], { size: 11, weight: 700, fill: r[4] });
    b += text(50, y + 17, r[1], { size: 11.5 });
    const x = xw(r[2]), w = (r[3] - r[2] + 1) * wk;
    b += `<rect x="${x}" y="${y + 6}" width="${w}" height="14" rx="7" fill="${r[4]}" ${r[0] === 'P7' ? 'fill-opacity=".35"' : ''}/>`;
  });
  const gl = Y0 + rows.length * rowH;
  [[2, 0], [5, 1], [10, 4], [12, 5], [13, 6]].forEach(([w, i]) => {
    const cx = xw(w + 1), cy = Y0 + i * rowH + 13;
    b += `<path d="M${cx} ${cy - 8}l8 8l-8 8l-8 -8z" fill="#fff" stroke="${INK}" stroke-width="1.6"/>`;
  });
  b += `<line x1="${xw(14)}" x2="${xw(14)}" y1="36" y2="${gl}" stroke="${RED}" stroke-width="1.4" stroke-dasharray="4 3"/>`;
  b += text(xw(14) - 6, gl + 16, 'Public launch at the end of week 13 (three months)', { size: 11, weight: 700, fill: RED, anchor: 'end' });
  b += `<path d="M24 ${gl + 32}l6 -6l6 6l-6 6z" fill="#fff" stroke="${INK}" stroke-width="1.5"/>`;
  b += text(44, gl + 36, 'Milestones: scope signed (week 2), design approved (5), feature complete (10), user acceptance (12), go-live (13)', { size: 10.5, fill: GREY });
  figs['fig-timeline3'] = wrap(760, H + 14, b, 'Three-month plan: planning, design and modelling, build, content and testing, launch at week 13, then 90 days of hypercare');
}

fs.mkdirSync(path.join(__dirname, 'figures'), { recursive: true });
for (const [name, svg] of Object.entries(figs)) fs.writeFileSync(path.join(__dirname, 'figures', name + '.svg'), svg);
console.log('figures:', Object.keys(figs).join(', '));
