# 6. The Lango Mobile App

*The whole platform in a pocket: offline dictionary, guided contribution from the field, notifications and recognition, on Android, iOS and the web.*

## 6.1 Why an app

For most people in Lango the phone is the computer. An installed app is one tap away, works when the signal drops, and can use the things only a phone has: the camera, the microphone, GPS and push notifications. That makes it the right tool for two jobs the website cannot do well:

1. **Daily learning and connection.** A word of the day on the home screen, an offline dictionary, a phrasebook and a translator that works without data.
2. **Capturing knowledge where it lives.** An elder telling a story, a heritage site in the bush, a ceremony in a village: record the audio, take the photo, drop the GPS pin, and let the app send it when the connection returns.

![phone|Home: greeting, word of the day, points and level, news and events.](shots/app-home.jpg) ![phone|Contribute: seven guided forms with GPS, camera, audio and files.](shots/app-contribute.jpg) ![phone|Dictionary: works offline once downloaded.](shots/app-dictionary.jpg) ![phone|Translator: English and Leb Lango, with no machine translation.](shots/app-translate.jpg)

## 6.2 What the app does

The app is organised into five tabs, with one codebase serving Android, iOS and the installable web app.

| Area | Features |
|---|---|
| **Onboarding and account** | Language choice (English or Leb Lango); sign up with clan; sign in with email or phone; browse as a guest; delete the account at any time |
| **Home** | Greeting, quick actions, word of the day, the member's points and level, a notable Lango, news, events, giving call |
| **Explore** | Clans, districts and Lira City, notable Langos, history and timeline, heritage, biodiversity, digital library |
| **Contribute** | Seven guided forms with GPS, camera, gallery, audio recording and file upload; tracking code; works offline (Section 6.3) |
| **Learn** | Dictionary (offline once downloaded), English to Leb Lango translator, proverbs, phrasebook, suggest-a-word |
| **Connect** | Events with ticket registration, professionals directory, the source-bound Cultural Assistant, news, leaderboard |
| **Support** | Donation pledges, programmes and transparency figures |
| **Me** | Points, Bronze to Platinum levels, badges, my contributions and their status, notifications (in-app and push), settings |

![phone|Explore: every section of the archive from one screen.](shots/app-explore.jpg) ![phone|Learn: language tools in one place.](shots/app-learn.jpg) ![phone|Clans: search and browse, with the clan leader shown.](shots/app-clans.jpg) ![phone|Cultural Assistant: answers only from reviewed content, with sources.](shots/app-assistant.jpg)

## 6.3 Built to work with no internet

Connectivity in northern Uganda is uneven and data is expensive, so the app is designed around the worst connection, not the best.

![fig|Figure 6.1: Offline-first behaviour. A contribution is saved on the phone, queued, and sent when the connection returns, with a unique reference so it can never be stored twice.](figures/fig-offline.svg)

| Behaviour | How it works |
|---|---|
| **Stale-while-revalidate** | Every list and page shows the last saved copy immediately, refreshes in the background, and keeps showing the saved copy if there is no internet |
| **Offline dictionary** | One tap downloads all published words. The download is versioned, so an update downloads nothing when nothing has changed. Search works in both directions with the same plain-keyboard folding as the website |
| **Offline contributions** | If a send fails, the contribution and its photo, audio or file are copied into the app's own storage and wait in a queue, with a banner. They are sent automatically when the connection returns |
| **No duplicates, ever** | Each contribution carries a unique reference, so a retry after a dropped connection cannot create a second copy on the server |
| **Tickets and tracking codes** | Saved on the phone so they open offline at the event gate or in a conversation with the Foundation |
| **Secure sign-in** | A token kept in the phone's secure store (Keychain on iOS, Keystore on Android); a rejected token signs the person out cleanly |

## 6.4 Contributing from the field

The app lets members contribute seven kinds of knowledge with the right fields for each:

| Contribution type | Typical use |
|---|---|
| **Cultural observation** | A ceremony, a craft, a custom, a place |
| **Natural heritage** | A plant, animal or habitat, with photo and GPS |
| **Word** | A Leb Lango word, with meaning and example |
| **Proverb** | A proverb with its meaning and context |
| **Oral history** | A story or account, with audio and the name of the speaker (with consent) |
| **Document** | A scan or photograph of a letter, certificate or record |
| **Heritage site** | A place with a GPS pin, photographs and description |

Every contribution shows its **status** (pending, under review, returned, approved, published) to the contributor, with the reviewer's note. When a reviewer acts, the contributor receives an in-app notification and, if they allowed it, a push notification. This feedback loop is what keeps contributors coming back.

## 6.5 Recognition without pressure

Members earn **points** for approved contributions and move through **Bronze, Silver, Gold and Platinum levels**, earning **badges** and appearing on a **leaderboard** of top contributors. The aim is recognition for people who preserve the culture, not competition for its own sake. The Foundation decides at discovery which recognition features are shown publicly, and how contributors can use a display name instead of their own.

## 6.6 Notifications and re-engagement

- **In-app notifications** for every contribution decision, with a read/unread state.
- **Push notifications** through Firebase Cloud Messaging on Android and Apple Push on iOS, sent when a reviewer acts and, in later phases, for events, news and the word of the day.
- **Event registration** with a ticket and code stored on the phone.
- **Word of the day** on the home screen to build a daily habit.

## 6.7 Languages and accessibility

The interface is available in **English and Leb Lango**. Each phrase is looked up in the translations the Foundation's experts enter in the admin panel; anything not yet translated shows in English. Nothing is machine-translated. The app uses large touch targets, readable type (Inter and Source Serif 4) and clear states for loading, errors and empty lists, so a first-time smartphone user is never left on a blank screen.

## 6.8 Platforms and distribution

| Channel | How people get it | Notes |
|---|---|---|
| **Android** | Google Play, or a direct APK link for pilots | Built in the cloud; the Foundation's own Play Console account is recommended |
| **iOS** | Apple App Store | Requires an Apple Developer account in the Foundation's name |
| **Installable web app (PWA)** | Open a link in the browser and choose *Add to Home screen* | No store needed; ideal for people on low-storage phones and for instant sharing |

The app is built with **Expo and React Native**, so one codebase produces all three. Builds run in the cloud, so no special equipment is needed. Over-the-air updates let us fix small problems without a store review. We register and publish the app in the Foundation's own store accounts so that ownership stays with the Foundation.

## 6.9 Roadmap items beyond the first release

These are valuable but not required for a successful launch. We list them so the Foundation can plan and prioritise.

| Item | Why it matters |
|---|---|
| **SMS and USSD fallback** | Reaches people on basic phones and in areas without data |
| **One-time-code (OTP) sign-in** | Easier sign-in for first-time and low-literacy users |
| **Community forum and clan discussion groups** | A moderated space for clans and the diaspora |
| **Maps and 360 degree tours inside the app** | Brings sites and events to life |
| **Live mobile-money and card payments** | MTN MoMo, Airtel Money and cards for giving, tickets and sponsorships (Kakebe already runs these in other products) |
| **Extended AI assistant** | A richer assistant that still answers only from reviewed content |

![phone|Events: registration with ticket and code.](shots/app-events.jpg) ![phone|Support: giving, programmes and transparency.](shots/app-support.jpg) ![phone|People: notable Langos.](shots/app-people.jpg) ![phone|Districts: the sub-region at a glance.](shots/app-districts.jpg)
