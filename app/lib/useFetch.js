// Data-fetching hook for GET endpoints, with a copy kept on the phone: the last answer shows at once (even with no internet) and
// is refreshed in the background. Returns { data, loading, refreshing, error, stale, reload, retry } so every screen can show
// loading, error and empty states the same way. `error` is only set when there is nothing at all to show.
import { useCallback, useEffect, useRef, useState } from 'react';
import api, { errorMessage } from './api';
import { readJson, writeJson } from './localFiles';

const hash = (s) => {
  let h = 5381;
  for (let i = 0; i < s.length; i++) h = ((h << 5) + h + s.charCodeAt(i)) | 0;
  return (h >>> 0).toString(36);
};

export function useFetch(path, { params, enabled = true, cache = true } = {}) {
  const [state, setState] = useState({ data: null, loading: enabled, refreshing: false, error: null, stale: false });
  const latest = useRef(0);
  const paramsKey = JSON.stringify(params ?? {});
  const cacheName = `cache/${hash(path + paramsKey)}.json`;

  const load = useCallback(
    async (mode = 'load') => {
      if (!enabled) return;
      const id = ++latest.current;
      let cached = null;
      if (cache && mode === 'load') cached = await readJson(cacheName);
      if (id !== latest.current) return;
      setState((s) => ({
        data: cached ? cached.data : mode === 'refresh' ? s.data : null,
        loading: mode === 'load' && !cached,
        refreshing: mode === 'refresh' || (mode === 'load' && !!cached),
        error: null,
        stale: !!cached,
      }));
      try {
        const { data } = await api.get(path, { params: JSON.parse(paramsKey) });
        if (id !== latest.current) return;
        setState({ data, loading: false, refreshing: false, error: null, stale: false });
        if (cache) writeJson(cacheName, { at: Date.now(), data });
      } catch (e) {
        if (id !== latest.current) return;
        setState((s) => ({ ...s, loading: false, refreshing: false, error: s.data ? null : errorMessage(e), stale: !!s.data }));
      }
    },
    [path, paramsKey, enabled, cache, cacheName],
  );

  useEffect(() => {
    load('load');
    return () => {
      latest.current++;
    };
  }, [load]);

  return { ...state, reload: () => load('refresh'), retry: () => load('load') };
}
