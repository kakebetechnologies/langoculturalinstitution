# Communities: one app for every tribe

The platform serves one community at a time and can hold many: Lango is the original, Acholi the second, and other tribes are added in the admin
panel. A person chooses a community when they sign in or create an account, and from then on everything is that community's own: clans (or
whatever the community calls them), districts, notable people, history, heritage, the dictionary, proverbs, events, news, radio, podcast, quizzes, the
Bible, the daily word and the weekly challenge. A member of Acholi has the same app as a member of Lango, filled with Acholi content. The only parts
that work across communities are the **translator** and **compare words**.

## Switching a community on or off

Admin, System, **Communities**. Each community has a status: **On** (people can choose it on the app's welcome and sign-in screens and on the
website) or **Off** (hidden; its content is kept). At least one community must stay on. A community that is switched off disappears from the app's
list the next time the app starts; a member of it falls back to the first community that is on. Acholi is installed switched **off**.

**Add a community** (same screen): its name, its language, what its people are called, what it calls a clan (Acholi: chiefdom) and a clan's leader
(Awitong, Rwot ...), where it is and its main city. Adding creates the community's own content tables and leaves it switched off. Fill it in with the
admin panel (use the **Editing** switch in the sidebar to choose whose content you are editing; site settings for a community are saved for it), then
switch it on.

## How it works

* Each community has its own copy of the content tables. Lango's are the original tables (`clans`, `districts`, `dictionary`, ...); every other
  community's carry a prefix (Acholi `ac_`, a new one `x<id>_`). The list is `COMMUNITY_TABLES` in `app/lib/db.php`; the communities themselves are
  the `communities` table.
* One current community per request. `q()` in `app/lib/db.php` renames those tables in the SQL, so no page, API route or admin screen has to know.
  Lango's SQL is never touched. Users, settings, contributions, donations and notifications are shared; `users.culture` and `contributions.culture` say
  which community a member or a contribution belongs to.
* Who decides the community: a signed-in member's own; otherwise the request (`X-App-Community` header or `?community=` for the API, a session and cookie
  for the website); otherwise the default (Lango). Only communities that are on are accepted, except in the admin panel.
* A member's clan belongs to one community. Switching keeps the clan details of the one they leave (`users.culture_state`) and restores them.
* Wording: for another community the website and app swap the names and clan words (Lango to Acholi, clan to chiefdom, Awitong to Rwot) and rewrite the
  few phrases that state a Lango fact, using the community's own region and capital (`community_words()` in `app/lib/community_api.php`,
  `communityWords()` in `app/lib/community.jsx`). The Lango emblem and the opening photographs are Lango's and are not shown for another community; the
  photo strip on the app's Home and the Leb Lango interface wording are Lango's too, until a community has its own.
* The database upgrade (`database/communities.php`, run by `lci_upgrade`) creates the `communities` table, creates and keeps in step each community's
  tables, and loads the Acholi content from `database/content/acholi.php` into empty tables.

## Translator and compare (the parts that work across communities)

* `POST /api/v1/translate` takes `from` and `to`, each `en` or a community code. English to or from a community's language uses that community's
  translator. Between two communities' languages the word's English meanings are looked up in the other dictionary, so the result is a **likely
  equivalent** (`likely: true`), never a checked one. Words and set phrases only; sentences are still to come. The app's Translate screen and the
  website's translator have a From and a To choice.
* `GET /api/v1/compare?q=`, the app's *Compare words* screen and the website's *Language, Compare* page show the dictionaries side by side, through
  English. A table of expert-checked links (and an admin screen for it) is the next step.

## The Acholi dictionary (read before publishing)

The Acholi dictionary data (`acholi-data/`, not in git) is an OCR scan of **Blackings, *Acholi English - English Acholi Dictionary* (LINCOM, 2009)**,
a book under copyright ("All rights reserved"), and it is unchecked scanning output (the data's README lists the known faults).

* `php database/import-acholi-dictionary.php <acholi_dictionary.json>` loads it as **drafts**: nothing is shown anywhere.
* `--publish` shows it publicly. Only do that once the publisher's (or the author's) written permission is in hand, or after replacing the data with a
  source you may publish. `--replace` removes the earlier import first.
* Entries are stored unchecked (`verified = 0`) with the source "Blackings 2009 (scanned, unchecked)". The daily word and the weekly challenge use only
  published entries, so they stay empty for Acholi until its dictionary is published.
* The folder is in `.gitignore`: never commit or deploy it. The live database only gets it if you run the import there.

## Acholi content that is included

Text only, copied from the Ker Kwaro Acholi project (`C:\xampp\htdocs\acholi`), whose sources are kerkwaroacholi.org and published references: nine
districts and cities, five chiefdoms, six heritage entries, ten notable Acholi, eight history chapters with a timeline and glossary, and nineteen
answers for the assistant. No photographs or logo (usage rights are still to be confirmed with the Ker Kwaro Acholi). The institution's own news,
programmes and leadership rows are left out.
