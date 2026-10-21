// Axios client for the Lango website API (<site>/api/v1, the PHP site in ../web) plus small helpers:
// friendly error messages, the /ping health check and media URL fixing for local testing.
import axios from 'axios';
import Constants from 'expo-constants';
import { Platform } from 'react-native';
import { getLang } from './lang';
import { clearToken, expireSession, getToken } from './session';

const ENV = Constants.expoConfig?.extra?.apiUrl || {};
const appEnv = process.env.EXPO_PUBLIC_APP_ENV || 'development';
const apiPath = process.env.EXPO_PUBLIC_API_PATH ?? '/langoculturalinstitution';

/**
 * While developing, the computer that serves the app to the phone is also the one running the website (XAMPP), so its address is
 * known from the dev server itself (192.168.1.4:8081 -> http://192.168.1.4/langoculturalinstitution/api). That way the phone keeps
 * working when the computer's address changes (home Wi-Fi, a phone hotspot, an office network).
 */
function devServerApi() {
  if (!__DEV__) return '';
  const fromBrowser = typeof document !== 'undefined' ? globalThis.location?.hostname : '';
  const host = String(Constants.expoConfig?.hostUri || Constants.expoGoConfig?.debuggerHost || fromBrowser || '').split(':')[0];

  return host && !host.includes('exp.direct') ? `http://${host}${apiPath}/api` : '';
}

/**
 * The installable web version lives in <site>/pwa, next to the API: work out the site's address from the page's own address.
 * (A web app served from the Expo dev server in development uses the dev-server rule above.)
 */
function sameSiteApi() {
  if (Platform.OS !== 'web' || __DEV__ || typeof location === 'undefined') return '';
  const base = location.pathname.replace(/\/pwa(\/.*)?$/, '');
  return /\/pwa(\/|$)/.test(location.pathname) ? `${location.origin}${base}/api` : '';
}

// EXPO_PUBLIC_API_URL wins (set it to a real address, or leave it as "auto" / empty to use the rules above);
// otherwise the per-environment URL from app.json.
const configured = (process.env.EXPO_PUBLIC_API_URL || '').trim();
export const API_ROOT = ((configured && configured !== 'auto' ? configured : sameSiteApi() || devServerApi()) || ENV[appEnv] || ENV.development || '').replace(/\/+$/, '');
export const SITE_URL = API_ROOT.replace(/\/api$/, '');

const api = axios.create({
  baseURL: `${API_ROOT}/v1`,
  timeout: 20000,
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
});

api.interceptors.request.use(async (config) => {
  const token = await getToken();
  if (token) config.headers.Authorization = `Bearer ${token}`;
  config.headers['X-App-Lang'] = getLang();
  return config;
});

api.interceptors.response.use(
  (res) => res,
  async (error) => {
    // A 401 on a signed-in request means the token expired or was revoked.
    if (error.response?.status === 401 && error.config?.headers?.Authorization) {
      await clearToken();
      expireSession();
    }
    return Promise.reject(error);
  },
);

/** Health check: GET /api/v1/ping. Resolves with { status, name, version, ms }. */
export async function ping() {
  const started = Date.now();
  const { data } = await axios.get(`${API_ROOT}/v1/ping`, { timeout: 8000, headers: { Accept: 'application/json' } });
  return { ...data, ms: Date.now() - started };
}

export { errorMessage, fieldErrors, isConnectionMessage, OFFLINE_MESSAGE, SLOW_MESSAGE } from './errors';

/**
 * Media URLs come from the server's own address. While testing on a phone that is often "localhost", which the phone cannot
 * reach, so point those at the API host instead.
 */
export function mediaUrl(url) {
  if (!url || !SITE_URL) return url || null;
  const origin = SITE_URL.match(/^https?:\/\/[^/]+/)?.[0];
  return origin ? url.replace(/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?/i, origin) : url;
}

/** Axios options for sending a FormData (the browser or phone sets the multipart boundary itself). */
export const multipart = { headers: { 'Content-Type': 'multipart/form-data' }, transformRequest: (d) => d, timeout: 120000 };

export default api;
