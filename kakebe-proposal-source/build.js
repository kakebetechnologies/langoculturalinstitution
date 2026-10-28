// Builds proposal.html from content/*.md, figures/*.svg and img/*.jpg. Usage: node build.js [toc.json]
// toc.json (optional) maps heading text to its PDF page number; render.js writes it after the first pass.
const fs = require('fs');
const path = require('path');
const { marked } = require('marked');

const dir = __dirname;
const CLIENT = 'Lango Cultural Foundation';
const DOC_TITLE = `Digital Platform for the ${CLIENT}`;
const DATE = '26 October 2026';
const VERSION = '1.0 (draft for discussion)';

const tocPages = fs.existsSync(process.argv[2] || '') ? JSON.parse(fs.readFileSync(process.argv[2], 'utf8')) : {};
const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const strip = (s) => s.replace(/<[^>]+>/g, '').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#39;/g, "'");
const slug = (s) => strip(s).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');

/* ---------- markdown renderer ---------- */
const renderer = new marked.Renderer();
renderer.image = (href, title, text) => {
  const m = /^(fig|browser|phone)\|(.*)$/.exec(text);
  if (!m) return `<img src="${href}" alt="${esc(text)}">`;
  const [, kind, cap] = m;
  if (kind === 'fig') {
    const svg = fs.readFileSync(path.join(dir, href), 'utf8').replace(/<svg /, '<svg class="svgfig" ');
    return `<figure class="fig">${svg}<figcaption>${cap}</figcaption></figure>`;
  }
  const img = path.basename(href);
  if (kind === 'browser') {
    return `<figure class="shot"><div class="bar"><i></i><i></i><i></i></div><img src="img/${img}" alt="${esc(cap)}"><figcaption>${cap}</figcaption></figure>`;
  }
  return `<figure class="phone"><div class="dev"><img src="img/${img}" alt="${esc(cap)}"></div><figcaption>${cap}</figcaption></figure>`;
};
renderer.paragraph = (text) => {
  if (/^(\s*<figure class="phone">[\s\S]*?<\/figure>\s*)+$/.test(text)) return `<div class="phones">${text}</div>`;
  if ((text.match(/<figure class="shot">/g) || []).length >= 2) return `<div class="shots2">${text}</div>
`;
  if (/^\s*<figure class="(shot|fig)">[\s\S]*<\/figure>\s*$/.test(text) && (text.match(/<figure/g) || []).length === 1) return text;
  return `<p>${text}</p>\n`;
};
renderer.blockquote = (quote) => `<blockquote class="callout">${quote}</blockquote>\n`;
renderer.table = (header, body) => {
  const cols = (header.match(/<th/g) || []).length;
  const cls = cols >= 6 ? 'dense' : cols === 5 ? 'mid' : '';
  const rows = (body.match(/<tr/g) || []).length;
  return `<div class="tw${rows <= 9 ? ' keep' : ''}"><table class="${cls}"><thead>${header}</thead><tbody>${body}</tbody></table></div>\n`;
};
renderer.heading = (text, level) => {
  const id = slug(text);
  if (level === 2) return `<h2 id="${id}" class="sec">${text}</h2>
`;
  if (level === 3) return `<h3 id="${id}" class="sub">${text}</h3>
`;
  return `<h${level}>${text}</h${level}>
`;
};
marked.setOptions({ gfm: true, breaks: false, renderer });

const tbc = (html) => html.replace(/\[(TBC[^\]]*)\]/g, '<span class="tbc">[$1]</span>');

/* ---------- chapters ---------- */
const files = fs.readdirSync(path.join(dir, 'content')).filter((f) => f.endsWith('.md')).sort();
const chapters = [];
for (const f of files) {
  let src = fs.readFileSync(path.join(dir, 'content', f), 'utf8').replace(/\{\{CLIENT\}\}/g, CLIENT).replace(/\r/g, '');
  // a file may hold several chapters (the appendices): split at every top-level heading
  const parts = src.split(/^(?=# )/m).filter((p) => p.trim());
  for (const part of parts) {
    const lines = part.split('\n');
    const h1 = lines.shift().replace(/^#\s+/, '').trim();
    let body = lines.join('\n').trim();
    let lead = '';
    const lm = /^\*([^*\n][^\n]*?)\*\s*\n/.exec(body + '\n');
    if (lm) { lead = lm[1]; body = body.slice(lm[0].length).trim(); }
    const nm = /^(\d+)\.\s+(.*)$/.exec(h1);
    const am = /^Appendix\s+([A-Z])\.\s+(.*)$/.exec(h1);
    const label = nm ? String(nm[1]).padStart(2, '0') : am ? 'Appendix ' + am[1] : '';
    const title = nm ? nm[2] : am ? am[2] : h1;
    const full = nm ? `${nm[1]}. ${nm[2]}` : am ? `Appendix ${am[1]}. ${am[2]}` : h1;
    chapters.push({ id: 'ch-' + slug(full), label, title, full, lead, body });
  }
}

for (const c of chapters) {
  c.html = tbc(marked.parse(c.body));
  c.sections = [...c.html.matchAll(/<h2 id="([^"]*)" class="sec">([\s\S]*?)<\/h2>/g)].map((m) => ({ id: m[1], text: strip(m[2]) }));
}

/* ---------- pages ---------- */
const logoW = 'img/kakebe-logo-white.png', logoD = 'img/kakebe-logo.png';

const cover = `<section class="cover">
  <div class="cv-top">
    <img class="cv-logo" src="${logoW}" alt="Kakebe Technologies">
    <p class="cv-eyebrow">Proposal</p>
    <h1>Digital Platform for the<br>${CLIENT}</h1>
    <p class="cv-sub">Website · Admin Control Panel · Lango Mobile App</p>
    <div class="cv-rule"></div>
    <dl class="cv-meta">
      <div><dt>Prepared for</dt><dd>${CLIENT}</dd></div>
      <div><dt>Prepared by</dt><dd>Kakebe Technologies Limited<br>Lira City, Uganda</dd></div>
      <div><dt>Date</dt><dd>${DATE}</dd></div>
      <div><dt>Version</dt><dd>${VERSION}</dd></div>
    </dl>
  </div>
  <div class="cv-art">
    <figure class="cv-browser"><div class="bar"><i></i><i></i><i></i></div><div class="vp"><img src="img/site-home.jpg"></div></figure>
    <figure class="cv-phone cv-p1"><div class="dev"><img src="img/app-home.jpg"></div></figure>
    <figure class="cv-phone cv-p2"><div class="dev"><img src="img/app-dictionary.jpg"></div></figure>
  </div>
  <p class="cv-conf">Confidential. Prepared for the ${CLIENT}.</p>
</section>`;

const control = `<section class="chapter front" id="ch-control" style="page:front">
  <div class="opener"><p class="label">Document control</p><h1>About this document</h1></div>
  <table class="kv"><tbody>
    <tr><th>Document</th><td>Proposal: ${DOC_TITLE}</td></tr>
    <tr><th>Prepared for</th><td>${CLIENT}</td></tr>
    <tr><th>Prepared by</th><td>Kakebe Technologies Limited, Rm B1, Plot 43, Renzo City House, Obote Avenue, Lira City, Uganda</td></tr>
    <tr><th>Date</th><td>${DATE}</td></tr>
    <tr><th>Version</th><td>${VERSION}</td></tr>
    <tr><th>Status</th><td>Draft for discussion, subject to the discovery workshop and a final quotation</td></tr>
    <tr><th>Validity</th><td><span class="tbc">[TBC: validity period, for example 30 days]</span></td></tr>
    <tr><th>Proposal contact</th><td><span class="tbc">[TBC: name, title, phone and email]</span></td></tr>
  </tbody></table>
  <h2 class="sec plain">How to read this proposal</h2>
  <ul>
    <li><b>Decision makers</b> can read Chapter 1 (Executive Summary), Chapter 3 (Our Recommendation) and Chapter 13 (Investment).</li>
    <li><b>Programme and content teams</b> will find the detail in Chapters 4 to 7 and the full feature catalogue in Appendix A.</li>
    <li><b>Technical and legal reviewers</b> will find architecture, security and data protection in Chapter 8, and assumptions and terms in Chapter 17.</li>
    <li><b>Evidence of delivery</b> is in Chapters 9 and 10, and the working demonstrator is shown in Appendix B.</li>
  </ul>
  <h2 class="sec plain">Conventions</h2>
  <ul>
    <li>Text like <span class="tbc">[TBC: item]</span> marks something Kakebe or the Foundation must confirm or complete before the proposal is issued as final.</li>
    <li>In Appendix A, <b>●</b> means already working in the demonstrator, <b>◐</b> means built in this engagement, and <b>○</b> means optional or roadmap.</li>
    <li>Amounts are in Ugandan shillings (UGX) and exclude taxes unless stated. Timelines are indicative until confirmed at the end of Phase 0.</li>
  </ul>
  <h2 class="sec plain">Document history</h2>
  <table><thead><tr><th>Version</th><th>Date</th><th>Author</th><th>Change</th></tr></thead><tbody>
    <tr><td>1.0</td><td>${DATE}</td><td>Kakebe Technologies</td><td>First draft for discussion</td></tr>
  </tbody></table>
  <h2 class="sec plain">Confidentiality</h2>
  <p>This document contains commercial and technical information prepared for the ${CLIENT} and is confidential. It may not be copied or shared outside the Foundation without the written permission of Kakebe Technologies Limited. Names of organisations and products mentioned belong to their respective owners and are used to describe work delivered by Kakebe.</p>
</section>`;

const pg = (key) => (tocPages[key] !== undefined ? tocPages[key] : '');
const tocRows = chapters.map((c) => {
  const secs = c.sections.map((s) => `<li class="t2"><a href="#${s.id}"><span>${esc(s.text)}</span><i></i><b>${pg(s.text)}</b></a></li>`).join('');
  const num = c.label.startsWith('Appendix') ? c.label : String(parseInt(c.label, 10));
  return `<li class="t1"><a href="#${c.id}"><span><em>${esc(num)}</em> ${esc(c.title)}</span><i></i><b>${pg(c.full)}</b></a></li>${secs}`;
}).join('');
const toc = `<section class="chapter front" id="ch-toc" style="page:front">
  <div class="opener"><p class="label">Contents</p><h1>Contents</h1></div>
  <ul class="toc">${tocRows}</ul>
</section>`;

const chapterHtml = chapters.map((c, i) => `<section class="chapter c${i}" id="${c.id}" style="page:ch${i}">
  <div class="opener"><p class="label">${c.label.startsWith('Appendix') ? c.label : 'Chapter ' + parseInt(c.label, 10)}</p>
  <h1>${esc(c.title)}</h1>${c.lead ? `<p class="lead">${c.lead}</p>` : ''}</div>
  ${c.html}
</section>`).join('\n');

const back = `<section class="back">
  <img class="bk-logo" src="${logoW}" alt="Kakebe Technologies">
  <h2>Let's build it in Lango, for Lango.</h2>
  <div class="bk-contact">
    <p><b>Kakebe Technologies Limited</b><br>Rm B1, Plot 43, Renzo City House<br>Obote Avenue, Lira City, Uganda</p>
    <p>+256 777 676 206<br>+256 393 249 845</p>
    <p>service@kakebe.tech<br>www.kakebe.tech</p>
  </div>
  <p class="bk-note">A youth-led tech hub building innovators across Northern Uganda.</p>
</section>`;

/* ---------- page rules (one named page per chapter so the running header carries the chapter title) ---------- */
const q = (s) => s.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
const pageRules = chapters.map((c, i) => `@page ch${i} { @top-right { content: "${q(c.label.startsWith('Appendix') ? c.full : parseInt(c.label, 10) + '. ' + c.title)}"; } }`).join('\n');

const css = fs.readFileSync(path.join(dir, 'proposal.css'), 'utf8').replace(/\{\{CLIENT\}\}/g, CLIENT);
const html = `<!doctype html><html lang="en"><head><meta charset="utf-8"><title>${esc(DOC_TITLE)}: Proposal by Kakebe Technologies</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<style>${css}
${pageRules}
@page front { @top-right { content: "Document control and contents"; } }
</style></head><body>
${cover}
${control}
${toc}
${chapterHtml}
${back}
</body></html>`;
fs.writeFileSync(path.join(dir, 'proposal.html'), html);
fs.writeFileSync(path.join(dir, 'toc-keys.json'), JSON.stringify(chapters.flatMap((c) => [c.full, ...c.sections.map((s) => s.text)]), null, 1));
console.log('proposal.html:', chapters.length, 'chapters,', (html.length / 1024).toFixed(0), 'KB');
