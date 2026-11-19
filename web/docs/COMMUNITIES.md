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
* The **Bible** is each community's own and is never copied: its book names are in its own `bible_books` table and its text is read on Bible.com. The
  address is the setting `<code>.bible_link_full` (Acholi: `https://www.bible.com/versions/559-aco85-acoli-baibul`, the Acoli Baibul 1985 of the Bible
  Society of Uganda; the upgrade adds it and the 66 Acholi book names from `database/content/acholi_bible.php` when they are missing, and never overwrites an
  edit; a book name an editor empties is filled again from the file on the next upgrade, and if the file is missing the upgrade logs a warning). Settings starting with `bible_` do not fall back to Lango's, so a community without a link shows none instead of Lango's. Admin, Site settings,
  with **Editing** set to the community, edits them. The app and the website need no change for another tribe: add its edition link and book names.
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
  published entries the scan is surer of (grade 2 or 3), so a doubtful word (grade 1) is never taught as a question; they stay empty for Acholi until its
  dictionary is published.
* In the app and the API (`doubtful: true`, and a 7th item in `/dictionary/export`) a grade 1 entry that no reviewer has verified carries the tag
  "Doubtful, check with a speaker".
* The folder is in `.gitignore`: never commit or deploy it. The live database only gets it if you run the import there, or import the CSV below.

### Getting it onto another copy of the site (for example the live one), after permission

`php database/export-acholi-import.php` writes `acholi-data/acholi_dictionary_import.csv` (git ignores it) from this computer's cleaned Acholi dictionary.
In the other site's admin, set the sidebar **Editing** switch to Acholi, open **Language, Dictionary, Import CSV** and upload that file. The import keeps each
entry's short meanings (`gloss`) and scan grade, sets the plain-letter spelling that search uses, and **publishes the entries**, so upload it only when you mean to.
Acholi speakers can then work through the list: filter **Scan check** to "Doubtful", correct the entry (and its short meanings), tick **Verified**, and set the
scan check to "Both halves agree".

### How the importer cleans the scan

The book has two halves, and the importer cross-checks them (it never invents a word: everything stored is in the scan).

* The English-Acholi half writes Acholi as it is pronounced (tones, doubled long vowels: *mììn*, *wòòr*); the Acholi-English half writes the plain
  headword (*min*). Words of the first half are stored in the spelling of the second (tones removed, a doubled vowel written once, *ario* read as
  *aryo* where that is the headword).
* Each entry gets a `grade` (the `dictionary.grade` column; Lango's entries have none): **3** both halves give the word with the same English meaning,
  **2** one half gives it and its spelling is a headword of the Acholi-English half, **1** the spelling cannot be matched (may be a scanning error)
  or the scan flags the entry, or the entry looks like two merged entries (a very long run-on sense).
* Each entry has a `gloss`, its short English meanings between pipes (`|water|`), which is what the translator, the comparison and the dictionary
  search match exactly. A long definition ("short form of the first person pronoun") therefore never matches an English word that merely occurs in it.
* An entry that the other half already gives is stored once. An entry of one half is not shown as a second row.
* Left out as debris: English inflections ("abandoning"), senses that are a stray letter or carry cross-reference numbers (and what follows such a
  number, which belongs to the next entry), words with capitals or digits, spellings with letters Acholi does not use (f, h, q, v, x, z) that the other
  half does not have, phrases with the book's own labels ("noun", "the suffix -e"), and example lines that are a bracketed pronunciation or a label.
  A headword the scan repeats in part ("dwe dw") is read as the word ("dwe"). The 97 entries the scan flagged for a pronunciation that does not match
  are kept at grade 1; the few without a translation are dropped.

### How lookups use it

* Acholi to English (`community_translator()` in `app/lib/content.php`): up to three meanings, the surest first (*dano*: human being; person; human).
* English to Acholi: the word or words with the best score (grade, and whether the meaning is the entry's first), at most three, so two equally sure
  words both show (*father*: wor; won) and a weaker one does not follow.
* Between Lango and Acholi (`translate_between()` in `app/lib/community_api.php`): the word's English meanings, the first ones weighing most, are looked
  up in the other dictionary; the words that carry the main meanings are the answer (`likely: true`), the rest are `candidates`, each with
  `confirmed` (true when both halves agree; null for Lango's entries, which have no grade).
* Search (website, app API): the word itself, the exact English meaning, the confirmed entries, then the closest spelling. Lango's ordering is unchanged.

What is still uncertain: it is OCR of a printed book that the scan README itself calls unchecked. Grade 3 is solid; grade 2 is probably right; grade 1
(about one in seven entries) needs a reader. Words with several equally sure entries (*big*: dwong, min, cogi) cannot be told apart from the scan.
Some basic words are missing or doubtful because the scan is: there is no *hello*; *father* comes out as *wor* (the scan's reading) and *won*.

## Acholi content that is included

Text only, copied from the Ker Kwaro Acholi project (`C:\xampp\htdocs\acholi`), whose sources are kerkwaroacholi.org and published references: nine
districts and cities, five chiefdoms, six heritage entries, ten notable Acholi, eight history chapters with a timeline and glossary, and nineteen
answers for the assistant. No photographs or logo (usage rights are still to be confirmed with the Ker Kwaro Acholi). The institution's own news,
programmes and leadership rows are left out.
