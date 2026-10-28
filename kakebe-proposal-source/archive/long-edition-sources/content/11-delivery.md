# 11. Delivery Approach and Project Plan

*How we turn this proposal into a launched platform: four clear stages, short build cycles, a staging site the Foundation can review at any time, and fixed gates before anything goes public.*

## 11.1 Our approach

Every Kakebe engagement, large or small, moves through the same four stages. For the Foundation they unfold as follows.

![fig|Figure 11.1: Kakebe's four-stage process applied to the Foundation's platform.](figures/fig-process.svg)

| Stage | What we do | What the Foundation sees |
|---|---|---|
| **1. Discover** | Listen first. Workshops with leadership, language experts, historians, clan leaders and youth; content audit; confirm scope, roles and content rules | A signed-off scope, content inventory, role matrix and project plan |
| **2. Design and build** | Brand and interface design; build in two-week cycles with a demonstration at the end of each; content structure and import | A staging site that grows every two weeks; short review sessions |
| **3. Test and launch** | Functional, device, accessibility, performance and security testing; pilot with a small group; training; store submissions; go-live | A pilot report, trained staff, a launch plan and a live platform |
| **4. Support and grow** | Hypercare for 90 days, then the chosen support plan; quarterly review and roadmap | Monitoring, fixes, improvements and a growing archive |

## 11.2 Working practices

- **Two-week cycles.** Each cycle ends with a demonstration on the staging site, and the Foundation's feedback goes straight into the next cycle.
- **A staging site from week 3.** The Foundation can log in and look at any time, rather than waiting for a "big reveal".
- **Weekly check-in.** A 30-minute project call, with a one-page status report: done, next, risks, decisions needed.
- **Monthly steering meeting.** Leadership reviews progress, budget, risks and priorities.
- **One backlog.** Every feature, bug and request is listed, visible and prioritised, so nothing is lost and scope changes are deliberate.
- **Definition of done.** A feature is done when it is built, tested on real devices, documented and accepted by the Foundation's coordinator.

## 11.3 Phases, deliverables and gates

| Phase | Weeks | Main activities | Deliverables | Gate to move on |
|---|---|---|---|---|
| **P0. Discover and align** | 1 to 2 | Kick-off, workshops, content audit, stakeholder interviews, review of the demonstrator, scope and role confirmation | Signed scope, content inventory, role matrix, risk register, detailed plan | Foundation signs off scope and plan |
| **P1. Brand, design and foundation** | 3 to 8 | Brand and interface design, design system, site structure, content model, environments, hosting, admin roles, website shell | Approved designs, staging site with navigation, admin with roles, hosting ready | Design approval |
| **P2. Culture and language modules** | 5 to 12 | History, districts, clans, notable Langos, heritage, biodiversity, library, Language Hub (dictionary, translator, proverbs, lessons, greetings), Cultural Assistant, bilingual interface | Working modules with Foundation content loaded where available | Module demonstrations accepted |
| **P3. Community engine and API** | 9 to 13 | Contribution engine, review workflow, accounts, points and badges, notifications, API hardening, privacy and consent flows | Moderated contribution pipeline on the website, API v1 documented | Workflow walk-through accepted |
| **P4. Mobile app** | 9 to 16 | App design, build for all screens, offline dictionary and outbox, push notifications, web app, store accounts | Test builds on Android and iOS, web app on staging | App demonstration accepted |
| **P5. Content, testing and pilot** | 15 to 18 | Content loading, user acceptance testing, accessibility and performance checks, security review, pilot with 20 to 40 users from different districts | Test reports, security report, pilot report, defect list closed | Foundation signs the acceptance certificate |
| **P6. Launch** | 19 to 20 | Staff and moderator training, store submissions and release, production go-live, launch communications | Live website, live app, trained team, launch pack | Go-live approval |
| **P7. Hypercare** | 90 days from go-live | Daily monitoring, rapid fixes, usage review, support hand-over | Hypercare report and support plan | End of hypercare review |

The plan for **Option A** (website and admin only) has the same shape without P4 and takes about 14 weeks. These durations are indicative and are confirmed at the end of P0, once the content inventory and the Foundation's availability for reviews are known.

![fig|Figure 11.2: Indicative project timeline for Option B: about 20 weeks to launch, then 90 days of hypercare.](figures/fig-timeline.svg)

## 11.4 Content migration and digitisation

The platform is only as valuable as the content it holds. We plan content as a work stream, not an afterthought.

| Step | What happens | Who |
|---|---|---|
| **Audit** | List existing content: documents, photographs, recordings, clan registers, dictionaries, histories; note owner, format, language and sensitivity | Kakebe with Foundation coordinator |
| **Prioritise** | Decide what must be live on launch day, what follows, and what stays restricted | Foundation |
| **Prepare** | Templates, CSV formats for clans, dictionary and proverbs; style guide for Leb Lango entries | Kakebe |
| **Load** | Bulk import (CSV) for clans, dictionary entries and proverbs; manual entry for narrative content; scanning and photography as needed | Kakebe, Foundation staff, interns under supervision |
| **Verify** | Language experts and historians review; items marked *verified* when confirmed | Foundation panels |
| **Maintain** | Ongoing contribution and review through the platform | Foundation moderators |

The demonstrator ships with a starter structure and a starter dictionary marked *unverified*; the Foundation's own verified content always takes precedence and is never overwritten by updates.

## 11.5 Quality assurance

| Test type | What we check |
|---|---|
| **Functional** | Every feature against its acceptance criteria |
| **Device and browser** | A range of low-, mid- and high-end Android phones, iPhones, and current browsers |
| **Offline and low connectivity** | Dictionary download, queued contributions, recovery after dropped connections |
| **Accessibility** | Contrast, keyboard navigation, labels, large text |
| **Performance** | Page weight and load time on a throttled 4G connection; app start time |
| **Security** | Dependency and configuration audit; penetration test against the OWASP Top 10 |
| **Content and language** | Spelling, diacritics, bilingual consistency, broken links |
| **User acceptance** | Foundation staff and pilot users run real scenarios and record issues |

Defects are classified by severity: **critical** (platform unusable or data at risk), **high** (a main function fails), **medium** (a workaround exists) and **low** (cosmetic). No critical or high defects may remain open at go-live.

## 11.6 Launch plan

1. **Soft launch** to the pilot group of clan leaders, language experts, youth and diaspora members, with feedback collected in a simple form.
2. **Store release.** Android first on Google Play, then iOS on the App Store (review times vary), and the web app available from day one.
3. **Public launch** with a communications pack: press release, social media assets, short how-to videos in English and Leb Lango, posters and QR codes for events, and a launch event [TBC: date and venue].
4. **Early support.** Daily monitoring and a visible support contact during the first weeks.

## 11.7 Change control

Requests that go beyond the agreed scope are logged, estimated in effort and cost, and approved in writing by the Foundation's coordinator before work starts. Small changes within a cycle can be swapped for equal-sized items without extra cost. Nothing is built that the Foundation has not agreed to.
