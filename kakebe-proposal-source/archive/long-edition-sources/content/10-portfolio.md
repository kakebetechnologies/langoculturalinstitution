# 10. Portfolio and References

*Platforms we have designed, built and operated, and exactly what each one proves about our ability to deliver the Foundation's website and app.*

## 10.1 How to read this chapter

A proposal is only as credible as the work behind it. This chapter presents the reference projects most relevant to the Foundation, grouped by the kind of capability they demonstrate, followed by a matrix that maps each Foundation requirement to the live systems where we have already delivered it. Where a system serves confidential or government users, we describe it in words and offer a private demonstration with the client's permission. [TBC: Kakebe to confirm which clients agree to be named as referees.]

| # | Reference | Type | What it proves for the Foundation |
|---|---|---|---|
| 1 | **Lango cultural platform** (demonstrator) | Website, admin, mobile app | The complete solution, already working |
| 2 | **A Better Life International** | Non-profit website, shop, admin | Foundation-style site with a full CMS and online payments |
| 3 | **Betty Amongi Foundation** | Foundation website, CMS, mentorship portal | A Lango-region foundation, content management and community programmes |
| 4 | **Pure Wage Foundation** | Foundation platform | [TBC] |
| 5 | **Civic Coded** | Civic technology platform | [TBC] |
| 6 | **OPM Schedules** | Government scheduling platform | Government-grade, multi-office, role-isolated administration |
| 7 | **iRecover** | Lost-documents recovery platform | Tracking codes, matching workflow, mobile-money payments |
| 8 | **BodaERP** | Rider registry and revenue ERP | Five-role system spanning riders to government |
| 9 | **FaithFellows** | Web platform and mobile app | Mobile app on a shared API, offline, multilingual |
| 10 | **Soila** | AI app for farmers | AI and mobile product for rural users |
| 11 | **Better Life Farmers Connect** | Farmers' application | [TBC] |
| 12 | **Lira City website** | Government website and CMS | Public institution CMS with citizen reporting |
| 13 | **SignConnect Uganda** | Sign-language learning app | Language learning with video, lessons and classes |
| 14 | **ObiFunds, ObiTickets, ChamaFunds** | Fundraising and ticketing | Mobile-money payments, QR tickets, payouts |
| 15 | **Kakebe Tech Camp platform** | Registration, payments, portal | Registration, receipts, tickets and a participant portal |
| 16 | **KPOS and Falcon ERP** | SaaS and operations ERP | Multi-tenant SaaS, multilingual, geotagged workflows |

## 10.2 Closest reference: the Lango cultural platform

**Type:** Public website, admin control panel, JSON API and mobile app · **Status:** working demonstrator

The platform described in this proposal exists today as a tested demonstrator: more than 30 website page types, 30 managed content types in the admin, about 50 app screens, a 32-table database and a documented API (Chapter 1 and Appendix B). It demonstrates offline-first contribution, a moderated review queue, a Leb Lango dictionary and translator that never invent content, and bilingual interface management by language experts.

**Why it matters.** It removes the single biggest risk in a project of this kind: discovering late that the idea is harder than expected.

## 10.3 Foundations and community organisations

### A Better Life International

*Website, e-commerce and admin dashboard · Non-profit working on climate action, youth empowerment and sustainable development · betterlifeint.org*

**What we built.** A full redesign of the organisation's website in a fresh green-and-blue nature theme, with real content from the live site, plus a new **BetterLife Farm** section selling honey, ghee and yoghurt. A complete admin dashboard lets staff manage programmes, farm products, team and board, testimonials, impact counters, projects, impact reports (uploaded PDF or link), a rich-text blog with categories and drafts, orders, messages and newsletter subscribers, and site settings, all without touching code. Customers add products to a cart and pay by card or mobile money through Pesapal's hosted checkout, with order confirmation, an admin alert and a paid receipt sent by email, plus printable invoices.

**Relevance.** The same pattern as the Foundation's site: a content-managed foundation website with a team page, programmes, impact figures, a blog, forms, newsletter, and online payments, edited by non-technical staff.

### Betty Amongi Foundation

*Foundation website, content management area and mentorship portal · Supports youth, women and entrepreneurs in the Lango sub-region and Northern Uganda through seed funding, mentorship and training*

**What we built.** A professional website covering the foundation's work, entrepreneurs, training, services, testimonials, blog and gallery, with a content management area for posts, pages, gallery, comments and messages. A separate **mentorship portal** has application and login flows and distinct areas for participants, mentors and administrators.

**Relevance.** A foundation serving the same Lango community, with the same needs: a public story, content management, a gallery, programmes and a structured way for the community to take part.

### Pure Wage Foundation

*[TBC: type of work, for example website, donation platform or CMS] · [TBC: URL]*

**What we built.** [TBC: two or three sentences on scope and features.]

**Relevance.** [TBC: for example, a foundation platform with donor and programme features.]

### Civic Coded

*[TBC: type of work, for example civic technology platform, community site or training programme] · [TBC: URL]*

**What we built.** [TBC: two or three sentences on scope and features.]

**Relevance.** [TBC: for example, civic engagement and community participation.]

## 10.4 Government, public-service and civic systems

### OPM Schedules (Office of the Prime Minister scheduling application)

*Multi-tenant scheduling platform for ministry offices of the Government of Uganda*

**What we built.** A multi-tenant platform in which each ministry office manages its weekly meetings and trips, completely isolated from every other office. Features include meetings and trips with date, time, venue, agenda, attendees and accompanying staff; automatic duration; a Monday-to-Friday weekly view with colour-coded warnings for upcoming items; clash flags that warn but never silently block; printer-friendly schedules; **branded email reminders** ahead of each meeting; and a one-click **weekly programme sent as a PDF** to every registered user in that office. A separate **Super Admin** area onboards new ministries, manages users across all offices and resets passwords. The reference office for the build was the Office of the Minister for Relief, Disaster Preparedness and Refugees.

**Relevance.** Government-grade role separation (office admin versus super admin), scheduled notifications, branded PDFs and multi-office data isolation, the same discipline the Foundation needs when clan leaders, editors and moderators each see only what they should.

### Lira City website

*Government website and CMS · A rebuild of the Lira City website*

**What we built.** A clean rebuild of the city's public site on PHP 8 and MySQL, with an administrator panel for news, events, pages (vision, mission, mandate, history, geography), downloads, tenders and notices, FAQs, departments, technical staff, an organogram, service categories and services, a messages inbox and subscribers with CSV export. Citizens can **report an issue and track it with a tracking code**; staff run a status workflow with a public update and internal notes.

**Relevance.** A public institution in Lira with a large content inventory, a citizen feedback loop and tracking codes, all of which appear in the Foundation's contribution engine.

### iRecover

*Lost-documents recovery platform · web, with station and admin dashboards · [TBC: public URL]*

**What we built.** A platform that helps people in Uganda recover lost official documents (National IDs, driving permits, passports, student IDs, academic documents, land titles, birth certificates and more) that have been found by partner radio stations, police posts and other collection points. A member of the public can **search** the found-documents database, **report** a lost document, get **matched**, pay a small recovery fee by **MTN or Airtel mobile money**, and collect the document from the station that holds it. Stations upload found documents and confirm hand-over; administrators approve matches and manage fees and alerts. A public **status tracker** and a **receipt with a pickup code** close the loop.

**Relevance.** A community-powered, multi-party workflow with partner organisations, tracking codes, matching, mobile-money payments and role-separated dashboards, much like the Foundation's contribution, review and archive pipeline.

### BodaERP

*Rider registry, fee collection and compliance platform for boda-boda transport · currently in development*

**What we built.** A platform in which **riders, stage chairpersons, city councils, government and platform administrators** each have their own workspace. Chairpersons register riders, collect payments and track defaulters and expiries; city councils monitor compliance, take enforcement actions and analyse revenue; government sees a national registry with comparisons and reports; super administrators manage cities, revenue splits, data quality, audit logs and backups. Riders see their ID card, payment history and renewal status. Mobile-money payments run through ioTec Pay, and anomaly scans flag suspicious data.

**Relevance.** Five-role access, digital ID cards, notifications, audit logs, data-quality controls and large-scale registries are close cousins of the Foundation's clan register and member records.

## 10.5 Mobile and AI products

### FaithFellows (web platform and mobile app)

*Multi-tenant church management and engagement platform · web, mobile app (iOS and Android) and PWA · by Kakebe Technologies*

**What we built.** A platform in which every church gets a website, sermons (audio and video, with offline listening), a Bible with notes, live streaming with audio-only mode, events with RSVPs, paid tickets and QR check-in, giving through mobile money and cards, a moderated prayer wall, small groups, pastoral care, and notifications by in-app, email, push, SMS and WhatsApp. The **Laravel** web platform and the **Expo / React Native** mobile app share one backend and one JSON API. The platform runs in **English, Luo, Luganda and Swahili**, installs as a progressive web app, and offers a lite mode for slow connections.

**Relevance.** This is the architecture of the Lango App in production: one backend serving a website and a mobile app, multilingual content including a Nilotic language, offline use, push notifications and community moderation.

### Soila

*AI app that helps farmers test soil and grow better · in development*

**What it is.** An AI-assisted mobile product for smallholder farmers, one of Kakebe's own products, currently in development.

**Relevance.** Experience with AI features and with designing for rural users on mobile.

### Better Life Farmers Connect

*[TBC: type of work, for example farmers' mobile application] · [TBC: URL or store link]*

**What we built.** [TBC: two or three sentences on scope and features.]

**Relevance.** [TBC: for example, a community mobile app for farmers, linked to A Better Life International.]

## 10.6 Language, learning and community knowledge

### SignConnect Uganda

*Mobile-first web app teaching Ugandan Sign Language to Deaf children and their families*

**What we built.** Short, segmented sign videos uploaded and tagged by an admin, with front and side views, slow motion and repeat, captions, and a learning path with stars, streaks and badges. Teachers create classes with a join code and assign lessons; parents manage child profiles and learn together. The admin has bulk upload, theme and lesson management, and search analytics including searches with no results. The design targets WCAG 2.1 AA, with large touch targets, high-contrast and larger-text options, and optional offline lessons.

**Relevance.** A language-learning product built around a community's own language and its teachers, with media-rich lessons, progress tracking and accessibility. It is the nearest cousin to the Foundation's Language Hub.

### NursesPro Academy

*E-learning platform for nursing and midwifery students in Uganda*

**What we built.** Roles for super admin, tutors and students; a curriculum of modules and notes; classes; subscription payments with access that expires; and real hashed-password sign-in.

**Relevance.** Structured learning content, role-based access and payments.

## 10.7 Payments, ticketing and community finance

### ObiFunds

*Crowdfunding platform · www.obifunds.com*

Campaigners raise money for personal, medical, education and community causes. Donors give by mobile money or bank. An admin team verifies campaigns and documents and releases funds. Payments in and out run through ioTec Pay, with PDF receipts, charts and transparent campaign pages.

**Relevance.** Donation flow, verification workflow, transparency, receipts and payouts, which are the building blocks of the Foundation's giving and sponsorship features.

### ObiTickets

*Event ticketing platform*

Organisers list events and sell tickets; buyers pay by MTN or Airtel mobile money or card and receive QR e-tickets instantly; organisers scan tickets at the gate; an admin team reviews events and releases payouts.

**Relevance.** Directly reusable for Foundation cultural festivals, with a gate scanner and instant e-tickets.

### ChamaFunds

*Community fundraising platform.* Fundraising campaigns with donation pages, Pesapal payments, downloadable receipts and withdrawals.

### Kakebe Tech Camp platform

*Registration, payments, receipts and participant portal*

Online registration with track selection, package pricing, pay-now or pay-later with part payments, mobile money and card through ioTec Pay, **PDF receipts** by email, a camp **ticket with photo and QR code**, a participant portal with one-time email codes, sponsor-a-child payments, and an admin control panel with finance, reminders, payments ledger and exports.

**Relevance.** Registration with payments, tickets, a portal and finance reporting, in production for a real event.

## 10.8 SaaS and operations systems

### KPOS (Kakebe Point of Sale)

Cloud-based, multi-tenant point of sale for Ugandan businesses, with MTN MoMo, Airtel Money, card and bank payments, inventory, purchasing, finance reports exportable to PDF, Excel and CSV, roles and audit log, REST API with keys and signed webhooks, daily backups, an installable web app, dark mode, and an interface in **English, Luganda and Kiswahili**.

### Falcon ERP

Export and trade operations for Falcon Beverages (U) Ltd: a four-role pipeline (merchant, supplier, client, driver) from request to delivery, with **geotagged checkpoints**, a goods-received note with photo and hand-drawn signature, and generated and verified documents.

**Relevance of both.** Multi-tenant SaaS, role-based workflows, offline-capable web apps, multilingual interfaces, PDF and spreadsheet reporting, and field data captured with GPS, photos and signatures.

## 10.9 Selected additional work

| Project | What it is |
|---|---|
| **Mwonya Streams** (mwonya.com) | A streaming platform for local entertainment and culture, preserving and promoting indigenous voices |
| **Kakebe Shop Online** (kakebeshop.com) | An online shop connecting Northern Ugandan businesses and customers |
| **Oroma TV** | News, shows and culture for Northern Uganda, with a content management admin, live streams and previous shows |
| **Innovate2Day** | An opportunity hub for young African innovators: verified grants, fellowships and calls, innovator profiles and an AI-assisted admin that writes and shares listings |
| **NUFA and the NUFA Awards** | Websites of the Northern Uganda Filmmakers Association and its annual awards: news, awards, partners, gallery, subscribers and an admin |
| **Mighty Rides** | Website, customer portal and staff workspace in Next.js with an accounting back office (Laravel) |
| **Kakebe Management System (KMS)** | Internal platform for people, programmes, projects, learning, finance, content, reporting and impact, with monitoring and evaluation |
| **Es Shemen International School** | School website for a Christ-centred international school in Lira City |
| **Kiryandongo High School** | School website for a mixed day community school in Bweyale |
| **H. Tubman Solutions** | Website for a CCTV, security and ICT solutions company |
| **Bellan Contractors and Suppliers** | Corporate website and company profile |
| **Nilotica Shea** | Website for a premium Ugandan shea butter brand |
| **The Big Five Heritage Collection** | Website for a national movement uniting conservation, leadership, tourism, culture and national pride |
| **Denise Ayebare** | Personal advocacy website for a climate activist and youth empowerment advocate |

[TBC: Kakebe to confirm the final list and any client permissions before this proposal is sent.]

## 10.10 Capability matrix: what the Foundation needs, and where we have already delivered it

| Foundation requirement | Delivered in |
|---|---|
| Content-managed website with a no-code admin | A Better Life International, Betty Amongi Foundation, Lira City website, NUFA, Es Shemen, Innovate2Day, Lango platform |
| Multi-role access and approvals | OPM Schedules, BodaERP, iRecover, FaithFellows, Lira City website, Lango platform |
| Mobile app (Android and iOS) sharing one API with the web | FaithFellows, Lango App |
| Installable web app and offline use | FaithFellows, SignConnect Uganda, KPOS, Lango App |
| Mobile money and card payments | ObiFunds, ObiTickets, iRecover, BodaERP, KPOS, Kakebe Tech Camp, A Better Life International (Pesapal), ChamaFunds |
| Events and ticketing (with QR check-in) | ObiTickets, Kakebe Tech Camp, FaithFellows; events with printable tickets in the Lango platform |
| Public reporting with tracking codes | Lira City website, iRecover, Lango platform |
| Multilingual interface including local languages | FaithFellows (Luo, Luganda, Swahili), KPOS (Luganda, Kiswahili), Lango platform (Leb Lango) |
| Media: audio, video, galleries, live streaming | FaithFellows, SignConnect Uganda, Oroma TV, Mwonya Streams |
| Learning content, lessons and progress | SignConnect Uganda, NursesPro Academy, Kakebe Tech Camp, Lango platform |
| Dashboards, PDF and spreadsheet reports | KPOS, ObiFunds, BodaERP, OPM Schedules, KMS |
| Email, SMS and push notifications | FaithFellows, KPOS, OPM Schedules, iRecover, Lango App |
| Government and institutional clients | OPM Schedules, Lira City website, BodaERP |
| GPS, photos and signatures captured in the field | Falcon ERP, Lango App |
| AI features | Soila, Innovate2Day AI Writer, Lango Cultural Assistant and translator |
