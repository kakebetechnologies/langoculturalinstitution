This is the Lango mobile app (Expo SDK 57, React Native, JavaScript). It talks to the PHP site in `../web` through `/api/v1`
(see `../web/docs/API.md`). Prioritize mobile-first patterns, performance and cross-platform compatibility (Android, iOS and web/PWA).

## Expo changes every SDK — check the docs

Read the major version of `expo` in `package.json`, then use the matching versioned docs
(`https://docs.expo.dev/versions/v<major>.0.0/`) or the index at `https://docs.expo.dev/llms.txt`
before writing code against an Expo, EAS or React Native API. Do not rely on memory.

## Commands

```bash
npx expo install <package>   # always use this instead of npm install for Expo/native packages
npx expo start               # dev server (scan the QR code with Expo Go, or press w for the browser)
npx expo-doctor              # diagnose dependency and config issues
npm run strings              # re-collect t('…') phrases into lib/strings.json after changing screen text
npm run bundle               # build the Android JS bundle to catch errors without a device
npm run pwa                  # build the web app into ../web/pwa
```

## Project rules

- Routes live in `app/` (Expo Router; every file is a screen, `_layout.jsx` defines navigators). Shared code lives
  outside it: `components/`, `lib/`, `constants/`.
- JavaScript only (`.js` / `.jsx`). Every file starts with a short comment saying what it is for.
- Every screen handles loading, error and empty states — use `components/StateView.jsx`.
- Brand: the colours come from the official LCI logo (`COLORS` in `constants/theme.js`); the layout follows the FaithFellows app (soft canvas, white rounded cards with a soft shadow, pill buttons, deep gradient headers). Fonts are Clarity City, with Inter for any text holding ɛ ɔ ŋ (Clarity City has no such glyphs): `AppText` switches by itself, text inputs use Inter.
  Take colours, spacing and radii from `constants/theme.js`. Use the shared components (`Card`, `Row`, `Button`, `GradientTile`, `HeroHeader`…) rather than new one-off card styles; gradients are for headers and feature tiles only.
- All API calls go through `lib/api.js` (Bearer token from secure storage, friendly `errorMessage()`); use `lib/useFetch.js` for GETs.
- All visible text goes through `t('English phrase')`; run `npm run strings` after adding any. Never write Leb Lango yourself —
  translations are entered by language experts in the website's admin panel.
- **Never invent cultural content** (clan names, proverbs, translations, biographies). Show an empty state instead.
- Contributions are sent with `lib/outbox.js` (`submitContribution`), which queues them offline and de-duplicates with `client_ref`.
- Only `EXPO_PUBLIC_*` environment variables reach the app. Keep `app/.env` out of git (use `.env.example`).
- Do not edit `ios/` or `android/` by hand; configure native behaviour in `app.json` / `app.config.js` and config plugins.
- Expo Go only includes Expo's bundled native modules. A library with its own native code needs a development build.
- Audio uses `expo-audio`; files use the `expo-file-system` class API (`File`, `Paths`).
