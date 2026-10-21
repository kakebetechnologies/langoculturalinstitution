# Lango Cultural Institution — Website & Admin Control Panel

Official digital home of the Lango Cultural Institution (Won Nyaci me Lango), with a
content-managed public website, a role-based admin control panel and a JSON API that the
future **Lango mobile app** will reuse.

**Stack:** PHP 8.2 · MySQL/MariaDB · no framework, no build step (runs on XAMPP or any shared host).

## Run it locally (XAMPP)

```bash
php database/install-cli.php          # creates DB "lci", tables, starter content, first admin
```

* Site: `http://localhost/langoculturalinstitution/`
* Admin: `http://localhost/langoculturalinstitution/admin/`
* First-run admin login is written to `config/first-run-credentials.txt` (git-ignored).
  You are forced to choose a new password at first sign-in. Delete the file afterwards.
* On a new server, open `/install.php` in a browser instead and follow the form.

## Look & feel

Modern charity-style layout (cream hero with overlapping circular photos, feature cards, stats band, program cards
with progress, event cards, support banner, dark footer) set in **Nunito**. The colour scheme comes from the official
LCI logo: black, red `#ee1a26`, yellow `#fddd16`, sky blue `#00adf1`, mountain grey and cream, with the logo's
flag-stripe accent along the header and footer.

* The official logo is `assets/img/logo.png` (transparent cut-out). Replace it from *Admin -> Site settings -> Branding & photos*
  or by overwriting that file. The homepage/About photos and the support-banner photo are replaceable there too.
* Colours live in the `:root` block at the top of `assets/css/site.css` and `assets/css/admin.css`.

## What is built

| Area | Public site | Admin |
|---|---|---|
| Home, About, Leadership, History, Districts (map) | ✓ | Leadership, Timeline, Districts |
| Clan Explorer (search, district filter, clan pages) | ✓ | Clans + **CSV import**, clan-leader self-service |
| Heritage: ceremonies, attire/crafts, music/dance, symbols, sites (map) | ✓ | Heritage items |
| Biodiversity register + community sightings map | ✓ | Species |
| Language: dictionary (2,829 entries), proverbs, lessons, word of the day | ✓ | Dictionary, Proverbs, Lessons, CSV import |
| News & blog (categories, bilingual, comments, share) | ✓ | News, Categories, Comments moderation |
| Events with online registration + printable ticket | ✓ | Events, Registrations |
| Programs with progress + sponsor button | ✓ | Programs |
| Media center (photo/video/audio/podcast), documents | ✓ | Media, Documents |
| Data Center: faceted search across everything | ✓ | — |
| **Community Contribution Engine** (4-step wizard, GPS, uploads, tracking code) | ✓ | Moderation queue, review notes, points, "Add to archive" |
| Contributor accounts, points, badges, leaderboard | ✓ | Users & roles |
| Donations (pledge flow), transparency dashboard | ✓ | Donations (confirm/fail), CSV export |
| Scholarship / grant / volunteer / partner / clan / diaspora forms | ✓ | Applications |
| Professionals directory | ✓ | Professionals (verify) |
| Cultural Assistant (answers only from reviewed archive, with sources) | ✓ | Knowledge base, flagged answers |
| 360° tours (A-Frame viewer) | ✓ | Tours |
| Open Data API + licence page | `/developers`, `/api/v1/*` | — |
| English / Leb Lango toggle | ✓ | **Translations** screen, per-field `_lg` content |
| Version history + restore, audit log, DB backup (.sql), CSV/JSON export | — | ✓ |

## Roles

`super_admin` and `admin` everything · `editor` content · `moderator` community submissions, comments, messages ·
`clan_leader` only their own clan (cannot self-publish) · `contributor` public accounts (no admin access).

## Folder map

```
index.php            public router          admin/        control panel (index, lib, modules, screens)
app/layout.php       header, footer, icons  app/pages/    one file per public route
app/lib/             db, helpers, i18n, auth, uploads, search & assistant
database/            schema.sql, installers, seed data     assets/      css, js, logo, photos
uploads/             user media (git-ignored)              legacy/      the old static template site (archived)
```

Add a new managed content type by adding one entry to `admin/modules.php`.

## Content rules followed

* Only facts LCI already published were used (Won Nyaci, 149 clans, nine districts, Okeme/Abuda, programme figures).
* **No clan names, proverbs or Leb Lango translations were invented.** Import the 149 clans via
  *Admin → Clans → Import CSV* (columns: `name,totem,totem_type,district,summary,leader_name,leader_title`);
  rows arrive as drafts for verification. Leb Lango interface text is entered in *Admin → Translations*;
  anything blank falls back to English.
* The dictionary was imported from the LCI language project and is marked **Draft/unverified** until experts verify it.
* Sample news, events and programme dates are placeholders — edit or delete them before launch.
* Privacy and Terms pages are drafts for legal review.

## Before going live

1. Set `'debug' => false` in `config/config.php` (the web installer does this) and serve over HTTPS.
2. Replace placeholder contact details in *Admin → Site settings*.
3. Connect a payment gateway (MTN MoMo / Airtel / cards / PayPal) — today pledges are recorded and confirmed manually.
4. Add email sending (event tickets, contribution status) via SMTP.
5. Schedule `mysqldump` + copy of `uploads/` off-site.
