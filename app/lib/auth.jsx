// Authentication state for the whole app: signed-in member, token, sign-in, registration and sign-out.
// Wrap the app in <AuthProvider> and read it anywhere with useAuth().
import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { Platform } from 'react-native';
import api from './api';
import { readJson, removeDir, removeFile, writeJson } from './localFiles';
import { forgetOutbox } from './outbox';
import { disablePush } from './push';
import { clearToken, getToken, onSessionExpired, setToken } from './session';

const AuthContext = createContext(null);
const USER_FILE = 'auth/user.json';
const device = () => `Lango app (${Platform.OS})`;

export function AuthProvider({ children }) {
  const [ready, setReady] = useState(false);
  const [token, setTokenState] = useState(null);
  const [user, setUser] = useState(null);

  // Restore the session on launch. The last known profile is kept on the phone, so the app opens at once and works with no
  // internet; the profile is refreshed in the background (and retried until it works).
  useEffect(() => {
    let retry = null;
    (async () => {
      const saved = await getToken();
      setTokenState(saved);
      if (!saved) return setReady(true);

      const cached = await readJson(USER_FILE);
      if (cached) {
        setUser(cached);
        setReady(true);
      }
      const refresh = async () => {
        try {
          const { data } = await api.get('/me', { timeout: 8000 });
          setUser(data.data);
          writeJson(USER_FILE, data.data);
          clearInterval(retry);
          return true;
        } catch {
          // Offline or server down: keep the token; a 401 is handled by the session listener.
          return false;
        }
      };
      const ok = await refresh();
      setReady(true);
      if (!ok) retry = setInterval(refresh, 20000);
    })();
    return () => clearInterval(retry);
  }, []);

  // The API client reports a revoked or expired token.
  useEffect(
    () =>
      onSessionExpired(() => {
        removeFile(USER_FILE);
        setTokenState(null);
        setUser(null);
      }),
    [],
  );

  // Sets the signed-in profile and keeps a copy on the phone for starting up offline.
  const remember = useCallback((me) => {
    setUser(me);
    if (me) writeJson(USER_FILE, me);
  }, []);

  const start = useCallback(
    async (data) => {
      await setToken(data.token);
      setTokenState(data.token);
      remember(data.user);
      return data.user;
    },
    [remember],
  );

  const signIn = useCallback(async (login, password) => start((await api.post('/auth/login', { login: login.trim(), password, device_name: device() })).data), [start]);

  // Create an account (POST /auth/register) and sign in. Throws the API error for the form to show.
  const signUp = useCallback(
    async (form) =>
      start(
        (
          await api.post('/auth/register', {
            name: form.name.trim(),
            email: form.email.trim().toLowerCase(),
            phone: form.phone?.trim() || undefined,
            country: form.country?.trim() || undefined,
            password: form.password,
            clan_id: form.clan_id,
            clan_other: form.clan_other?.trim() || undefined,
            device_name: device(),
          })
        ).data,
      ),
    [start],
  );

  // Re-reads the profile (points, level and badges change when contributions are approved).
  const refresh = useCallback(async () => {
    try {
      const { data } = await api.get('/me');
      remember(data.data);
      return data.data;
    } catch {
      return null;
    }
  }, [remember]);

  const signOut = useCallback(async () => {
    await disablePush();
    try {
      await api.post('/auth/logout');
    } catch {
      // Even if the server cannot be reached, sign out on this device.
    }
    await forgetOutbox();
    removeDir('cache');
    removeFile(USER_FILE);
    await clearToken();
    setTokenState(null);
    setUser(null);
  }, []);

  const value = useMemo(() => ({ ready, token, user, signIn, signUp, signOut, refresh, setUser: remember }), [ready, token, user, signIn, signUp, signOut, refresh, remember]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used inside <AuthProvider>');
  return ctx;
}
