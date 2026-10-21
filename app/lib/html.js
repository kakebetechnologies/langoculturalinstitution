// Turns the website's stored article HTML (paragraphs, headings, lists, quotes, bold/italic, links, images) into simple blocks
// that components/RichText.jsx draws with native Text. Only the tags the website's editor produces are understood; anything
// else is shown as plain text.

const NAMED = { amp: '&', lt: '<', gt: '>', quot: '"', apos: "'", nbsp: ' ', ndash: '–', mdash: '—', hellip: '…', ldquo: '“', rdquo: '”', lsquo: '‘', rsquo: '’' };

const decode = (s) =>
  s.replace(/&(#x?[0-9a-f]+|[a-z]+);/gi, (m, e) => {
    if (e[0] === '#') {
      const code = e[1].toLowerCase() === 'x' ? parseInt(e.slice(2), 16) : parseInt(e.slice(1), 10);
      return Number.isFinite(code) ? String.fromCodePoint(code) : m;
    }
    return NAMED[e.toLowerCase()] ?? m;
  });

const BLOCK_OPEN = new Set(['p', 'h2', 'h3', 'h4', 'blockquote', 'li', 'figcaption']);

/** @returns {{type:'p'|'h2'|'h3'|'h4'|'quote'|'li'|'hr'|'img', runs?:{text:string,bold?:boolean,italic?:boolean,href?:string}[], ordered?:boolean, n?:number, src?:string, alt?:string}[]} */
export function htmlToBlocks(html) {
  const blocks = [];
  let cur = null;
  let bold = 0;
  let italic = 0;
  let href = null;
  const lists = [];

  const open = (type, extra = {}) => {
    cur = { type, runs: [], ...extra };
    blocks.push(cur);
  };

  for (const tok of String(html ?? '').split(/(<[^>]+>)/g)) {
    if (!tok) continue;
    if (tok[0] === '<') {
      const m = tok.match(/^<\s*(\/?)\s*([a-z0-9]+)([^>]*)>/i);
      if (!m) continue;
      const closing = m[1] === '/';
      const tag = m[2].toLowerCase();
      if (BLOCK_OPEN.has(tag)) {
        if (!closing) {
          const list = lists[lists.length - 1];
          if (tag === 'li') {
            if (list) list.n += 1;
            open('li', { ordered: !!list?.ordered, n: list?.n ?? 1 });
          } else open(tag === 'blockquote' ? 'quote' : tag);
        } else cur = null;
      } else if (tag === 'ul' || tag === 'ol') {
        if (!closing) lists.push({ ordered: tag === 'ol', n: 0 });
        else lists.pop();
        cur = null;
      } else if (tag === 'strong' || tag === 'b') bold += closing ? -1 : 1;
      else if (tag === 'em' || tag === 'i') italic += closing ? -1 : 1;
      else if (tag === 'a') href = closing ? null : (m[3].match(/href\s*=\s*["']([^"']+)["']/i)?.[1] ?? null);
      else if (tag === 'br') {
        if (!cur) open('p');
        cur.runs.push({ text: '\n' });
      } else if (tag === 'hr') {
        blocks.push({ type: 'hr' });
        cur = null;
      } else if (tag === 'img' && !closing) {
        blocks.push({ type: 'img', src: m[3].match(/src\s*=\s*["']([^"']+)["']/i)?.[1] ?? '', alt: m[3].match(/alt\s*=\s*["']([^"']*)["']/i)?.[1] ?? '' });
        cur = null;
      }
      continue;
    }
    const text = decode(tok).replace(/\s+/g, ' ');
    if (!text.trim() && !cur) continue;
    if (!cur) open('p');
    const run = { text: cur.runs.length ? text : text.replace(/^\s+/, '') };
    if (bold > 0) run.bold = true;
    if (italic > 0) run.italic = true;
    if (href) run.href = href;
    if (run.text) cur.runs.push(run);
  }

  return blocks.filter((b) => b.type === 'hr' || b.type === 'img' ? true : b.runs.some((r) => r.text.trim()));
}
