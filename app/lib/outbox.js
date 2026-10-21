// Contributions made with no internet — a photo of a heritage site in a village with no signal, a word typed on a bus — wait here
// and are sent in order as soon as there is a connection (when the internet comes back, when the app comes forward, every 15 seconds
// while something waits, and right after you submit).
//
// Each waiting contribution keeps its photo, recording or document: the file is copied into the app's own folder, so it survives
// the app being closed. Every contribution carries a unique `client_ref`; if a send is cut off half-way and retried, the website
// recognises it and does not create a duplicate. Contributions belong to one member: on sign-out the queue is cleared.
import { useEffect, useSyncExternalStore } from 'react';
import { AppState, Platform } from 'react-native';
import api, { errorMessage, multipart } from './api';
import { dropFile, keepFile, readJson, removeFile, serial, writeJson } from './localFiles';
import { isOffline, isRejected } from './net';

const QUEUE = 'outbox.json';
const MAX_WAITING = 100;

let queue = null; // [{ id, fields, media: {kind, uri, name, type} | null, at, error? }]
let snapshot = { pending: 0, flushing: false, items: [] };
const listeners = new Set();
let running = null;

async function load() {
  if (!queue) queue = (await readJson(QUEUE)) ?? [];
  return queue;
}

function emit() {
  const next = { pending: queue?.length ?? 0, flushing: !!running, items: (queue ?? []).map((q) => ({ id: q.id, title: q.fields.word || q.fields.title || '', type: q.fields.type, at: q.at, error: q.error ?? null })) };
  snapshot = next;
  listeners.forEach((l) => l());
  return serial('outbox', () => writeJson(QUEUE, queue));
}

/** `{ pending, flushing, items }` — re-renders when the waiting contributions change. */
export const useOutbox = () => useSyncExternalStore((l) => (listeners.add(l), () => listeners.delete(l)), () => snapshot);

/** Adds a file (picked photo, recorded audio, chosen document) to a FormData under `field`. */
async function appendMedia(form, media) {
  const field = `media_${media.kind}`;
  if (Platform.OS === 'web') form.append(field, await (await fetch(media.uri)).blob(), media.name);
  else form.append(field, { uri: media.uri, name: media.name, type: media.type });
}

async function send(item) {
  const form = new FormData();
  Object.entries(item.fields).forEach(([k, v]) => v !== undefined && v !== null && v !== '' && form.append(k, String(v)));
  form.append('client_ref', item.id);
  if (item.media) await appendMedia(form, item.media);
  const { data } = await api.post('/contributions', form, multipart);
  return data.data;
}

/**
 * Sends what is waiting, oldest first. Stops (keeping the rest) when there is no connection; a contribution the website refuses
 * for good (for example a file that is too large) is dropped and reported. Resolves `{ sent: [codes], failed: [{id, error}] }`.
 */
export function flush() {
  if (running) return running;
  running = (async () => {
    const result = { sent: [], failed: [], stalled: false };
    await load();
    emit();
    while (queue.length) {
      const item = queue[0];
      try {
        const res = await send(item);
        result.sent.push(res.code);
      } catch (e) {
        if (isOffline(e) || !isRejected(e)) {
          result.stalled = true;
          break;
        }
        result.failed.push({ id: item.id, error: errorMessage(e) });
      }
      dropFile(item.media?.uri);
      queue = queue.filter((o) => o.id !== item.id);
      emit();
    }
    return result;
  })().finally(() => {
    running = null;
    emit();
  });
  emit();
  return running;
}

/**
 * Submits a contribution. Resolves `{ queued: false, code }` when the website has it, or `{ queued: true }` when there is no
 * connection and it was kept on the phone. Throws when the website refused it (the error has the field messages).
 */
export async function submitContribution(fields, media = null) {
  await load();
  const id = `${Date.now().toString(36)}${Math.random().toString(36).slice(2, 8)}`;
  const kept = media ? { ...media, uri: keepFile(media.uri, `${id}-${media.name}`) } : null;
  const item = { id, fields, media: kept, at: Date.now() };
  try {
    const res = await send(item);
    dropFile(kept?.uri);
    return { queued: false, code: res.code };
  } catch (e) {
    if (isOffline(e)) {
      if (queue.length >= MAX_WAITING) dropFile(queue.shift()?.media?.uri);
      queue.push(item);
      await emit();
      return { queued: true };
    }
    dropFile(kept?.uri);
    throw e;
  }
}

/** Forgets everything waiting on this phone (sign-out). */
export async function forgetOutbox() {
  await load();
  queue.forEach((q) => dropFile(q.media?.uri));
  queue = [];
  removeFile(QUEUE);
  emit();
}

/**
 * Mount once in the signed-in app: sends what is waiting when the app starts, comes forward, the connection returns,
 * or on a timer while something waits. `onSent` runs after something was sent (to refresh lists).
 */
export function useOfflineSync(user, onSent) {
  const userId = user?.id ?? null;
  const { pending } = useOutbox();

  useEffect(() => {
    if (!userId) return undefined;
    let alive = true;
    const run = async () => {
      if (!alive) return;
      const r = await flush();
      if (r.sent.length) onSent?.(r);
    };
    run();
    const sub = AppState.addEventListener('change', (s) => s === 'active' && run());
    let network = null;
    const back = () => run();
    if (Platform.OS === 'web') globalThis.addEventListener?.('online', back);
    else {
      try {
        network = require('expo-network').addNetworkStateListener((s) => s.isInternetReachable !== false && s.isConnected && back());
      } catch {
        // No network events here: the timer below still retries.
      }
    }
    return () => {
      alive = false;
      sub.remove();
      network?.remove();
      if (Platform.OS === 'web') globalThis.removeEventListener?.('online', back);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [userId]);

  useEffect(() => {
    if (!userId || !pending) return undefined;
    const timer = setInterval(() => flush(), 15000);
    return () => clearInterval(timer);
  }, [userId, pending]);
}

// Make sure a snapshot exists for screens that mount before anything was loaded.
load().then(emit);
