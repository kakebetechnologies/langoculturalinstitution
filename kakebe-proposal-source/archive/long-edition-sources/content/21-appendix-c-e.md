# Appendix C. Content Integrity Policy

*The rules that decide what the platform may publish, so that a visitor can trust every page.*

## C.1 The rule

**Nothing cultural is invented.** Every piece of content on the platform comes from one of four sources, and each is labelled.

| Source | How it is handled | Label |
|---|---|---|
| **The Foundation** | Supplied or approved by the Foundation's authorities and experts | Verified |
| **Published sources** | Taken from named published works (for example official statistics, academic and press articles, encyclopaedic sources); the source is shown on the page | Source cited |
| **Community contributions** | Submitted by members, reviewed by a moderator, approved or published | Community |
| **Starter data** | Loaded to give the platform a useful start (for example the starter dictionary); flagged until experts review it | Unverified |

## C.2 What the platform will not do

- It will not generate clan names, proverbs, translations, biographies or histories that no person or source has supplied.
- It will not machine-translate the interface or content and present it as expert translation.
- It will not publish a contested claim, or take a side on a dispute.
- It will not publish photographs, recordings or documents without confirmed permission.
- It will not display personal data of members beyond what they have chosen to share.

## C.3 What the demonstrator shows today

- **Clans.** Only clans that could be sourced are pre-loaded; the register is designed to receive the Foundation's full list by bulk add or CSV import, and new clans enter as drafts for verification.
- **Proverbs.** None are pre-loaded; the public suggestion form feeds the review queue.
- **History, districts and notable people.** Compiled from published sources, with the sources listed on the page, and flagged for the Foundation's historians to review before launch. District populations are census figures from the Uganda Bureau of Statistics.
- **Dictionary.** A starter dictionary of 2,829 entries, flagged *unverified* until experts review it. The provenance and licence of the starter data are confirmed with the Foundation during discovery.
- **Leadership.** Cabinet and elder entries are placeholders until the Foundation supplies and approves them.
- **Sample items.** News, events and programme dates in the demonstrator are placeholders to be replaced before launch.

## C.4 Review before launch

Before the public launch, the Foundation's panels review: every history chapter and glossary entry; every district and notable-person profile; the dictionary entries chosen for launch; the leadership page; the privacy notice, terms and contributor licence; and every photograph and document, for permission.

# Appendix D. API Overview

*The interface that the website, the app and any future channel use. The complete reference is delivered with the project.*

| Group | Examples | Notes |
|---|---|---|
| **Accounts** | Register, sign in, sign out, profile, change password, delete account, my contributions, notifications, register a phone for push | Bearer token; only a hash of the token is stored |
| **Contributions** | Submit a contribution with media; check status by tracking code | Duplicate-safe through a unique reference; rate limited |
| **Content (read only)** | Home, clans, districts, people, history, heritage, species, library, news, events, programmes, transparency | Paged; English or Leb Lango on request |
| **Language** | Dictionary search; full dictionary export for offline use; proverbs; greetings; translator | The export is versioned and returns *unchanged* when nothing changed |
| **Assistant** | Ask a question | Answers only from reviewed content, with sources |
| **Engagement** | Event registration and ticket; donation pledge; professionals directory | Pledges are confirmed by the finance office |
| **Interface text** | Register phrases; fetch Leb Lango translations | Powers the Translations screen |
| **Health** | Ping | For monitoring |

**Conventions.** JSON in and out; success responses carry the data and, for lists, paging details; errors carry a message and field errors with the usual status codes; no sessions or cookies; rate limits answer with a standard status code; cross-origin access is allowed for the web app.

# Appendix E. Glossary

| Term | Meaning |
|---|---|
| **Admin control panel** | The password-protected workspace where the Foundation's staff manage content, review contributions and run the platform |
| **API (application programming interface)** | The set of requests the website and the app use to read and write data |
| **App (mobile app)** | The Lango App for Android and iOS |
| **Audit log** | A record of who did what, and when, in the admin |
| **Bearer token** | A secret code that proves who is signed in to the app, stored on the phone |
| **CMS (content management system)** | Software that lets non-technical people edit a website |
| **Contribution** | A piece of knowledge submitted by a member: a word, proverb, oral history, observation, document, heritage site or natural heritage record |
| **Demonstrator** | The working version of this platform that Kakebe has already built |
| **Hypercare** | Intensive support in the first period after launch |
| **Idempotent upload** | An upload that can be retried safely: the server stores it only once |
| **Leb Lango** | The Lango language |
| **Offline first** | Designed to work without an internet connection and to catch up when one is available |
| **Outbox** | The queue on the phone where contributions wait until they can be sent |
| **PWA (progressive web app)** | A website that can be installed on a phone like an app |
| **Push notification** | A message that appears on a phone even when the app is closed |
| **Staging** | A private copy of the site used for testing and review before changes go public |
| **Tracking code** | A short code that lets a contributor follow a submission's status |
| **Won Nyaci** | The title of the paramount traditional leader of the Lango |
