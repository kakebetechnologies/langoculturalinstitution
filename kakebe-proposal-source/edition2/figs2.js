// Edition 2 figures with larger type: fig-platform2.svg (platform diagram) and fig-road.svg (timeline). Run: node figs2.js
const fs = require('fs');
const path = require('path');

const NAVY = '#164574', BLUE = '#1F5FA8', RED = '#E01E26', INK = '#17283b', GREY = '#5c6b7a', LINE = '#c9d9ec', TINT = '#EAF1FA';
const FONT = "font-family=\"Inter, 'Segoe UI', sans-serif\"";
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;');
const T = (x, y, s, o = {}) => `<text x="${x}" y="${y}" font-size="${o.size || 14}" font-weight="${o.w || 400}" fill="${o.fill || INK}" text-anchor="${o.a || 'start'}"${o.font ? ` font-family="${o.font}"` : ''}>${esc(s)}</text>`;
const R = (x, y, w, h, o = {}) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${o.rx === undefined ? 10 : o.rx}" fill="${o.fill || '#fff'}" stroke="${o.stroke || LINE}" stroke-width="${o.sw || 1.4}"/>`;
const wrap = (w, h, body, label, extraDefs = '') => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${w} ${h}" role="img" aria-label="${esc(label)}" ${FONT}><defs><marker id="a" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto"><path d="M0 0L10 5L0 10z" fill="${GREY}"/></marker>${extraDefs}</defs>${body}</svg>`;
const arrow = (x, y1, y2) => `<path d="M${x} ${y1}V${y2}" stroke="${GREY}" stroke-width="2" fill="none" marker-end="url(#a)"/>`;

/* ---------- platform diagram ---------- */
{
  let b = '';
  const cards = [
    [6, RED, 'Public website', ['History and districts', 'Language Hub, news', 'For everyone']],
    [264, INK, 'Admin control panel', ['Publish and translate', 'Review and moderate', 'For Foundation staff']],
    [522, BLUE, 'Lango Mobile App', ['Offline dictionary', 'Voice, photo and GPS', 'Android and iOS']],
  ];
  for (const [x, col, name, lines] of cards) {
    b += R(x, 6, 232, 138, { stroke: col, sw: 2, rx: 12 });
    b += `<path d="M${x} 18a12 12 0 0 1 12 -12h208a12 12 0 0 1 12 12v26h-232z" fill="${col}"/>`;
    b += T(x + 116, 31, name, { size: 16.5, w: 700, fill: '#fff', a: 'middle' });
    lines.forEach((l, i) => { b += T(x + 18, 72 + i * 25, l, { size: 15 }); });
    b += arrow(x + 116, 144, 190);
  }
  b += R(6, 192, 748, 62, { fill: TINT, stroke: NAVY, sw: 2, rx: 12 });
  b += T(380, 220, 'One JSON API', { size: 20, w: 800, fill: NAVY, a: 'middle' });
  b += T(380, 243, 'Accounts · roles · secure tokens · English and Leb Lango', { size: 14.5, fill: GREY, a: 'middle' });
  const low = [[6, 'Content and language', ['Clans, history, words', 'Members and points']], [264, 'Media and documents', ['Photos, audio, video', 'Backed up off-site']], [522, 'One review queue', ['Web and app input', 'Alerts to contributors']]];
  for (const [x, name, lines] of low) {
    b += arrow(x + 116, 254, 292);
    b += R(x, 294, 232, 92, { stroke: x === 522 ? RED : LINE, fill: x === 522 ? '#fdecee' : '#fff', sw: 1.8, rx: 12 });
    b += T(x + 116, 322, name, { size: 16, w: 700, fill: x === 522 ? RED : NAVY, a: 'middle' });
    lines.forEach((l, i) => { b += T(x + 116, 349 + i * 23, l, { size: 14.5, fill: GREY, a: 'middle' }); });
  }
  b += `<rect x="6" y="400" width="748" height="38" rx="10" fill="#fff" stroke="${LINE}" stroke-width="1.4" stroke-dasharray="6 5"/>`;
  b += T(380, 425, 'Email · SMS · Push notifications · Maps · Mobile money (optional)', { size: 14.5, fill: GREY, a: 'middle' });
  fs.writeFileSync(path.join(__dirname, 'figures', 'fig-platform2.svg'), wrap(760, 446, b, 'One platform: website, admin panel and mobile app share one API, one database and one review queue'));
}

/* ---------- winding road timeline ---------- */
{
  const pins = [
    [95, 200, 'below', '01', 'WEEK 1', 'Plan and align'],
    [215, 90, 'above', '02', 'WEEK 3', 'Design approved'],
    [335, 200, 'below', '03', 'WEEK 6', 'Feature complete'],
    [455, 90, 'above', '04', 'WEEK 7', 'User acceptance'],
    [575, 200, 'below', '05', 'WEEK 8', 'Public launch'],
    [690, 118, 'above', '06', '+90 DAYS', 'Support included'],
  ];
  const d = 'M20 200 H150 V90 H270 V200 H390 V90 H510 V200 H630 V118 H745';
  let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 300" role="img" aria-label="Two-month plan: week 1 plan and align, week 3 design approved, week 6 feature complete, week 7 user acceptance, week 8 public launch, then 90 days of support" font-family="Montserrat, Inter, sans-serif">`;
  s += `<path d="${d}" fill="none" stroke="${NAVY}" stroke-width="32" stroke-linejoin="round" stroke-linecap="round"/>`;
  s += `<path d="${d}" fill="none" stroke="#fff" stroke-width="2.6" stroke-dasharray="9 8" stroke-linejoin="round"/>`;
  for (const [x, y, side, n, wk, title] of pins) {
    s += `<circle cx="${x}" cy="${y}" r="17" fill="${RED}" stroke="#fff" stroke-width="3"/>`;
    s += `<text x="${x}" y="${y + 5.5}" text-anchor="middle" font-size="14" font-weight="800" fill="#fff">${n}</text>`;
    const ty = side === 'below' ? y + 52 : y - 42;
    s += `<text x="${x}" y="${ty}" text-anchor="middle" font-size="15.5" font-weight="800" fill="${BLUE}">${wk}</text>`;
    s += `<text x="${x}" y="${ty + 20}" text-anchor="middle" font-size="16" font-weight="500" fill="${INK}">${title}</text>`;
  }
  s += '</svg>';
  fs.writeFileSync(path.join(__dirname, 'figures', 'fig-road.svg'), s);
}
console.log('fig-platform2.svg, fig-road.svg');
