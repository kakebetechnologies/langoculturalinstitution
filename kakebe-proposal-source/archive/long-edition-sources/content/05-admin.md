# 5. The Admin Control Panel

*A role-based workspace where the Foundation's own people publish, review, translate and report, with every change recorded and reversible.*

## 5.1 Designed for the people who will use it

The admin panel is where the platform stays alive after launch, so we design it for the Foundation's staff and volunteers rather than for developers. The demonstrator already shows the approach: one consistent layout, a live notification bell, a dashboard that opens on what needs attention, and every managed content type edited through the same simple forms.

![browser|Figure 5.1: The admin dashboard: items waiting for review, contributions per month, the archive at a glance, latest contributions and quick actions.](shots/admin-dashboard.jpg)

| Principle | In practice |
|---|---|
| **No code for daily work** | Pages, news, events, history, districts, clans, dictionary entries and more are edited in forms with rich-text editors and image uploads |
| **Everything is reversible** | Version history with restore on content; an audit log of who did what and when |
| **Right access for each person** | Six roles, so a clan leader sees only their clan and a moderator sees only the review queue |
| **Never miss anything** | A live bell and a Notifications page for every new submission, comment, message, application, donation, directory profile, event registration, new member and "clan not listed" alert |
| **Built to extend** | A new content type is added by registering one entry, which keeps the platform cheap to grow |

## 5.2 Roles and permissions

| Role | Typical person | Can do |
|---|---|---|
| **Super admin** | Platform owner, Kakebe during support | Everything, including users, roles, settings, backups |
| **Admin** | Foundation operations lead | Everything except platform-level settings |
| **Editor** | Communications and content staff | Create and publish news, pages, history, heritage, events, programmes, media |
| **Moderator** | Language experts, community managers | Review contributions, word suggestions, comments and messages |
| **Clan leader** | Verified leader of one clan | Edit their own clan's page and members; changes need review, so they cannot self-publish |
| **Contributor** | Any registered member | No admin access; submits content from the website or the app |

The final role set, and which permissions each role holds, is agreed in discovery. Custom roles can be added.

## 5.3 What can be managed

The demonstrator manages **30 content types** through a single, consistent engine, plus bespoke screens for the tasks that need more. They fall into six groups.

| Group | Managed items |
|---|---|
| **Culture and language** | Dictionary, proverbs, lessons, clans (with bulk add and CSV import), districts, notable Langos, history chapters, timeline, glossary, heritage items, species, 360 degree tours, leaders |
| **Content and media** | News posts and categories, events, programmes, media (photo, video, audio, podcast), documents, partners |
| **Community and moderation** | Contributions, word and proverb suggestions, comments, messages, applications, professionals, subscribers |
| **Finance** | Donations (confirm or fail, CSV export), programme progress, transparency figures |
| **Assistant** | Knowledge base entries, chat log, flagged answers for review |
| **System** | Users and roles, site settings and branding, translations, notifications, audit log, database backup, data export |

![browser|Figure 5.2: Managing the dictionary: filters, status, verification and bulk import.](shots/admin-dictionary.jpg)

## 5.4 The moderation workspace

This is the screen that protects the Foundation's credibility. Every contribution arrives with its type, description, location, media, clan and contributor, and the reviewer can:

1. **Open and read** it with its photographs, audio and map pin.
2. **Add a review note** that the contributor sees.
3. **Edit** the text, for example to correct spelling.
4. **Approve, publish, return for changes or reject.** Each action notifies the contributor in the app and on the website, and sends a push notification if allowed.
5. **Award points** for good contributions.
6. **Add to the archive**, which turns an approved contribution into a permanent record (a dictionary entry, a heritage site, a document and so on).

Chapter 7 describes the full lifecycle and the review tiers.

![browser|Figure 5.3: Reviewing a contribution with its media, location, note field and status actions.](shots/admin-contribution-review.jpg)

## 5.5 Translations and the Leb Lango interface

The **Translations** screen lists every phrase of the website and the app interface. On first run, the app registers its own phrases so they appear ready to translate. The Foundation's language experts enter the Leb Lango wording; anything not yet translated shows in English. **Nothing is machine-translated or invented.** Content fields such as news and history also have a Leb Lango version beside the English one.

![browser|Figure 5.4: Translations: each English phrase beside the field for its Leb Lango wording.](shots/admin-translations.jpg)

## 5.6 Reporting, export and safety

- **Dashboards** with counts of contributions, words, members, registrations, donations and applications.
- **Exports** to CSV and JSON for the main lists, and a full **database backup** (SQL) on demand.
- **Audit log** of administrative actions with the user, time and item.
- **Version history and restore** on content, so a mistaken edit is never permanent.
- **Forced password change** at first sign-in; login throttling and rate limits on sensitive actions.

## 5.7 What the Foundation controls without Kakebe

After handover, Foundation staff can independently: publish and edit any content; translate the interface; review and approve contributions; add and import clans, dictionary entries and proverbs; manage events, tickets and registrations; confirm donations; change the logo, photographs and contact details; create users and assign roles; export data; and download backups. Kakebe is needed for new features, security updates and infrastructure, which is exactly what the support plans in Chapter 14 cover.
