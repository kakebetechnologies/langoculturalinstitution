// Podcast episodes as tracks for the shared player (lib/player.js). Starting an episode also counts one play on the website.
import api from './api';
import { playQueue } from './player';

export const episodeTrack = (e) => ({ id: `e${e.id}`, title: e.title, credit: e.storyteller, audio_url: e.audio_url, cover: e.cover, route: `/podcast/${e.id}` });

/** Plays `list[index]` and then the episodes after it. */
export function playEpisodes(list, index, label) {
  api.post(`/podcasts/${list[index].id}/play`).catch(() => {});
  return playQueue(list.map(episodeTrack), index, { label });
}
