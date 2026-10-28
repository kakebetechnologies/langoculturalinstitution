// Builds proposal2.html (edition 2: light design, one designed page per section) from content2.js. Usage: node build2.js
const fs = require('fs');
const path = require('path');
const { CLIENT, CONTACT, pages } = require('./content2.js');

const dir = __dirname;
const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const inline = (s) => esc(s).replace(/\*\*(.+?)\*\*/g, '<b>$1</b>').replace(/\*(.+?)\*/g, '<i>$1</i>');
const DATE = 'October 2026', VERSION = '2.0';
const COPY = 'Copyright @KakebeTechnologies · Proposal for the Lango Cultural Foundation';

function block(b) {
  switch (b.t) {
    case 'p': return `<p>${inline(b.x)}</p>`;
    case 'sub': return `<h3 class="sub">${inline(b.x)}</h3>`;
    case 'theme': return `<div class="theme">${inline(b.x)}</div>`;
    case 'checks': return `<ul class="checks${b.cols === 2 ? ' cols2' : ''}">${b.items.map((i) => `<li>${inline(i)}</li>`).join('')}</ul>`;
    case 'key': return `<div class="key"><div class="lab">${esc(b.label)}</div><div class="card"><ul class="checks">${b.items.map((i) => `<li>${inline(i)}</li>`).join('')}</ul></div></div>`;
    case 'chips': return `<div class="chips">${b.items.map((i) => `<span class="chip">${esc(i)}</span>`).join('')}</div>`;
    case 'tiles': return `<div class="tiles${b.small ? ' small' : ''}" style="grid-template-columns:repeat(${b.items.length},1fr)">${b.items.map(([k, v]) => `<div class="tile"><div class="k">${esc(k)}</div><div class="v">${esc(v)}</div></div>`).join('')}</div>`;
    case 'table': {
      const head = `<tr>${b.head.map((h) => `<th>${esc(h)}</th>`).join('')}</tr>`;
      const rows = b.rows.map((r) => `<tr>${r.map((c) => `<td>${inline(c).replace(/ ((?:www\.|[a-z0-9-]+\.)[a-z0-9.\/-]+\.[a-z]{2,}[a-z0-9.\/-]*)$/i, '<i>$1</i>')}</td>`).join('')}</tr>`).join('');
      return `<table class="${b.cls || ''}"><thead>${head}</thead><tbody>${rows}</tbody></table>`;
    }
    case 'fig': {
      const svg = fs.readFileSync(path.join(dir, b.f), 'utf8').replace(/<svg /, `<svg style="width:${b.w || 90}%" `);
      return `<div class="fig">${svg}<div class="cap">${esc(b.c)}</div></div>`;
    }
    case 'team': return `<div class="team">${b.people.map(([n, r, d, ph]) => `<div class="person"><img src="img2/${ph}" alt="${esc(n)}"><div class="n">${esc(n)}</div><div class="r">${esc(r)}</div></div>`).join('')}</div>`;
    case 'steps': return `<div class="steps" style="grid-template-columns:repeat(${b.cols || 5},1fr)">${b.items.map(([t, x], i) => `<div class="step"><div class="no">${i + 1}</div><div class="t">${esc(t)}</div><div class="x">${esc(x)}</div></div>`).join('')}</div>`;
    case 'contact': return `<div class="contact"><div class="name">${esc(CONTACT.name)}</div><div class="ttl">${esc(CONTACT.title)}</div>
      <div class="k">Phone</div><div class="v">${esc(CONTACT.phone)}</div><div class="k">Email</div><div class="v">${esc(CONTACT.email)}</div>
      <div class="k">Office</div><div class="v">${esc(CONTACT.office)}</div><div class="k">Web</div><div class="v">${esc(CONTACT.web)}</div></div>`;
    default: throw new Error('block ' + b.t);
  }
}

const foot = (n, band) => `<div class="foot"><div class="pill">Page ${n}</div><div class="copy">${COPY}</div></div>`;
const bandHtml = (b) => `<div class="band" style="background-image:url('img2/${b.photo}')"><div class="qm">&ldquo;</div><div class="q">${esc(b.quote)}</div></div>`;

const cover = `<section class="pg cover">
  <img class="logo" src="img2/kakebe-logo.png" alt="Kakebe Technologies"><div class="tag">PROPOSAL</div>
  <div class="title"><span class="l">Digital Platform</span><span class="b">for the Lango<br>Cultural Foundation</span><div class="bar"></div>
    <div class="sub2">Website · Admin Control Panel<br>Mobile App for Android and iOS</div></div>
  <div class="photo" style="background-image:url('img2/kakebe-building.jpg')"></div>
  <div class="inset" style="left:16mm;top:176mm;background-image:url('img2/group-liracity.jpg')"></div>
  <div class="inset" style="right:16mm;top:166mm;background-image:url('img2/team-desk.jpg')"></div>
  <div class="band2"><div class="big"><small>Delivered in</small><strong>2 months</strong></div>
    <div class="meta"><div><b>Prepared for</b>${esc(CLIENT)}</div><div><b>Prepared by</b>Kakebe Technologies Limited</div><div><b>Date</b>${DATE}</div><div><b>Financials</b>Separate Financial Proposal</div></div></div>
</section>`;

const tocEntries = [['Executive Summary', 3], ['Understanding Your Needs', 4], ['One Platform, Three Surfaces', 5], ['The Public Website', 6], ['Admin Control Panel', 7], ['Mobile App for Android and iOS', 8], ['Hosting, Security and Privacy', 9], ['Delivery in Two Months', 10], ['Our Team', 11], ['Training, Handover and Support', 12], ['About Kakebe', 13], ['Our Experience', 14], ['Experience, Continued', 15], ['Risks and Assumptions', 16], ['Next Steps and Contact', 17]];
const contents = `<section class="pg">
  <div class="body"><h1 class="ph"><span>Table of</span> <span class="b">Contents</span></h1><div class="ph-bar"></div>
  <div class="twocol"><ul class="toc">${tocEntries.map(([t, p], i) => `<li><span class="n">${String(i + 1).padStart(2, '0')}</span>${esc(t)}<i></i><b>${p}</b></li>`).join('')}</ul>
  <div><div class="glance"><h4>At a glance</h4>
    <div class="row"><span>Scope</span><div>Website, admin control panel, mobile app</div></div>
    <div class="row"><span>Platforms</span><div>Web, Android and iOS</div></div>
    <div class="row"><span>Delivery</span><div>2 months to complete and launch</div></div>
    <div class="row"><span>Hosting</span><div>Any platform of the Foundation's choice</div></div>
    <div class="row"><span>Support</span><div>90 days included</div></div>
    <div class="row"><span>Investment</span><div>In the separate Financial Proposal</div></div></div>
  <div class="glance" style="margin-top:5mm"><h4>Proposal contact</h4><div style="font-size:10pt"><b>${esc(CONTACT.name)}</b><br>Director<br>${esc(CONTACT.phone)}<br>${esc(CONTACT.email)}</div></div></div></div></div>
  ${foot(2)}</section>`;

const body = pages.map((p, i) => {
  const n = i + 3;
  const hasBand = !!p.band;
  const style = hasBand ? ` style="--band:${p.bandH || 60}mm"` : '';
  return `<section class="pg${hasBand ? ' hasband' : ''}${p.roomy ? ' roomy' : ''}"${style}>
  <div class="body"><h1 class="ph"><span>${esc(p.title[0])}</span> <span class="b">${esc(p.title[1])}</span></h1><div class="ph-bar"></div>
  ${p.blocks.map(block).join('\n')}</div>
  ${hasBand ? bandHtml(p.band) : ''}${foot(n)}</section>`;
}).join('\n');

const css = fs.readFileSync(path.join(dir, 'style2.css'), 'utf8');
const html = `<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Proposal: Digital Platform for the ${esc(CLIENT)} | Kakebe Technologies</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>${css}</style></head><body>
${cover}
${contents}
${body}
</body></html>`;
fs.writeFileSync(path.join(dir, 'proposal2.html'), html);
console.log('proposal2.html', pages.length + 2, 'pages,', (html.length / 1024).toFixed(0), 'KB');
