# Lango App

The mobile app of the Lango Cultural Institution — heritage, language, clans and community contributions.
Expo SDK 57 · React Native 0.86 · Expo Router · JavaScript. One codebase for **Android**, **iOS** and the installable **web app (PWA)**.
It talks to the PHP website in [`../web`](../web) through the JSON API (`/api/v1`, see [`../web/docs/API.md`](../web/docs/API.md)).

## Run it

```bash
npm install
cp .env.example .env     # optional: the defaults work with XAMPP
npx expo start           # w = browser, a = Android emulator, or scan the QR code with Expo Go
```

The website must be running (XAMPP: Apache + MySQL, database installed with `php ../web/database/install-cli.php`).
In development the app builds the API address from the computer running Expo — `http://<computer-ip>/langoculturalinstitution/api`
— so a phone on the same Wi-Fi keeps working when the computer's address changes. To point at another server set
`EXPO_PUBLIC_API_URL` in `.env` (only `EXPO_PUBLIC_*` variables reach the app). The Settings screen shows the address in use and
has a *Check connection* button.

| Command | What it does |
|---|---|
| `npm start` | Expo dev server |
| `npm run web` | Dev server in the browser |
| `npm run strings` | Re-collects every `t('…')` phrase into `lib/strings.json` (run after changing screen text) |
| `npm run bundle` | `expo export --platform android` — builds the JS bundle to catch errors without a device |
| `npm run pwa` | Builds the web app and publishes it into the website at `../web/pwa` (served at `<site>/pwa/`) |
| `npm run doctor` | `expo-doctor` — checks dependencies and configuration |

## Structure

```
app/                  routes (Expo Router, one file per screen)
  (auth)/             welcome, login, register
  (tabs)/             Home · Explore · Contribute · Learn · Me   (custom AppTabBar)
  contribute/         guided form (new), thank-you (done), tracking (track)
  clans/ districts/ people/ heritage/ species/ news/ events/ programs/   list + [slug] detail
  dictionary translate proverbs phrasebook history library connect professionals assistant
  support donate leaderboard notifications contributions profile settings about legal/[page]
components/           AppText, Button, Card, StateView, TextField, Select, MediaField, AudioRecorder, Sheet, …
constants/            theme.js (logo palette, spacing, type), contribute.js (the seven contribution kinds)
lib/                  api.js, useFetch.js, auth, i18n, outbox, dictionary, push, media, location, tickets, …
scripts/              extract-strings.js, build-pwa.js
public/               PWA manifest, service worker, icons
```

## How it behaves

- **Every screen handles loading, error and empty states** through `components/StateView.jsx`.
- **Stale-while-revalidate:** `lib/useFetch.js` shows the last saved copy of a page immediately (kept on the device), refreshes it in the
  background and keeps showing it when there is no internet. Pull down to refresh.
- **Offline contributions:** `lib/outbox.js` — when a send fails because of the connection, the contribution (and its photo, audio or
  file, copied into the app's own folder) waits in a queue with a banner, and is sent automatically when the connection returns.
  Each one carries a unique `client_ref`, so a retry can never create a duplicate on the server.
- **Offline dictionary:** *Settings → Offline dictionary → Download* saves all published words (`/dictionary/export`, versioned, so an
  update downloads nothing when nothing changed). Search then works with no internet in both directions, with the same
  plain-keyboard folding as the website (`e` finds `ε`, `ng` finds `ŋ`).
- **Tickets and tracking codes** are saved on the phone so they open offline.
- **Sign-in:** a bearer token kept in the secure store (Keychain / Keystore; local storage in the browser). A rejected token signs the
  person out cleanly.
- **Language:** English or Leb Lango. `t('English phrase')` looks the phrase up in the translations entered by LCI's language experts
  (*Admin → Translations*); anything not translated shows in English. On first run the app registers its phrases with the server
  so they appear in the admin screen ready to translate. Nothing is machine-translated or invented.
- **Push notifications:** when a reviewer approves, publishes or returns a contribution, the member gets an in-app notification and, if
  they allowed it, an Expo push.

## Push notifications setup

Push needs a real build, not Expo Go on Android:

1. Create a Firebase project, add the Android app `tech.kakebe.lango`, download `google-services.json` and save it in this folder
   (it is git-ignored) — or set `GOOGLE_SERVICES_JSON` to its path. `app.config.js` picks it up.
2. Run `eas credentials` to upload the FCM key (Android) and let EAS manage the Apple key (iOS).
3. The Expo project id is written to `app.json` by `eas init`; the app reads it to ask for a push token.

The server side (`expo_push()` in `../web/app/lib/helpers.php`) needs nothing else.

## Building installable apps

No Android SDK or Apple tooling is needed on your computer — EAS builds in the cloud.

```bash
npm install -g eas-cli
eas login
eas init                                  # once: creates the Expo project and writes its id into app.json
eas build -p android --profile preview    # installable APK (share the link to install)
eas build -p android --profile production # .aab for Google Play
eas build -p ios --profile production     # needs an Apple Developer account
eas submit -p android                     # upload to Google Play
```

Profiles in `eas.json`:

| Profile | Output | API address |
|---|---|---|
| `preview`, `production-apk` | APK | `https://myculture.online/api` |
| `production` | Android App Bundle | `https://myculture.online/api` |
| `preview-ios` | iOS ad-hoc build | `https://myculture.online/api` |
| `local-wifi` | APK for testing against the XAMPP site on the home network | `http://192.168.0.167/langoculturalinstitution/api` (cleartext allowed for this profile only) |

The live website is <https://myculture.online>. Its address is set in `eas.json` (store and preview builds) and in `app.json` →
`extra.apiUrl` (the fallback by environment); `app/.env` stays pointed at the development computer, so local work never touches
the live site. Change `192.168.0.167` to your computer's address for the `local-wifi` profile. Over-the-air updates
(`expo-updates`) are installed but deliberately left unconfigured until an EAS project exists.

## The web app (PWA)

`npm run pwa` exports the app for the web with the base path `<site>/pwa`, adds the manifest, icons and a service worker, and copies the
result into `../web/pwa`. The page finds the API from its own address (`<site>/api`), so no configuration is needed. Browsers that support it
offer *Install* / *Add to Home screen*. Camera, microphone and location work in the browser when the site is served over HTTPS.

## Not built yet

SMS/USSD fallback for areas without data, OTP (one-time-code) sign-in, community forum and clan discussion groups, maps and
360° tours inside the app, a live payment gateway (donations are recorded as pledges for the finance office to confirm) and an
AI chatbot beyond the current source-bound assistant.
