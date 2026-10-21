# Lango API v1

The JSON API behind the Lango App (`../app`) and the open-data page at `/developers`. It lives in the PHP site:
`web/app/pages/api.php` routes the request, `web/app/lib/app_api.php` answers it.

**Base URL:** `<site>/api/v1` — for example `http://localhost/langoculturalinstitution/api/v1`.

## Conventions

| | |
|---|---|
| Format | JSON in, JSON out. Contribution uploads use `multipart/form-data`. |
| Success | `{ "data": … }`. Lists also carry `meta: { page, limit, total, has_more }` (`?page=` and `?limit=`). |
| Errors | `{ "message": "…", "errors": { "field": "…" } }` with the usual status code (401, 404, 422, 429 …). |
| Sign-in | `Authorization: Bearer lci_…` — a token from `/auth/login` or `/auth/register`. Only a SHA-256 hash is stored. |
| Language | `?lang=lg` or the `X-App-Lang: lg` header returns Leb Lango where an expert has translated it, English otherwise. |
| Sessions | None. `/api/v1/` never starts a PHP session or sets a cookie. |
| Limits | Sign-in, registration, uploads, the translator and the assistant are rate limited (HTTP 429, table `rate_limits`). |
| CORS | Open for the PWA and the dev server; `OPTIONS` pre-flights are answered. |

## Accounts

| Method | Path | Notes |
|---|---|---|
| POST | `/auth/register` | `name, email, password (8+), clan_id (id or "other" + clan_other)`, optional `phone, country` → `{ token, user }` |
| POST | `/auth/login` | `login` (email or phone) + `password` → `{ token, user }` |
| DELETE | `/auth/token` | Signs out (revokes this token) |
| GET / PATCH | `/me` | Profile with `points`, `level`, `counts`, `badges` |
| DELETE | `/me` | Deletes the account (needs `password`); contributions stay, unlinked |
| POST | `/me/password` | `current_password`, `password` |
| GET | `/me/contributions` | The member's own contributions with status and review note |
| GET / POST | `/me/notifications`, `/me/notifications/read` | In-app notifications; read one (`id`) or all (`all: true`) |
| POST / DELETE | `/me/device` | Registers / removes the Expo push token (`token`, `platform`) |

## Contributions

| Method | Path | Notes |
|---|---|---|
| POST | `/contributions` | Signed in. Fields: `type` (`cultural`, `natural`, `language`, `oral_history`, `document`, `heritage_site`), `title`, `description`, optional `clan_id`, `district_id`, `lat`, `lng`, `location_text`, `observed_on`, `rights`, one file in `media_image`, `media_audio`, `media_video` or `media_document`. Words and proverbs add `kind=word|proverb`, `word_cat`, `word_pos`, `example_lg`, `note`, `credit`. Send a unique `client_ref` so a retry after a dropped connection never creates a duplicate. → `{ code, status }` |
| GET | `/contributions/track?code=` | Status of one contribution by its tracking code |

Approving, publishing or returning a contribution in the admin panel creates a notification for the member and sends an Expo push to their registered phones.

## Content (read only)

| Path | Returns |
|---|---|
| `/ping` | Health check |
| `/home` | Word of the day, a featured notable Lango, latest news, upcoming events, programs, site counts, unread count |
| `/clans`, `/clans/options`, `/clans/{slug}` | Clan register (`?q=`, `?district=`), id/name pairs for pickers, one clan with its leader |
| `/districts`, `/districts/{slug}` | The nine districts and Lira City |
| `/people`, `/people/{slug}` | Notable Langos (`?category=`) |
| `/history` | History chapters, timeline, glossary |
| `/heritage`, `/heritage/{slug}` | Ceremonies, crafts, music, symbols, sites (`?category=`, `?q=`) |
| `/species`, `/species/{slug}` | Biodiversity register |
| `/library` | Published documents with download links |
| `/news`, `/news/{slug}` | Articles |
| `/dictionary` | Search (`?q=&dir=lg|en|all&cat=&letter=`), paged |
| `/dictionary/export` | The whole dictionary for offline use; pass `?version=` to get `{ unchanged: true }` when nothing changed |
| `/proverbs`, `/greetings` | Published proverbs (none are pre-loaded); greetings from the Practical Lango Grammar |
| `/translate` | `text`, `direction=en-lango|lango-en` — dictionary-based, not machine translation |
| `/assistant` | POST `q` — answers only from reviewed LCI content, with sources |
| `/events`, `/events/{slug}` | Upcoming (or `?past=1`) events |
| `/programs`, `/programs/{slug}`, `/transparency` | Programs with progress; confirmed donation totals |
| `/professionals` | GET the directory; POST (signed in) a profile for verification |
| `/leaderboard`, `/about` | Top contributors; organisation details |

## Other writes

| Method | Path | Notes |
|---|---|---|
| POST | `/events/{slug}/register` | `name, email, tickets`; returns the ticket code |
| POST | `/donations` | Records a pledge; the finance office confirms it. No payment gateway is connected yet. |
| POST | `/i18n/register` | The app lists its English phrases (`keys: []`) so they appear in *Admin → Translations* |
| GET | `/i18n` | The Leb Lango phrases entered so far (`{ "English phrase": "Leb Lango" }`) |

The older open-data endpoints documented at `/developers` keep working unchanged.
