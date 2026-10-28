# 7. Community Contribution and Content Governance

*How knowledge enters the archive, who decides what is true, and how the platform protects the Foundation's credibility and its people.*

## 7.1 The principle: the community decides, the platform enforces

A digital archive is only as trusted as its weakest entry. The Foundation's reputation therefore depends on one rule that is built into the platform and into this proposal:

> **The platform never invents culture.** Clans, proverbs, translations, biographies and history come only from the Foundation, from published sources that are shown on the page, or from community contributions that an authorised reviewer has approved. Where something is missing, the platform shows it as empty, not made up.

Everything in this chapter follows from that rule.

## 7.2 The contribution lifecycle

![fig|Figure 7.1: The lifecycle of a contribution, from capture on the phone or website to a published, attributed archive record.](figures/fig-workflow.svg)

| Step | What happens | Who acts |
|---|---|---|
| **1. Capture** | The contributor fills one of seven guided forms on the website or the app, adding location, photos, audio or files; offline if needed | Contributor |
| **2. Submit** | The submission receives a **tracking code** and a status of *Pending*. Duplicates are prevented, and rate limits stop floods | System |
| **3. Alert** | A bell notification and a new row in the review queue tell the moderators something is waiting | System |
| **4. Review** | A moderator reads it, adds a note, corrects spelling, and decides: approve, return for changes or reject | Moderator |
| **5. Specialist check** | Where the content needs it (a clan's history, a sacred practice, a contested fact), it is routed to the clan leader or the Foundation's elders' panel before publication | Clan leader, elders' panel |
| **6. Publish and archive** | The record is published or added to the archive as a dictionary entry, heritage site, document and so on, with its contributor and reviewer recorded | Moderator, editor |
| **7. Notify and recognise** | The contributor is notified in-app and by push, and receives points and badges | System |

## 7.3 Status model

| Status | Meaning | Visible to the contributor |
|---|---|---|
| **Pending** | Received and waiting for review | Yes, with the tracking code |
| **Under review** | A reviewer has opened it | Yes |
| **Returned** | The reviewer needs more information or corrections; the note explains what | Yes, with the note |
| **Approved** | Accepted by the reviewer | Yes |
| **Published** | Live on the website and the app | Yes |
| **Rejected** | Not accepted, with a reason | Yes |

Anyone can check a submission's status with its tracking code, without logging in.

## 7.4 Review tiers and roles

The demonstrator already separates the roles that matter most:

- **Moderators** (language experts and community managers) review contributions, word suggestions and comments.
- **Clan leaders** can edit only their own clan and **cannot publish their own changes**. Every change they make is reviewed, so the clan's voice is respected without bypassing quality control.
- **Editors** publish Foundation content such as news, events and programmes.
- **Admins** manage users, roles and settings.

For this project we recommend adding a **Cultural Review Panel**, a small group of elders and historians nominated by the Foundation, who act as the final reviewer for sensitive content. [TBC: the Foundation to confirm panel composition and the categories that require panel sign-off.]

## 7.5 Trust labels and sources

Every record tells the reader how far to trust it.

| Label | Meaning |
|---|---|
| **Verified** | Checked and approved by the Foundation's experts |
| **Community** | Contributed by a member and approved by a moderator, not yet verified by the panel |
| **Unverified** | Loaded from an external or starter source and awaiting review (for example the starter dictionary) |
| **Source cited** | History, districts and people show the published sources they come from |

## 7.6 Cultural protocols: consent, attribution and restricted knowledge

Some knowledge is not meant for everyone, and some belongs to a family, a clan or a ceremony. We propose that the platform support the following, to be agreed with the Foundation at discovery.

| Protocol | How the platform supports it |
|---|---|
| **Informed consent** | Recording and photography forms record that the speaker or owner has agreed to the use of their words and image |
| **Attribution** | Contributors and speakers are credited as they wish: by name, by clan, or anonymously |
| **Restricted knowledge** | A record can be marked *restricted* so that it is held in the archive but shown only to authorised roles, or not shown at all |
| **Right of correction and removal** | Every page will offer a route to ask for a correction or withdrawal, handled in the review queue |
| **Children's content** | Clear rules on photographs and recordings of children, with guardian consent |
| **Neutral on contested matters** | Leadership titles and officeholders are published exactly as the Foundation confirms, with an approval step; the platform takes no position on disputes |

## 7.7 Ownership, licensing and attribution

- **The Foundation owns the platform's content and data.** Contributors keep authorship of what they submit and grant the Foundation a licence to publish and preserve it, which the contribution form states plainly.
- **Public reuse is a choice.** The Foundation decides which data is released as open data, under which licence, on the open data page. The default is to share nothing without approval.
- **Sources are respected.** Material from published sources is cited, and permission is sought for photographs, recordings and documents before they are published.

## 7.8 Keeping out spam and abuse

- **Rate limits** on sign-in, registration, uploads, the translator and the assistant.
- **Duplicate checks** on word suggestions and idempotent uploads.
- **Moderation before publication** for contributions, comments and directory profiles.
- **Account controls:** administrators can deactivate abusive accounts, and members can delete their own.
- **Report and correction routes:** a visible way on public pages to report content or request a correction, feeding the moderation queue (added in this engagement where not already present).

## 7.9 The Cultural Assistant

The assistant answers visitors' questions, for example *Who is the leader of this clan?* or *What is known about this district?*, **only from the register and the reviewed archive**, and it shows its sources. If it cannot answer, it says so and logs the question as a flagged item so that the Foundation can see what people are asking and fill the gap. It is deliberately not an open-ended chatbot: it cannot make things up because it has nothing to draw on except approved content.
