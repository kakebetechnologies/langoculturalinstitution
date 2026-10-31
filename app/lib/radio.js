// Starting a Lango Radio channel (shared by the Radio screen and the radio card on Home): a live stream plays at once; a playlist, or the
// channel with every podcast episode, loads its recordings (mixed up when asked) and keeps looping.
import api from './api';
import { playLive, playQueue } from './player';

const shuffled = (list) => {
  const a = [...list];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
};

/** Plays a channel. Returns 'empty' when it has nothing to play yet; a failed request throws, for the caller to show. */
export async function tuneChannel(c, shuffle = true) {
  if (c.source === 'stream') {
    await playLive(c);
    return undefined;
  }
  const { data: r } = await api.get(`/radio/${c.id}`);
  let tracks = r.data.tracks ?? [];
  if (!tracks.length) return 'empty';
  if (shuffle && tracks.length > 1) tracks = shuffled(tracks);
  await playQueue(tracks, 0, { loop: true, label: c.name, route: '/radio', key: `radio:${c.id}` });
  return undefined;
}
