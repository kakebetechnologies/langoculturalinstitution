// One audio player for the whole app, shared by Lango Radio and the Podcast, so a story keeps playing while the member moves between
// screens and, on a phone, with the screen off (the lock-screen controls keep it alive). What plays is a queue of tracks
// { id, title, credit, audio_url, cover }; a live radio stream is a queue of one marked live. Screens read it with usePlayer()
// (changes rarely) and usePlayerClock() (the position, about twice a second).
import { useSyncExternalStore } from 'react';
import { Platform } from 'react-native';
import { createAudioPlayer, requestNotificationPermissionsAsync, setAudioModeAsync } from 'expo-audio';
import { mediaUrl } from './api';

const EMPTY = { queue: [], index: -1, live: false, loop: false, label: '', route: null, key: null, playing: false, loading: false, error: null };
let core = EMPTY;
let clock = { position: 0, duration: 0 };
const coreSubs = new Set();
const clockSubs = new Set();
const subscribeCore = (fn) => (coreSubs.add(fn), () => coreSubs.delete(fn));
const subscribeClock = (fn) => (clockSubs.add(fn), () => clockSubs.delete(fn));
const setCore = (patch) => {
  core = { ...core, ...patch };
  coreSubs.forEach((f) => f());
};
const setClock = (patch) => {
  clock = { ...clock, ...patch };
  clockSubs.forEach((f) => f());
};

export const usePlayer = () => useSyncExternalStore(subscribeCore, () => core);
export const usePlayerClock = () => useSyncExternalStore(subscribeClock, () => clock);
export const currentTrack = (p = core) => p.queue[p.index] ?? null;

let player = null;
let modeReady = false;
let finished = ''; // the track that last ended, so one ending moves the queue on only once

function ensure() {
  if (!player) {
    player = createAudioPlayer(null, { updateInterval: 500 });
    player.addListener('playbackStatusUpdate', onStatus);
  }
  return player;
}

// Sound in silent mode and with the screen off; the first time on Android also the permission for the "now playing" notification.
async function prepare() {
  if (modeReady) return;
  modeReady = true;
  try {
    await setAudioModeAsync({ playsInSilentMode: true, shouldPlayInBackground: true, interruptionMode: 'doNotMix' });
  } catch {}
  if (Platform.OS === 'android') {
    try {
      await requestNotificationPermissionsAsync();
    } catch {}
  }
}

function lockScreen(t) {
  try {
    player.setActiveForLockScreen(true, { title: t.title, artist: t.credit || undefined, albumTitle: core.label || undefined, artworkUrl: t.cover ? mediaUrl(t.cover) : undefined }, { isLiveStream: core.live });
  } catch {}
}

async function loadCurrent() {
  const t = currentTrack();
  if (!t) return;
  try {
    await prepare();
    const p = ensure();
    finished = '';
    p.replace({ uri: mediaUrl(t.audio_url) });
    p.play();
    lockScreen(t);
  } catch {
    setCore({ loading: false, playing: false, error: 'This cannot be played right now.' });
  }
}

function goTo(index) {
  setCore({ index, playing: false, loading: true, error: null });
  setClock({ position: 0, duration: 0 });
  return loadCurrent();
}

function ended() {
  if (core.index + 1 < core.queue.length) goTo(core.index + 1);
  else if (core.loop) goTo(0);
  else {
    player?.pause();
    player?.seekTo(0);
    setCore({ playing: false });
  }
}

function onStatus(s) {
  const t = currentTrack();
  if (!t) return;
  setClock({ position: s.currentTime || 0, duration: s.duration || 0 });
  const loading = !s.isLoaded || (s.isBuffering && !s.playing);
  const error = s.error ? 'This cannot be played right now.' : null;
  if (s.playing !== core.playing || loading !== core.loading || error !== core.error) setCore({ playing: !!s.playing, loading, error });
  if (s.didJustFinish && !core.live) {
    const key = `${core.index}:${t.id}`;
    if (finished !== key) {
      finished = key;
      ended();
    }
  }
}

/** Plays a list of tracks from `start`. `loop` goes back to the first track after the last (radio); `route` is the screen the bar opens
 *  (a track's own `route` wins); `key` names what is playing (for example "radio:3") so a screen can tell its own channel. */
export function playQueue(tracks, start = 0, { live = false, loop = false, label = '', route = null, key = null } = {}) {
  const queue = tracks.filter((t) => t?.audio_url);
  if (!queue.length) return Promise.resolve();
  const index = Math.min(Math.max(start, 0), queue.length - 1);
  setCore({ queue, index, live, loop, label, route, key, playing: false, loading: true, error: null });
  setClock({ position: 0, duration: 0 });
  return loadCurrent();
}

/** A live radio stream. */
export const playLive = (channel) =>
  playQueue([{ id: `live${channel.id}`, title: channel.name, credit: channel.description, audio_url: channel.stream_url, cover: channel.cover }], 0, { live: true, label: 'Lango Radio', route: '/radio', key: `radio:${channel.id}` });

export function toggle() {
  if (!player || !currentTrack()) return;
  if (core.playing) player.pause();
  else if (core.error) loadCurrent();
  else player.play();
}

export const seekTo = (seconds) => player?.seekTo(Math.max(0, seconds));
export const seekBy = (seconds) => {
  if (!player || core.live) return;
  const to = clock.position + seconds;
  return player.seekTo(Math.max(0, clock.duration ? Math.min(to, clock.duration - 0.5) : to));
};

export function next() {
  if (core.index + 1 < core.queue.length) goTo(core.index + 1);
  else if (core.loop) goTo(0);
}

export function previous() {
  if (clock.position > 3 || core.index <= 0) player?.seekTo(0);
  else goTo(core.index - 1);
}

/** Stops and takes the bar away. */
export function stop() {
  try {
    player?.pause();
    player?.clearLockScreenControls();
  } catch {}
  setCore(EMPTY);
  setClock({ position: 0, duration: 0 });
}

/** m:ss (or h:mm:ss) for a number of seconds. */
export function fmtClock(seconds) {
  const s = Math.max(0, Math.floor(Number(seconds) || 0));
  const h = Math.floor(s / 3600);
  const m = Math.floor((s % 3600) / 60);
  const ss = String(s % 60).padStart(2, '0');
  return h ? `${h}:${String(m).padStart(2, '0')}:${ss}` : `${m}:${ss}`;
}
