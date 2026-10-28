# Lango Cultural Institution — Website & Admin Control Panel

Official digital home of the Lango Cultural Institution (Won Nyaci me Lango), with a
content-managed public website, a role-based admin control panel and the JSON API (`/api/v1`) that the
**Lango App** (`../app`) uses. This folder is the `web/` half of the repository; see the [root README](../README.md).

**Stack:** PHP 8.2 · MySQL/MariaDB · no framework, no build step (runs on XAMPP or any shared host).

## Run it locally (XAMPP)

The repository root has an `.htaccess` that maps requests into this folder, so the URLs below work from the XAMPP checkout as they
are. On a live server, point the domain's document root at `web/` directly.

```bash
php web/database/install-cli.php      # from the repo root (or: php database/install-cli.php from here) — creates DB "lci", tables, starter content, first admin
```

* Site: `http://localhost/langoculturalinstitution/`
* Admin: `http://localhost/langoculturalinstitution/admin/`
* First-run admin login is written to `config/first-run-credentials.txt` (git-ignored).
  You are forced to choose a new password at first sign-in. Delete the file afterwards.
* On a new server, open `/install.php` in a browser instead and follow the form.
* **Existing install?** After pulling new code run `php web/database/upgrade-cli.php`. It adds any missing columns and
  tables and loads starter content (districts, history, notable people, clans, sample leaders) without overwriting
  anything an editor has changed. Safe to run repeatedly; new installs run it automatically.

## Look & feel

The FaithFellows look (also used by the Lango App), in the Lango colours: a contained layout (content in a 1180px container with
real side margins, and a framed page on very wide monitors) on a soft canvas, white rounded cards with a gentle shadow and no
borders, pill buttons, and deep black-to-red gradient panels (home hero, dark bands, footer, inner-page headers). Type is
**Clarity City** (files in `assets/fonts/`, SIL Open Font Licence); it has no ɛ ɔ ŋ, so the browser takes those letters from **Inter**,
which is next in the font stacks. The colour scheme comes from the official LCI logo: black, red `#ee1a26`, yellow `#fddd16`,
sky blue `#00adf1`, mountain grey and cream, with the logo's flag-stripe accent on the header and footer.

* **Navigation:** the full menu is inline from 992px up (the wordmark hides on narrower laptops so nothing wraps);
  below 992px it becomes a drawer with expandable sections. A slim top bar carries phone, email, language and sign-in.
* The official logo is `assets/img/logo.png` (transparent cut-out). Replace it from *Admin -> Site settings -> Branding & photos*
  or by overwriting that file. The homepage/About photos and the support-banner photo are replaceable there too.
* Design tokens (colours, container width, radii, type) live in the `:root` block at the top of `assets/css/site.css`
  and `assets/css/admin.css`. The FaithFellows restyle is the last block of each file ("FaithFellows layer"), so it can be
  found and tuned in one place. Do not put `backdrop-filter` on `.header`: it would trap the fixed mobile menu inside it.

## What is built

| Area | Public site | Admin |
|---|---|---|
| Home, About (with the Won Nyaci's portrait), Leadership | ✓ | Leadership (Won Nyaci, cabinet, elders) |
| **History**: ten accordion chapters, table of contents, timeline, glossary, sources | ✓ | History chapters, Timeline, Glossary |
| **Districts & Lira City**: atlas list, comparison table, map; one page per district with demography, economy, best-known-for, places, history | ✓ | Districts (all fields editable) |
| **Notable Langos**: people pages with stories (Obote, Oyite-Ojok, Akii-Bua …) and portraits | ✓ | Notable Langos |
| Clan Explorer (search, clan pages showing the clan leader and photo) | ✓ | Clans, **Add clans in bulk**, CSV import, clan-leader self-service |
| Heritage: ceremonies, attire/crafts, music/dance, symbols, sites (map) | ✓ | Heritage items |
| Biodiversity register + community sightings map | ✓ | Species |
| Language: **dictionary** (plain-keyboard search, A–Z, topics), **AI translator** (English ⇄ Leb Lango), proverbs, greetings, lessons | ✓ | Dictionary, Proverbs, Lessons, CSV import |
| **Word & proverb suggestions** from the public → review → approve → live in the dictionary and translator | ✓ | **Word suggestions** (edit, approve, quick-add, reject), duplicate check |
| **Bell notifications** for every submission (contribution, word, message, application, donation, comment, directory profile, event registration, new member, clan missing from the register) | — | Bell + **Notifications** page, live count |
| News & blog (categories, bilingual, comments, share) | ✓ | News, Categories, Comments moderation |
| Events with online registration + printable ticket | ✓ | Events, Registrations |
| Programs with progress + sponsor button | ✓ | Programs |
| Media center (photo/video/audio/podcast), documents | ✓ | Media, Documents |
| Data Center: faceted search across everything | ✓ | — |
| **Community Contribution Engine**: one simple form, optional account creation on the spot, clan picker, GPS, uploads, tracking code | ✓ | Moderation queue, review notes, points, "Add to archive" |
| Contributor accounts (with clan), points, badges, leaderboard | ✓ | Users & roles |
| **Clan picker on every registration** (account, contribution, scholarship/grant/volunteer, diaspora, event tickets, professionals) with a "my clan is not listed" option that alerts the admins | ✓ | Clan shown on each record |
| Donations (pledge flow), transparency dashboard | ✓ | Donations (confirm/fail), CSV export |
| Scholarship / grant / volunteer / partner / clan / diaspora forms | ✓ | Applications |
| Professionals directory | ✓ | Professionals (verify) |
| Cultural Assistant: answers only from the register and reviewed archive, with sources, including **"Who is the clan leader of …?"**, district facts and notable people | ✓ | Knowledge base, flagged answers |
| 360° tours (A-Frame viewer) | ✓ | Tours |
| Open Data API + licence page | `/developers`, `/api/v1/*` | — |
| **Lango App API**: token sign-in, contributions with media (idempotent, so offline retries never duplicate), member notifications + Expo push, offline dictionary export | `/api/v1` ([docs](docs/API.md)) | Review/approve actions notify the member |
| **Installable web app (PWA)** built from `../app` | `/pwa/` (run `npm run pwa` in `app/`) | — |
| English / Leb Lango toggle | ✓ | **Translations** screen, per-field `_lg` content |
| Version history + restore, audit log, DB backup (.sql), CSV/JSON export | — | ✓ |

## Roles

`super_admin` and `admin` everything · `editor` content · `moderator` community submissions, comments, messages ·
`clan_leader` only their own clan (cannot self-publish) · `contributor` public accounts (no admin access).

## Folder map

```
index.php            public router          admin/        control panel (index, lib, modules, screens)
app/layout.php       header, footer, icons  app/pages/    one file per public route
app/lib/             db, helpers, i18n, auth, uploads, search, assistant, translator engine
app/data/            translator dictionaries, textbook sentences, greetings (from the langoai project)
database/            schema.sql, installers, upgrade.php,  assets/      css, js, logo, photos, people portraits
                     content/ (districts, history, people)
uploads/             user media (git-ignored)              legacy/      the old static template site (archived)
docs/API.md          the /api/v1 reference                 pwa/         the web app, generated by app/scripts/build-pwa.js (git-ignored)
app/lib/app_api.php  the mobile-app endpoints (called from app/pages/api.php)
```

## API for the app

`/api/v1` is stateless: no PHP session, no cookies. A member signs in with `POST /auth/login` or `/auth/register` and receives a
bearer token (`lci_…`, only its SHA-256 hash is stored in `api_tokens`). Errors are `{ message, errors }`, lists carry
`meta { page, limit, total, has_more }`, writes are rate limited, and a contribution sent twice with the same `client_ref` is stored once.
The full endpoint list is in [`docs/API.md`](docs/API.md). The reverse proxy / Apache must pass the `Authorization` header
(`.htaccess` already does for Apache).

When a reviewer approves, publishes or returns a contribution in the admin panel, `contribution_status_notify()` (in
`app/lib/helpers.php`) writes a `user_notifications` row and sends an Expo push to the member's registered phones.

Add a new managed content type by adding one entry to `admin/modules.php`.

## Content rules followed

* Only facts LCI already published were used (Won Nyaci, 149 clans, nine districts, Okeme/Abuda, programme figures).
* **No clan names, proverbs or Leb Lango translations were invented.** Import the 149 clans via
  *Admin → Clans → Import CSV* (columns: `name,totem,totem_type,district,summary,leader_name,leader_title`);
  rows arrive as drafts for verification. Leb Lango interface text is entered in *Admin → Translations*;
  anything blank falls back to English.
* Only five clans could be sourced and are loaded: **Otengoro Olang** (supplied by LCI) plus **Atek, Okarowok, Arak and Oki**
  (named as Lango clans in published research). Load the rest with *Admin → Clans → Add clans in bulk* (paste names, one per line).
* **No proverbs are pre-loaded**, because none could be sourced reliably. The public "Add a proverb" form feeds the review queue.
* **Notable Langos have no portraits yet** (monograms are shown). Add properly licensed photographs in *Admin → Notable Langos*.
* The four cabinet and elder entries on the Leadership page are **sample slots** ("Name to be announced"); edit them in
  *Admin → Leadership*. The Won Nyaci's photograph is the one supplied by LCI.
* District populations are 2024 census figures (Uganda Bureau of Statistics); areas and creation years come from published
  sources and should be reviewed by LCI. The History chapters are compiled from published sources (listed on the page)
  for LCI's historians to review.
* The dictionary was imported from the LCI language project and is marked unverified until experts verify it. Words approved
  from community suggestions are marked "Community".
* Sample news, events and programme dates are placeholders — edit or delete them before launch.
* Privacy and Terms pages are drafts for legal review.

## Before going live

1. Set `'debug' => false` in `config/config.php` (the web installer does this) and serve over HTTPS.
2. Replace placeholder contact details in *Admin → Site settings*.
3. Connect a payment gateway (MTN MoMo / Airtel / cards / PayPal) — today pledges are recorded and confirmed manually.
4. Add email sending (event tickets, contribution status) via SMTP.
5. Schedule `mysqldump` + copy of `uploads/` off-site.
6. For the app: serve the site over HTTPS (Android blocks plain http), put the live address into `app/eas.json`, set up Firebase for
   push (see `app/README.md`), and run `npm run pwa` in `app/` if you want the web app at `/pwa/`.
7. Remove `web/pwa/` from the server if you do not want the web app online; nothing else depends on it.
