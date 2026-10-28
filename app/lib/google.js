// "Continue with Google" through the phone's browser (no native Google library, so it works in Expo Go and in store builds).
//   1. make a secret PKCE verifier and send only its hash (the challenge) to the website;
//   2. open <site>/auth/google/start in the in-app browser → Google → the website checks the person;
//   3. the browser comes back to the app link (lango://auth?code=ONE_TIME_CODE);
//   4. trade that code and the verifier for an API token (lib/auth.jsx → POST /api/v1/auth/google/exchange).
// A new person is then asked for a clan (every member has one) before the account is created. The button only shows when the website
// says Google sign-in is set up (GET /ping → google: true).
import { useEffect, useState } from 'react';
import * as Crypto from 'expo-crypto';
import * as Linking from 'expo-linking';
import * as WebBrowser from 'expo-web-browser';
import { ping, SITE_URL } from './api';

// While testing on a phone the sign-in page may have to live on a public https address (Google refuses private addresses).
const authBase = () => (process.env.EXPO_PUBLIC_GOOGLE_AUTH_URL || SITE_URL).replace(/\/+$/, '');

export class GoogleSignInError extends Error {
  constructor(message, code = 'failed') {
    super(message);
    this.code = code;
  }
}

const toHex = (bytes) => Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
const toBase64Url = (base64) => base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

/** Resolves with { code, verifier }, or null if the person closed the browser. Throws GoogleSignInError with a message that is safe to show. */
export async function googleBrowserSignIn() {
  const verifier = toHex(await Crypto.getRandomBytesAsync(48));
  const challenge = toBase64Url(await Crypto.digestStringAsync(Crypto.CryptoDigestAlgorithm.SHA256, verifier, { encoding: Crypto.CryptoEncoding.BASE64 }));

  const returnUrl = Linking.createURL('auth'); // lango://auth (exp://…/--/auth in Expo Go)
  const startUrl = `${authBase()}/auth/google/start?redirect=${encodeURIComponent(returnUrl)}&challenge=${challenge}`;

  let result;
  try {
    result = await WebBrowser.openAuthSessionAsync(startUrl, returnUrl);
  } catch {
    throw new GoogleSignInError('Google sign-in cannot open here. Use a phone or an installed build.', 'unsupported');
  }
  if (result.type !== 'success') return null;

  const { queryParams = {} } = Linking.parse(result.url);
  if (queryParams.error === 'cancelled') return null;
  if (queryParams.error) {
    throw new GoogleSignInError(queryParams.error === 'denied' && queryParams.message ? String(queryParams.message) : 'We could not verify your Google sign-in. Please try again.', String(queryParams.error));
  }
  if (!queryParams.code) throw new GoogleSignInError('Google sign-in did not finish. Please try again.');
  return { code: String(queryParams.code), verifier };
}

// The new Google person waiting to choose a clan. Kept in memory only (never in a link or on disk).
let pending = null;
export const setPendingGoogle = (p) => {
  pending = p;
};
export const getPendingGoogle = () => pending;

// Whether the website has Google sign-in set up, asked once per run of the app.
let known = null;
export function useGoogleAvailable() {
  const [on, setOn] = useState(known ?? false);

  useEffect(() => {
    if (known !== null) return;
    ping()
      .then((p) => {
        known = !!p.google;
        setOn(known);
      })
      .catch(() => {});
  }, []);

  return on;
}
