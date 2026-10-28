// Content of the proposal (edition 2): one object per page. Used for the PDF (build2.js) and for the editable Doc (docmd2.js).
const CLIENT = 'Lango Cultural Foundation';
const CONTACT = {
  name: 'Sedrick Otolo', title: 'Director, Kakebe Technologies Limited', phone: '0777 676 206', email: 'ot.sedrick@gmail.com',
  office: 'Rm B1, Plot 43, Renzo City House, Obote Avenue, Lira City, Uganda', web: 'www.kakebe.tech',
};

const pages = [
  /* 3 */ {
    id: 'summary', title: ['Executive', 'Summary'], bandH: 62,
    blocks: [
      { t: 'p', x: `The ${CLIENT} holds a heritage older than any archive: the Leb Lango language, well over a hundred clans, and the stories, songs and proverbs of nine districts and Lira City. Much of it still lives in elders' memories and WhatsApp groups, while a phone-first generation looks for its culture on a screen.` },
      { t: 'p', x: 'Kakebe Technologies proposes **one digital platform** to keep it, teach it and share it: a **public website**, an **admin control panel** and a **mobile app for Android and iOS**, all built on one backend and one review queue.' },
      { t: 'theme', x: '**Development is already under way.** A build from scratch would take about five months. From where we have reached, we can complete and launch the website, the admin panel and both apps in **two months**.' },
      { t: 'key', label: 'What the Foundation gets', items: [
        'A trusted public record of the language, clans, districts and heritage, with sources shown',
        'A Language Hub: dictionary, translator, proverbs and lessons, online and offline',
        'A contribution engine for words, stories and sites, captured by voice, photo and GPS, even offline',
        'Admin tools that the Foundation\'s own staff run, with roles and expert review',
        'Android and iOS apps, plus an installable web app',
        'Training, handover and 90 days of included support',
      ] },
    ],
    band: { photo: 'group-liracity.jpg', quote: 'We are not starting from zero. We are finishing.' },
  },
  /* 4 */ {
    id: 'needs', title: ['Understanding', 'Your Needs'], roomy: true,
    blocks: [
      { t: 'p', x: 'The Lango live in nine districts and Lira City, more than two million people at the 2024 census. The Foundation carries their identity under the Won Nyaci, so its platform must hold knowledge in trust, show where every fact came from, and leave authority with the people the community recognises.' },
      { t: 'table', head: ['What must be solved', 'How the platform responds'], rows: [
        ['**Knowledge held by elders, not on paper**', 'Guided recording of stories, words and sites with voice, photo and GPS, reviewed before publishing'],
        ['**Fading use of Leb Lango among youth**', 'Dictionary, translator, proverbs and lessons that work offline on a phone'],
        ['**A scattered community and costly data**', 'A website for reach, an app for daily use, offline-first design'],
        ['**Trust in what is published**', 'Sources shown, trust labels, expert review, nothing invented'],
        ['**Limited IT capacity**', 'A no-code admin, staff training and a support plan'],
      ] },
      { t: 'sub', x: 'Our principles' },
      { t: 'tiles', items: [
        ['Community authority', 'The Foundation decides what is true'],
        ['Offline first', 'Built for the weakest connection'],
        ['Plain language', 'Simple for first-time smartphone users'],
        ['Own your data', 'Exportable at any time'],
      ], small: true },
      { t: 'sub', x: 'Seven objectives' },
      { t: 'chips', items: ['Preserve', 'Teach', 'Connect', 'Represent', 'Sustain', 'Include', 'Govern'] },
    ],
  },
  /* 5 */ {
    id: 'platform', title: ['One Platform,', 'Three Surfaces'], roomy: true,
    blocks: [
      { t: 'p', x: 'Every surface talks to the same API, database and review queue. A word suggested on a phone in Aduku and one suggested on a laptop in London appear in the same moderation screen, and once an expert approves it, it is live on the website and in the app at the same time.' },
      { t: 'fig', f: 'figures/fig-platform2.svg', c: 'One platform: website, admin panel and mobile app share one API, one database and one review queue.', w: 100 },
      { t: 'checks', items: [
        '**Website** for reach, credibility and search',
        '**Admin control panel** for publishing, review and reporting',
        '**Mobile app** for daily learning and for recording in the field, offline',
      ] },
      { t: 'p', x: 'Built together, the three cost far less than built one after the other, and nothing is built twice.' },
    ],
  },
  /* 6 */ {
    id: 'website', title: ['The Public', 'Website'], roomy: true,
    blocks: [
      { t: 'p', x: 'Fast, bilingual and designed for phones first. It carries the Foundation\'s own identity and switches between English and Leb Lango in one tap.' },
      { t: 'checks', cols: 2, items: [
        'Home, About and Leadership',
        'History: ten chapters, timeline, sources',
        'Districts and Lira City atlas with map',
        'Clan Explorer: a page per clan',
        'Notable Langos',
        'Heritage: ceremonies, crafts, music, sites',
        'Biodiversity register',
        'News, events with tickets, programmes',
        'Library: documents, audio and video',
        'Giving, volunteering and diaspora forms',
      ] },
      { t: 'key', label: 'Language Hub', items: [
        'Dictionary with plain-keyboard search (type e to find ε)',
        'Translator built on the dictionary, not machine translation',
        'Proverbs, greetings and lessons',
        'Suggest a word: reviewed by experts before it goes live',
        'Cultural Assistant: answers only from reviewed content, with sources',
      ] },
      { t: 'theme', x: '**Visitors can contribute.** A word, proverb, oral history, observation or heritage site, with GPS, photos and audio, and a tracking code to follow it.' },
      { t: 'tiles', items: [['WCAG 2.1 AA', 'accessibility target'], ['Mobile first', 'plus a lite mode for slow data'], ['2 languages', 'English and Leb Lango']] },
    ],
  },
  /* 7 */ {
    id: 'admin', title: ['Admin', 'Control Panel'],
    blocks: [
      { t: 'p', x: 'Built for the Foundation\'s staff and volunteers, not for developers. Every change is recorded and reversible.' },
      { t: 'table', head: ['Role', 'Can do'], rows: [
        ['**Admin**', 'Everything, including users, settings and backups'],
        ['**Editor**', 'Publish news, pages, history, heritage and events'],
        ['**Moderator**', 'Review contributions, words, comments and messages'],
        ['**Clan leader**', 'Edit their own clan only; changes are reviewed'],
      ] },
      { t: 'checks', items: [
        '30 content types and a Translations screen for the Leb Lango interface, entered by experts',
        'Notification bell, dashboard, export, backup, version history and a full audit log',
      ] },
      { t: 'sub', x: 'How a contribution is reviewed' },
      { t: 'steps', cols: 3, items: [
        ['Capture', 'on the website or app, even offline'],
        ['Submit', 'a tracking code is issued at once'],
        ['Review', 'a moderator reads, edits and decides'],
        ['Specialist check', 'clan leader or review panel, where needed'],
        ['Publish', 'with contributor and source recorded'],
        ['Notify', 'in-app and push, with points and badges'],
      ] },
      { t: 'theme', x: '**The platform never invents culture.** Content comes only from the Foundation, published sources shown on the page, or contributions a reviewer has approved.' },
    ],
  },
  /* 8 */ {
    id: 'app', title: ['Mobile App', 'Android and iOS'],
    blocks: [
      { t: 'p', x: 'One app for the whole platform: Android on Google Play, iOS on the App Store, and an installable web app for anyone without a store.' },
      { t: 'checks', cols: 2, items: [
        'Offline dictionary and translator',
        'Seven guided forms: observation, natural heritage, word, proverb, oral history, document, heritage site',
        'Record by voice, photo and GPS, even with no signal',
        'Contributions queue on the phone and send once, never duplicated',
        'Track every contribution; push notification when reviewed',
        'Points, Bronze to Platinum levels and badges',
        'Explore clans, districts, history, heritage and events',
        'English and Leb Lango interface',
      ] },
      { t: 'sub', x: 'Offline first' },
      { t: 'steps', cols: 4, items: [
        ['Record', 'story, photo and GPS, with no signal'],
        ['Save', 'on the phone with a unique reference'],
        ['Send', 'automatically when connected'],
        ['Stored once', 'duplicates are ignored'],
      ] },
      { t: 'key', label: 'Publishing', items: [
        'Google Play for Android and the App Store for iOS',
        'Submitted from the Foundation\'s own store accounts',
        'One codebase (Expo and React Native) keeps both apps in step',
      ] },
      { t: 'theme', x: '**One codebase, two stores.** Android and iOS releases are built and tested together, so every member gets the same features on any phone.' },
      { t: 'p', x: '*After launch:* SMS and USSD fallback, one-time-code sign-in, clan groups and live mobile-money payments.' },
    ],
  },
  /* 9 */ {
    id: 'hosting', title: ['Hosting, Security', 'and Privacy'], roomy: true,
    blocks: [
      { t: 'key', label: 'Hosting', items: [
        'On the hosting platform of the Foundation\'s choice: any reputable cloud, VPS or local provider',
        'Accounts, domain and store listings in the Foundation\'s own name',
        'Staging and production environments, HTTPS everywhere',
        'Daily off-site backups and a restore drill before launch',
      ] },
      { t: 'sub', x: 'Security' },
      { t: 'checks', cols: 2, items: [
        'Hashed passwords',
        'API tokens stored only as hashes',
        'Six roles with scoped permissions',
        'Rate limits on sign-in and uploads',
        'Duplicate-safe uploads',
        'Audit log and version history',
        'Forced password change at first sign-in',
        'Penetration test before launch (OWASP Top 10)',
      ] },
      { t: 'sub', x: 'Data protection: Uganda Data Protection and Privacy Act, 2019' },
      { t: 'checks', items: [
        'Plain-language consent and a privacy notice in English and Leb Lango',
        'Only the data needed; phone, country and clan are optional',
        'Members can view, correct and delete their data',
        'A retention schedule and a named data protection focal person',
        'Clear rules for children\'s content and consent for recorded elders',
      ] },
      { t: 'tiles', items: [['PHP 8.2', 'website and admin'], ['MySQL', 'one database'], ['React Native', 'Android and iOS'], ['JSON API', 'shared by all']], small: true },
    ],
  },
  /* 10 */ {
    id: 'delivery', title: ['Delivery in', 'Two Months'],
    blocks: [
      { t: 'p', x: 'Development has already started. A full build would take about five months; from our current position we complete and launch everything in **eight weeks**, then support it for 90 days.' },
      { t: 'fig', f: 'figures/fig-road.svg', c: 'Two-month plan with five milestones and 90 days of support.', w: 90 },
      { t: 'table', head: ['Phase', 'Weeks', 'What happens'], rows: [
        ['**Plan and align**', '1', 'Kick-off, content audit, scope and brand assets confirmed'],
        ['**Design and model**', '1 to 3', 'Brand and interface design, clickable prototype, data model'],
        ['**Build and complete**', '2 to 6', 'Website, admin, language and community modules; Android and iOS apps'],
        ['**Content, test, train**', '5 to 7', 'Content loaded and verified; testing; pilot; staff training'],
        ['**Launch**', '7 to 8', 'Store submissions, go-live, launch communications'],
      ] },
      { t: 'theme', x: '**What keeps it to two months:** the working platform, a named Foundation coordinator in week 1, reviews within five working days, and an agreed launch content set.' },
    ],
  },
  /* 11 */ {
    id: 'team', title: ['Our', 'Team'],
    blocks: [
      { t: 'team', people: [
        ['Sedrick Otolo', 'Director', 'Leads delivery and the client relationship', 'sedrick-otolo.jpg'],
        ['Patrick Okello', 'Chief Technology Officer', 'Architecture, security and quality', 'patrick-okello.jpg'],
        ['Oscar Jerome Okello', 'Web Development Lead', 'Website and admin build', 'oscar-jerome.jpg'],
        ['Komakech Moses', 'Sales and Marketing', 'Engagement and training coordination', 'komakech-moses.jpg'],
        ['Bodo Desderio', 'SEO Engineer', 'Search, structure and analytics', 'bodo-desderio.jpg'],
      ] },
      { t: 'p', x: 'Supported by dedicated mobile developers, a product designer, a QA engineer and supervised interns from our Digital Bridge Internship Programme.' },
      { t: 'key', label: 'Working with you', items: [
        'A weekly 30-minute call with a one-page status report',
        'A demonstration every two weeks on a private staging site',
        'One Foundation coordinator, with named language and heritage panels',
        'Sign-off at four gates: scope, design, acceptance and go-live',
      ] },
      { t: 'table', head: ['On the Foundation side', 'Role'], rows: [
        ['**Executive sponsor**', 'Approves milestones and resolves escalations'],
        ['**Project coordinator**', 'Decisions, feedback and sign-off, about four hours a week'],
        ['**Language and heritage panels**', 'Review Leb Lango wording, history and clan content'],
        ['**Communications officer**', 'Brand assets and news; becomes the lead editor'],
      ] },
    ],
  },
  /* 12 */ {
    id: 'training', title: ['Training, Handover', 'and Support'], roomy: true,
    blocks: [
      { t: 'table', head: ['Who', 'Training'], rows: [
        ['**Administrators**', 'One day, hands-on, in Lira: users, roles, settings, backups'],
        ['**Editors and communications**', 'One day: news, pages, events, media, translations'],
        ['**Moderators and language experts**', 'One day: the review queue, dictionary and proverbs'],
        ['**Clan leaders and youth ambassadors**', 'Half a day each, in English and Leb Lango'],
      ] },
      { t: 'key', label: 'At handover', items: [
        'Access to hosting, domain, database and store accounts, in the Foundation\'s name',
        'Technical and user documentation, an admin manual and short videos',
        'A content inventory with the source of every loaded fact',
        'A maintenance runbook',
      ] },
      { t: 'sub', x: 'After launch' },
      { t: 'checks', items: [
        '**90 days of hypercare, included:** daily monitoring, rapid fixes, usage reviews at 30, 60 and 90 days',
        '**Then a support plan** (Essential, Standard or Premium): uptime monitoring, security updates, backups, defect fixes, app-store updates, quarterly review',
        '**No lock-in:** the Foundation owns its content, data and accounts and can export everything at any time',
      ] },
    ],
  },
  /* 13 */ {
    id: 'about', title: ['About', 'Kakebe'], bandH: 58,
    blocks: [
      { t: 'p', x: 'Kakebe Technologies Limited is a youth-led technology company in Lira City, in the heart of Lango. We build software, AI and robotics for real local problems, and train young people to build the next ones.' },
      { t: 'tiles', items: [['5+', 'years in Lira City'], ['257', 'youth graduated Tech Camp 2025'], ['6', 'service lines'], ['20+', 'platforms delivered']] },
      { t: 'sub', x: 'Our programmes' },
      { t: 'checks', items: [
        '**Kakebe Tech Camp:** residential bootcamp for ages 13 to 30; next camp 14 to 23 December 2026',
        '**Digital Bridge Internship Programme** and the **Innovation Programme**, home of the Lango Innovation Challenge',
      ] },
      { t: 'sub', x: 'Why Kakebe' },
      { t: 'checks', items: [
        '**We are local:** workshops with elders and clan leaders happen in person, in Lira',
        '**We have already built it:** the working platform exists today',
        '**Proven delivery:** content-managed sites, mobile apps on a shared API, offline-first design, mobile-money payments',
        '**We build capacity:** staff trained, documentation complete, content and data yours',
        '**A public partner:** BetterLife International announced its partnership with Kakebe on 20 October 2026',
      ] },
    ],
    band: { photo: 'robotics.jpg', quote: 'Built in Lira. Built for Lango.' },
  },
  /* 14 */ {
    id: 'experience', title: ['Our', 'Experience'],
    blocks: [
      { t: 'table', cls: 'ref', head: ['Project', 'What we built', 'Why it matters'], rows: [
        ['**Lango cultural platform**', 'Website, admin, API and mobile app, working end to end', 'The complete solution, already built'],
        ['**A Better Life International** betterlifeint.org', 'Redesigned site with farm shop, card and mobile-money checkout, full admin, blog and press hub', 'A foundation site run by non-technical staff'],
        ['**Betty Amongi Foundation**', 'Foundation website, CMS and a mentorship portal', 'Serves the same Lango community'],
        ['**Civic Coded** civiccoded.com', 'Civic-education platform: citizens ask about rights, laws and bills and get sourced answers saved as lessons', 'Sourced answers, like our Cultural Assistant'],
        ['**OPM Schedules**', 'Scheduling for ministry offices: reminders and a weekly PDF programme', 'Government-grade roles and isolation'],
        ['**iRecover**', 'Lost-documents recovery with matching, mobile-money fees and tracking codes', 'Review workflow with tracking codes'],
        ['**BodaERP**', 'Rider registry and fee platform across five roles, from riders to government', 'A large registry like the clan register'],
      ] },
      { t: 'sub', x: 'Also delivered' },
      { t: 'checks', cols: 2, items: [
        'ObiFunds: crowdfunding',
        'ObiTickets: QR event ticketing',
        'ChamaFunds: community fundraising',
        'Kakebe Tech Camp platform',
        'KPOS: point of sale',
        'Falcon ERP: export operations',
        'Mwonya Streams: culture streaming',
        'Kakebe Shop Online',
        'Oroma TV: news and live streams',
        'Innovate2Day: innovation hub',
        'NUFA and NUFA Awards',
        'Mighty Rides: portals and accounting',
      ] },
    ],
  },
  /* 15 */ {
    id: 'more', title: ['Experience,', 'Continued'],
    blocks: [
      { t: 'table', cls: 'ref', head: ['Project', 'What we built', 'Why it matters'], rows: [
        ['**FaithFellows**', 'Church platform: web and Expo app on one API; English, Luo, Luganda, Swahili', 'The app architecture, in production'],
        ['**Soila**', 'AI app that helps farmers test soil', 'AI on mobile for rural users'],
        ['**Better Life Farmers Connect**', 'Mobile application for farmers', 'Farmer-facing mobile experience'],
        ['**Lira City website**', 'City website rebuild with CMS, tenders and citizen issue reports', 'A public institution in Lira'],
        ['**SignConnect Uganda**', 'Sign-language learning: tagged videos, lessons and classes', 'The closest cousin to the Language Hub'],
      ] },
      { t: 'sub', x: 'What the Foundation needs, and where we delivered it' },
      { t: 'table', head: ['Need', 'Delivered in'], rows: [
        ['**No-code content management**', 'A Better Life International, Betty Amongi Foundation, Lira City website'],
        ['**Multi-role access**', 'OPM Schedules, BodaERP, iRecover, FaithFellows'],
        ['**App and web on one API, offline**', 'FaithFellows, SignConnect Uganda, KPOS'],
        ['**Mobile money**', 'ObiFunds, ObiTickets, iRecover, KPOS, Kakebe Tech Camp'],
        ['**Tracking codes**', 'Lira City website, iRecover'],
        ['**Local-language interfaces**', 'FaithFellows (Luo, Luganda, Swahili), KPOS'],
        ['**AI features**', 'Civic Coded, Soila, Innovate2Day'],
      ] },
      { t: 'p', x: 'References, and private demonstrations of confidential systems, are available on request.' },
    ],
  },
  /* 16 */ {
    id: 'risks', title: ['Risks and', 'Assumptions'],
    blocks: [
      { t: 'table', head: ['Risk', 'How we manage it'], rows: [
        ['**Content arrives late**', 'Content audit in week 1, an agreed launch content set, bulk import, supervised interns'],
        ['**Slow reviews on a two-month plan**', 'Named roles, a five-working-day review rule, fortnightly demonstrations'],
        ['**Low connectivity**', 'Offline-first app, queued uploads, a lite web mode'],
        ['**Disputed or inaccurate content**', 'Sources shown, trust labels, a Cultural Review Panel, nothing published without approval'],
        ['**App-store review delays**', 'Store accounts set up in week 1, Android first, web app live from day one'],
      ] },
      { t: 'key', label: 'Assumptions', items: [
        'The Foundation names its coordinator and panels in week 1 and reviews within five working days',
        'Cultural and language content is supplied or verified by the Foundation\'s experts',
        'Not included: original content writing, professional photography, large-scale scanning, live payments, SMS and USSD, printed materials',
        'Content and data belong to the Foundation; custom code is licensed to it without time limit',
        'Financial terms are in the separate Financial Proposal',
      ] },
      { t: 'sub', x: 'How we measure success' },
      { t: 'chips', items: ['Records in the archive', 'Share verified', 'Contributions per month', 'Monthly active users', 'App installs', 'Staff running it unaided'] },
    ],
  },
  /* 17 */ {
    id: 'next', title: ['Next', 'Steps'], bandH: 54,
    blocks: [
      { t: 'steps', items: [
        ['Review', 'this proposal and the Financial Proposal'],
        ['Meet', 'a live demonstration and a 90-minute discovery workshop'],
        ['Agree', 'scope and sign the agreement'],
        ['Kick off', 'week 1 begins'],
        ['Launch', 'end of week 8'],
      ] },
      { t: 'table', head: ['Acceptance', `For the ${CLIENT}`, 'For Kakebe Technologies'], rows: [
        ['**Name**', '', CONTACT.name],
        ['**Title**', '', 'Director'],
        ['**Signature**', '', ''],
        ['**Date**', '', ''],
      ], cls: 'sign' },
      { t: 'contact' },
    ],
    band: { photo: 'techcamp-graduation.jpg', quote: 'We would be proud to build this with the Foundation, in Lango, for Lango.' },
  },
];

module.exports = { CLIENT, CONTACT, pages };
