// Axios client for the Lango website API (<site>/api/v1, the PHP site in ../web) plus small helpers:
// friendly error messages, the /ping health check and media URL fixing for local testing.
import axios from 'axios';
import Constants from 'expo-constants';
import { Platform } from 'react-native';
import { getLang } from './lang';
import { clearToken, expireSession, getToken } from './session';
import { getStored, setStored } from './storage';

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
const BUILT_IN = ((configured && configured !== 'auto' ? configured : sameSiteApi() || devServerApi()) || ENV[appEnv] || ENV.development || '').replace(/\/+$/, '');

// The address in use. A tester can change it at run time (Settings, "Server address"), so one installed build works with a computer on
// the Wi-Fi, a staging site or the live site without being built again. Imports of these two see the change (live bindings).
export let API_ROOT = BUILT_IN;
export let SITE_URL = API_ROOT.replace(/\/api$/, '');

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

/** True while the app talks to a computer on the home network or to this device (a test build), so an error screen can offer "Change server address". Members of a build that uses the live website never see it. */
export const isTestServer = () => /^https?:\/\/(localhost|(127|10)(\.\d{1,3}){3}|192\.168(\.\d{1,3}){2}|172\.(1[6-9]|2\d|3[01])(\.\d{1,3}){2}|[^/:]+\.local)(?=[:/]|$)/i.test(API_ROOT);

/** What a typed address means: "192.168.1.4/langoculturalinstitution", "http://host/site" and "https://example.org/api" all give the API root. Empty = the built-in address. */
export function normalizeServerAddress(input) {
  let s = String(input ?? '').trim().replace(/\/+$/, '');
  if (!s) return BUILT_IN;
  if (!/^https?:\/\//i.test(s)) s = `http://${s}`;
  if (!/\/api(\/v1)?$/i.test(s)) s += '/api';
  return s.replace(/\/v1$/i, '');
}

function useAddress(root) {
  API_ROOT = root;
  SITE_URL = root.replace(/\/api$/, '');
  api.defaults.baseURL = `${root}/v1`;
}

/** Points the app at another website address now and remembers it. Pass '' to go back to the built-in address. */
export async function setServerAddress(input) {
  const root = normalizeServerAddress(input);
  useAddress(root);
  await setStored('server_url', String(input ?? '').trim() ? root : null);
  return root;
}

/** Reads the remembered address once, before the app makes its first request. */
export async function loadServerAddress() {
  const saved = await getStored('server_url', null);
  if (saved) useAddress(normalizeServerAddress(saved));
}

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
