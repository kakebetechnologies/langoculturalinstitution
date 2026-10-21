// The offline dictionary. "Download" saves the whole Lango–English dictionary (about 2,800 words, a few hundred KB) on the phone
// (GET /api/v1/dictionary/export); searching then works with no internet, in both directions, and finds words typed on an
// ordinary keyboard (e for ε, o for ɔ, ng for ŋ) exactly like the website does.
import { useEffect, useState } from 'react';
import api from './api';
import { readJson, removeFile, writeJson } from './localFiles';

const FILE = 'dictionary.json';
let store = null; // { version, at, entries: [[lango, english, pos, category, verified, community]] }
let folded = null;
const listeners = new Set();

const MAP = { ε: 'e', ɛ: 'e', ɔ: 'o', ŋ: 'ng', ë: 'e', ö: 'o', ï: 'i', é: 'e', è: 'e', ó: 'o', ò: 'o', á: 'a', à: 'a', í: 'i', ì: 'i', ú: 'u', ù: 'u' };

/** Lowercases and folds the special Lango letters so plain-keyboard spellings match. */
export const fold = (s) => String(s).toLowerCase().replace(/[εɛɔŋëöïéèóòáàíìúù]/g, (c) => MAP[c] ?? c);

function index() {
  folded = store ? store.entries.map((e) => fold(e[0])) : null;
  listeners.forEach((l) => l());
}

export async function loadOffline() {
  if (!store) {
    store = await readJson(FILE);
    if (store) index();
  }
  return store;
}

/** Downloads (or refreshes) the dictionary. Resolves { count, unchanged }. */
export async function downloadDictionary() {
  await loadOffline();
  const { data } = await api.get('/dictionary/export', { params: store ? { version: store.version } : {}, timeout: 90000 });
  if (data.unchanged) return { count: store.entries.length, unchanged: true };
  store = { version: data.version, at: Date.now(), entries: data.entries };
  await writeJson(FILE, store);
  index();
  return { count: store.entries.length, unchanged: false };
}

export function removeOffline() {
  store = null;
  folded = null;
  removeFile(FILE);
  listeners.forEach((l) => l());
}

/** `{ ready, count, at }` for the settings screen and the dictionary search. */
export function useOfflineDictionary() {
  const [, bump] = useState(0);
  useEffect(() => {
    const l = () => bump((n) => n + 1);
    listeners.add(l);
    loadOffline().then(l);
    return () => listeners.delete(l);
  }, []);
  return { ready: !!store, count: store?.entries.length ?? 0, at: store?.at ?? null };
}

const toWord = (e, i) => ({ id: `o${i}`, lango: e[0], english: e[1], pos: e[2] || null, category: e[3] || null, verified: !!e[4], community: !!e[5], offline: true });

/** Searches the downloaded dictionary. dir: 'all' | 'lg' | 'en'. Returns up to `limit` words, best matches first. */
export function searchOffline(query, dir = 'all', limit = 60) {
  if (!store || !folded) return [];
  const q = fold(query.trim());
  if (!q) return [];
  const hits = [];
  store.entries.forEach((e, i) => {
    const lg = folded[i];
    const en = e[1].toLowerCase();
    let rank = 9;
    if (dir !== 'en') {
      if (lg === q) rank = 0;
      else if (lg.startsWith(q)) rank = Math.min(rank, 2);
      else if (lg.includes(q)) rank = Math.min(rank, 4);
    }
    if (dir !== 'lg') {
      if (en.split(/;\s*/).includes(q)) rank = Math.min(rank, 1);
      else if (en.startsWith(q)) rank = Math.min(rank, 3);
      else if (en.includes(q)) rank = Math.min(rank, 5);
    }
    if (rank < 9) hits.push([rank, e[0].length, i]);
  });
  hits.sort((a, b) => a[0] - b[0] || a[1] - b[1]);
  return hits.slice(0, limit).map(([, , i]) => toWord(store.entries[i], i));
}

/** Words beginning with a letter (the A–Z strip), from the downloaded copy. */
export function browseOffline(letter, limit = 60) {
  if (!store || !folded) return [];
  const out = [];
  for (let i = 0; i < store.entries.length && out.length < limit; i++) if (folded[i].startsWith(letter)) out.push(toWord(store.entries[i], i));
  return out;
}
