// Records that the signed-in member opened the app today (POST /me/checkin), once per day, and tells Home when a streak milestone was reached.
import { useCallback, useEffect, useState } from 'react';
import api from './api';
import { getStored, setStored } from './storage';

const today = () => {
  const d = new Date();
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
};

/** Call from Home: `const { milestone, clear } = useDailyCheckIn(user, refresh)`. */
export function useDailyCheckIn(user, refresh) {
  const [milestone, setMilestone] = useState(null);
  const userId = user?.id ?? null;

  useEffect(() => {
    if (!userId) return;
    let live = true;
    (async () => {
      const key = `checkin_${userId}`;
      if ((await getStored(key)) === today()) return;
      try {
        const { data } = await api.post('/me/checkin', {}, { timeout: 8000 });
        await setStored(key, today());
        if (!live) return;
        if (data.data.milestone) setMilestone(data.data.milestone);
        refresh?.();
      } catch {
        // Offline: it will be tried again the next time Home opens.
      }
    })();
    return () => {
      live = false;
    };
  }, [userId, refresh]);

  return { milestone, clear: useCallback(() => setMilestone(null), []) };
}
