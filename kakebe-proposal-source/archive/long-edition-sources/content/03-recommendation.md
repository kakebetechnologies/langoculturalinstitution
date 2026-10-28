# 3. Our Recommendation: One Platform, Three Surfaces

*Why the website and the mobile app belong together, and how one shared platform gives the Foundation more reach for less cost.*

## 3.1 The recommendation in one paragraph

Build a single platform with **one backend and three surfaces**: a public website for reach, credibility and search; an admin control panel where the Foundation's staff run everything; and the Lango Mobile App for daily use, offline learning and field contribution. Deliver it in a way that the Foundation owns, can run with its own people, and can extend for years without rebuilding.

![fig|Figure 3.1: One platform, three surfaces. Every surface talks to the same JSON API, database and review queue.](figures/fig-platform.svg)

## 3.2 Why not the website alone, or the app alone?

A website and an app are good at different things. Choosing only one leaves a gap that the Foundation's community will feel.

| Need | Website | Mobile app |
|---|---|---|
| Be found on Google, shared in a link, cited by the media | **Strong.** Every page has its own address, search-engine markup and share preview | Weak. App content is not indexed in the same way |
| Read long histories and use large tables and maps | **Strong** on desktop and tablet | Good on a phone for short reading |
| Work with no internet | Limited (cached pages only) | **Strong.** Offline dictionary, queued contributions, saved tickets |
| Record a voice, a photo or a GPS point at the source | Possible, but clumsy in a browser | **Strong.** Native camera, microphone and location |
| Remind and re-engage people | Email only | **Strong.** Push notifications, word of the day |
| Serve people who do not install apps | **Strong.** Nothing to install | Via the installable web app (PWA) |
| Staff administration, reporting and moderation | **Strong.** A desktop workspace is the right tool | Not suitable |
| Reach older users and institutional partners on desktop | **Strong** | Not the primary channel |
| Reach youth and the diaspora on phones | Good | **Strong.** Installed, always at hand |

The conclusion is simple. **The website is the Foundation's public face and its control room; the app is its daily companion and its field recorder.** Together they cover every audience in Chapter 2.

## 3.3 One backend: what "shared" means in practice

- **One set of accounts.** A member signs in once, on the website or in the app, with the same email or phone number and password.
- **One content store.** The history chapter an editor publishes in the admin panel is the same chapter the app shows, in English and in Leb Lango.
- **One review queue.** Every contribution, word suggestion, proverb or directory profile, whichever surface it came from, lands in the same moderation workspace, with the same status model and the same notifications to the contributor.
- **One API.** The app uses the same documented interface that powers the open data page. Any future channel (a kiosk, an SMS service, a partner's portal) can use it too.
- **One place to fix things.** A correction made once is corrected everywhere.

## 3.4 Who uses which surface

| Audience | Website | Admin panel | Mobile app |
|---|:---:|:---:|:---:|
| Visitors, students, researchers, media | ● | | ○ |
| Members and contributors | ● | | ● |
| Diaspora | ● | | ● |
| Clan leaders | ● | ● (own clan only) | ○ |
| Moderators and language experts | ○ | ● | |
| Editors and communications staff | ○ | ● | |
| Administrators and leadership | ○ | ● | |
| Partners and donors | ● | | ○ |

● primary surface · ○ occasional use

## 3.5 The three packages

To give the Foundation a clear decision, we propose three packages. Prices are in Chapter 13; the scope is summarised here.

| Scope | **Option A: Website and admin** | **Option B: Complete platform** *(recommended)* | **Option C: Complete platform plus growth year** |
|---|---|---|---|
| Public website, bilingual | ● | ● | ● |
| Admin control panel and roles | ● | ● | ● |
| Language Hub (dictionary, translator, proverbs, lessons) | ● | ● | ● |
| Community contribution engine on the website | ● | ● | ● |
| **Lango Mobile App** (Android, iOS, web app) | | ● | ● |
| Offline dictionary and offline contributions | | ● | ● |
| Push notifications and member notifications | | ● | ● |
| Store publishing (Google Play, Apple App Store) | | ● | ● |
| Training and handover | ● | ● | ● |
| 90 days of hypercare | ● | ● | ● |
| Content digitisation support (supervised interns) | | | ● |
| 12 months of support and quarterly improvements | | | ● |
| Launch campaign and digital engagement programme | | | ● |

Option A is complete and valuable on its own, and every part of it is reused if the Foundation adds the app later. We recommend Option B because the incremental cost of the app is much lower when it is built alongside the platform than when it is added to a finished one, and because the app is where the community's contributions will come from.

## 3.6 Starting from a working demonstrator

The delivery plan in Chapter 11 begins with the demonstrator, not a blank page:

- **Risk is lower.** The hardest engineering problems (offline sync that cannot create duplicates, the moderation workflow, bilingual content, push notifications) are already solved and tested.
- **The Foundation can see before it signs.** Appendix B shows the demonstrator, and we can give the Foundation a live walk-through at the discovery workshop.
- **Time goes where it matters.** The first weeks of the project are spent on the Foundation's identity, structure and content, not on infrastructure.
- **Nothing is hidden.** The demonstrator shows exactly what is built and what is still to do (the roadmap items in Section 6.9), so expectations are clear from day one.

> **A note on the name.** The demonstrator was built under the working title *Lango Cultural Institution*. In this project every screen, logo, colour, text and app store listing will carry the Foundation's own name and identity.
