# 8. Architecture, Security and Data Protection

*Plain, proven technology on affordable hosting, with security and privacy designed in and a clear route to grow.*

## 8.1 Architecture at a glance

The platform is a classic, robust three-layer design. Everyone talks to one **JSON API**; the API reads and writes one **database** and one **media store**; and a small set of **services** handle email, SMS, push notifications, payments and maps.

![fig|Figure 8.1: Platform architecture. Three surfaces share one API, one database and one media store. Services are connected behind the API.](figures/fig-architecture.svg)

## 8.2 Technology choices

| Layer | Choice | Why |
|---|---|---|
| **Website and admin** | PHP 8.2, MySQL or MariaDB, no framework and no build step | Runs on affordable shared, VPS or cloud hosting; easy for local developers to maintain; the same approach as several of our production systems |
| **API** | Stateless JSON API (`/api/v1`) with bearer tokens, paging, rate limits and documented errors | Any future channel can use it; no sessions or cookies to manage |
| **Mobile app** | Expo SDK with React Native, Expo Router, one JavaScript codebase | One codebase for Android, iOS and the web app; fast iteration; over-the-air updates |
| **Web app (PWA)** | Built from the same app code and served from the website | No store needed; installable from a link |
| **Push notifications** | Expo Notifications over Firebase Cloud Messaging (Android) and Apple Push (iOS) | Free to start; reliable |
| **Media** | Files stored on the server with image optimisation; optional move to cloud storage | Simple now; scalable later |
| **Maps** | Interactive maps for districts, heritage sites and sightings | Visual exploration of the sub-region |
| **Email and SMS** | SMTP email; SMS through a local gateway such as Africa's Talking | Receipts, confirmations and alerts; Kakebe already integrates both in other products |
| **Payments (optional upgrade)** | ioTec Pay for MTN and Airtel mobile money and cards; Pesapal as an alternative | Both are in production in Kakebe systems (Chapter 10) |

Kakebe uses this stack, or its Laravel counterpart, across its own systems, including BetterLife International, iRecover, ObiFunds, KPOS, SignConnect Uganda and FaithFellows. The technology is not experimental, and the Foundation will not be dependent on a single developer to keep it running.

## 8.3 Hosting and environments

| Option | Description | Best for |
|---|---|---|
| **A. Managed by Kakebe** | Kakebe provisions and manages hosting on a reliable provider, with monitoring, backups and security updates under a support plan | Foundations that want to focus on content, not servers (recommended) |
| **B. Foundation-owned hosting** | The Foundation holds the hosting account; Kakebe deploys and maintains it | Foundations that want direct control of infrastructure |
| **C. Shared hosting (Option A scope only)** | A shared hosting plan suitable for the website and admin alone | Budget-conscious launch, with the option to upgrade |

Whichever option is chosen, we set up a **staging environment** for testing and review and a **production environment** for the public. HTTPS is mandatory (it is also required by Android for the app). The domain, hosting account and store accounts are registered in the Foundation's name. [TBC: preferred hosting location and domain name, for example a .ug or .org address.]

## 8.4 Security

We combine what the platform already does with a pre-launch review.

| Control | Status | Detail |
|---|---|---|
| **Password storage** | In the demonstrator | Passwords are hashed with a modern one-way algorithm; never stored in readable form |
| **Token security** | In the demonstrator | API tokens are random, and only a SHA-256 hash is stored, so a database leak does not expose live tokens |
| **No session cookies on the API** | In the demonstrator | The API is stateless, so cookie-based attacks do not apply to it |
| **Role-based access** | In the demonstrator | Six roles; clan leaders limited to their own clan; contributors have no admin access |
| **Rate limiting** | In the demonstrator | Sign-in, registration, uploads, translator and assistant requests are rate limited |
| **Duplicate-safe writes** | In the demonstrator | Uploads carry a unique reference so retries cannot create duplicates |
| **Forced first-login password change** | In the demonstrator | The first administrator must choose a new password |
| **Audit log and version history** | In the demonstrator | Who did what and when; mistaken edits can be restored |
| **Secure token storage on the phone** | In the demonstrator | Keychain on iOS, Keystore on Android |
| **Account deletion** | In the demonstrator | Members can delete their own account and remove their data link |
| **Input handling and file uploads** | Reviewed at hardening | Validation of every field, restricted file types and sizes |
| **CSRF protection on forms** | In the demonstrator | Forms carry a per-session token |
| **HTTPS and security headers** | Added at launch | Enforced on production |
| **Dependency and configuration audit** | Added at launch | Review of libraries, server settings and secrets |
| **Penetration test** | Added before launch | Our cybersecurity service tests the site and API against common attack types (the OWASP Top 10) |
| **Backups** | Added at launch | Section 8.7 |

## 8.5 Data protection and privacy

The platform will hold personal data about members and contributors: names, contact details, clan, location and recordings. We treat that as a duty of care and align the design with the **Uganda Data Protection and Privacy Act, 2019**.

| Requirement | How we address it |
|---|---|
| **Lawful basis and consent** | Clear, plain-language consent at registration and on contribution forms, with a privacy notice in English and Leb Lango |
| **Data minimisation** | Only the fields needed are collected; phone, country and clan are optional where possible |
| **Access, correction and deletion** | Members can view and update their profile and delete their account; administrators can export or erase a person's data on request |
| **Purpose limitation** | Personal data is used for running the platform and the Foundation's work, not sold or shared with third parties |
| **Security safeguards** | Section 8.4; encrypted connections; restricted admin access |
| **Retention** | A retention schedule agreed with the Foundation, for example for unconfirmed accounts and rejected submissions |
| **Registration and accountability** | We advise the Foundation to register with the Personal Data Protection Office and to nominate a data protection focal person [TBC: confirm with the Foundation's legal adviser] |
| **Children** | Clear rules on photographs and recordings of children, and on the age of sign-up |
| **Cross-border hosting** | The hosting location is chosen and disclosed deliberately, with the lawful basis for any transfer |

The demonstrator's Privacy and Terms pages are drafts for legal review, and we will work with the Foundation's legal adviser to finalise them before launch.

## 8.6 Performance and reliability

- **Light pages and images,** with sensible caching and compressed media, so pages load on mid-range phones on a 4G connection.
- **A lightweight reading mode** for slow connections.
- **App caching:** screens show saved data instantly and refresh in the background.
- **Monitoring** of uptime and errors under the support plan, with alerts to the Kakebe support team.
- **Capacity.** The platform comfortably serves the Foundation's expected audience on a modest server. Growth beyond that is handled by moving to a larger plan, not by rebuilding.

## 8.7 Backups and recovery

| Item | Approach |
|---|---|
| **Database** | Automated daily backups, kept off the main server, plus an on-demand SQL backup from the admin panel |
| **Uploads** | Regular copies of photographs, audio and documents to separate storage |
| **Restore test** | A restore drill before launch and then periodically |
| **Target recovery** | [TBC: proposed recovery point and recovery time targets, for example no more than 24 hours of data and one working day to restore] |

## 8.8 Integrations

| Integration | Purpose | Phase |
|---|---|---|
| Email (SMTP) | Event tickets, contribution status, receipts, newsletters | Launch |
| SMS gateway | Alerts, one-time codes, event reminders | Optional, post-launch |
| Mobile money and cards | Donations, event tickets, sponsorships | Optional upgrade |
| Push notifications | Contribution decisions, events, word of the day | Launch (decisions), later (campaigns) |
| Maps | Districts, heritage sites, sightings | Launch |
| Analytics | Privacy-conscious usage insight | Launch |
| Open data API | Researchers, schools and partners | Launch |

## 8.9 Scalability and the road ahead

The platform is **modular**. A new managed content type is added by registering one entry in the admin, and the API and app follow. The same backend can later feed an SMS or USSD service, a community forum, kiosks in schools and museums, partner portals, radio programmes and more, without changing the foundation.

## 8.10 Accessibility and inclusion

- WCAG 2.1 AA as the target for the website: sufficient contrast, keyboard navigation, labelled forms and alternative text.
- Large touch targets and readable type in the app.
- Plain language, icons with text labels, and Leb Lango alongside English.
- Offline and lite modes for people with limited data.
- Assisted contribution: a youth ambassador can submit on an elder's behalf, with the elder credited and consent recorded.
