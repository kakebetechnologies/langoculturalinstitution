// Small formatting helpers: dates, numbers and initials. Dates come from the website as "2026-10-21 15:27:24" (East Africa Time).

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

/** "2026-10-21 15:27:24" -> Date (local). */
export function toDate(s) {
  if (!s) return null;
  const m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
  return m ? new Date(+m[1], +m[2] - 1, +m[3], +(m[4] ?? 0), +(m[5] ?? 0)) : new Date(s);
}

export const fmtDate = (s) => {
  const d = toDate(s);
  return d ? `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}` : '';
};

export const fmtLongDate = (s) => {
  const d = toDate(s);
  return d ? `${DAYS[d.getDay()]}, ${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}` : '';
};

export const fmtTime = (s) => {
  const d = toDate(s);
  if (!d) return '';
  const h = d.getHours();
  return `${h % 12 || 12}:${String(d.getMinutes()).padStart(2, '0')} ${h < 12 ? 'AM' : 'PM'}`;
};

export const dayParts = (s) => {
  const d = toDate(s);
  return d ? { day: d.getDate(), month: MONTHS[d.getMonth()].toUpperCase() } : { day: '', month: '' };
};

export const number = (n) => (n === null || n === undefined ? '—' : Number(n).toLocaleString('en-US'));

export const money = (n, cur = 'UGX') => `${cur} ${Number(n || 0).toLocaleString('en-US', { maximumFractionDigits: cur === 'UGX' ? 0 : 2 })}`;

export const initials = (name = '') => {
  const w = String(name).replace(/^(Maj\.?\s*Gen\.?|Gen\.?|Dr\.?|Rwot|Eng\.?)\s+/i, '').split(/[^A-Za-zÀ-ɏ]+/).filter(Boolean);
  if (!w.length) return '?';
  return (w.length > 1 ? w[0][0] + w[w.length - 1][0] : w[0].slice(0, 2)).toUpperCase();
};

/** "Grace Akello" -> "Grace". */
export const firstName = (name = '') => String(name).trim().split(/\s+/)[0] || '';

/** Strips tags and entities for a plain-text preview. */
export const plain = (html = '') => String(html).replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/\s+/g, ' ').trim();
