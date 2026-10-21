# Lango Cultural Institution

The official website, admin control panel and mobile app of the **Lango Cultural Institution** (*Won Nyaci me Lango*),
built by [Kakebe Technologies Limited](https://kakebe.tech) — Lira City, Uganda.

## Repository structure

```
lango/
├── web/   → PHP 8.2 + MySQL website, admin panel and JSON API (/api/v1)
├── app/   → Lango App — Expo / React Native (Android, iOS) and the installable web app (PWA)
├── scripts/dev.sh        → checks the API, then starts Expo
├── lango.code-workspace  → VS Code workspace with both projects
└── .htaccess             → keeps http://localhost/langoculturalinstitution/ working (maps requests into web/)
```

One repository, one database, one backend: the PHP site serves the public website **and** the API the app uses.
The app and the site share the same accounts, clans, dictionary and moderation queue, so a word suggested on a phone shows up
in *Admin → Word suggestions* and, once approved, in the dictionary and the translator on both.

More detail: [`web/README.md`](web/README.md) (site, admin, content rules, going live) ·
[`web/docs/API.md`](web/docs/API.md) (API v1) · [`app/README.md`](app/README.md) (the app, builds, offline behaviour).

## What the app does

| Area | Features |
|---|---|
| Onboarding & account | Language choice (English / Leb Lango), sign up with clan, sign in with email or phone, browse as a guest |
| Home | Greeting, quick actions, word of the day, your points and level, a notable Lango, news, events, donation call |
| Contribute | Seven guided forms (cultural observation, natural heritage, word, proverb, oral history, document, heritage site) with GPS, camera, gallery, audio recording and files; tracking code; **works offline** — drafts and uploads queue on the phone and send when the connection returns |
| Explore | Clans, districts and Lira City, notable Langos, history and timeline, heritage, biodiversity, digital library |
| Learn | Dictionary (works offline once downloaded), English ⇄ Leb Lango translator, proverbs, phrasebook, "suggest a word" |
| Connect | Events with ticket registration, professionals directory, cultural assistant (answers only from reviewed content), news, leaderboard |
| Support | Donation pledges, programs, transparency figures |
| Me | Points, Bronze → Platinum levels, badges, my contributions and their status, notifications (in-app + push), settings |

## Tech stack

**Web (`web/`)** — PHP 8.2, MySQL / MariaDB, no framework and no build step; runs on XAMPP or any shared host.
Role-based admin panel; stateless bearer-token API (`/api/v1`).

**App (`app/`)** — Expo SDK 57 · React Native 0.86 · React 19 · Expo Router · Axios · Expo SecureStore (token) ·
Expo Notifications (push) · Expo Audio · Expo Image Picker / Location / Document Picker · Inter + Source Serif 4. JavaScript only.

## Local development

### Prerequisites

- XAMPP (Apache + MySQL) with PHP 8.2+
- Node.js 20+ and npm
- The **Expo Go** app on a phone (same Wi-Fi as the computer), or just a browser

### 1. The website and API

```bash
php web/database/install-cli.php      # creates the "lci" database, tables, starter content and the first admin
```

- Site: <http://localhost/langoculturalinstitution/> · Admin: <http://localhost/langoculturalinstitution/admin/>
- The first admin login is written to `web/config/first-run-credentials.txt` (git-ignored); you must choose a new password at first sign-in.
- Updating an existing install: `php web/database/upgrade-cli.php` (adds missing tables/columns, never overwrites edits).
- Check the API: <http://localhost/langoculturalinstitution/api/v1/ping>

### 2. The app

```bash
cd app
npm install
npx expo start            # press w for the browser, or scan the QR code with Expo Go
```

`app/.env.example` documents the settings. By default the app finds the website from the address of the computer running Expo
(`http://<your-ip>/langoculturalinstitution/api`), so a phone on the same Wi-Fi works without editing anything.
From the repo root, `npm run app:start` and `./scripts/dev.sh` do the same.

### 3. The installable web app (PWA)

```bash
cd app && npm run pwa
```

Exports the app and publishes it inside the website at <http://localhost/langoculturalinstitution/pwa/> (the folder is generated
and git-ignored). Open it on a phone and choose *Add to Home screen*.

## Building the app for phones

`app/eas.json` defines the EAS Build profiles; see [`app/README.md`](app/README.md#building-installable-apps) for the one-time
setup (Expo account, `eas init`, the live API address) and the commands. A debug JS bundle can be checked without any device:

```bash
cd app && npm run bundle        # expo export --platform android
```

## Content rules

Cultural content is never invented. Clans, proverbs, translations and biographies come from LCI, published sources or
community contributions that a reviewer approves. Anything missing is shown as empty, not made up — see the
*Content rules followed* section of [`web/README.md`](web/README.md).

## Contact

Kakebe Technologies Limited · Rm B1 Plot 43, Renzo City House, Obote Ave., Lira City · 0393249845 · service@kakebe.tech
