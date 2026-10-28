# 6. The Lango Mobile App

*The whole platform in a pocket: offline dictionary, guided contribution from the field, notifications and recognition, on Android, iOS and the web.*

For most people in Lango the phone is the computer. The app does two jobs the website cannot: **daily learning** (word of the day, offline dictionary, phrasebook, translator) and **capturing knowledge where it lives**: an elder's story, a heritage site in the bush, a ceremony in a village, recorded with voice, photo and GPS and sent when the connection returns.

![phone|Home: word of the day, points, news, events.](shots/app-home.jpg) ![phone|Contribute: seven guided forms.](shots/app-contribute.jpg) ![phone|Dictionary: works offline.](shots/app-dictionary.jpg) ![phone|Translator: no machine translation.](shots/app-translate.jpg) ![phone|Explore: the whole archive.](shots/app-explore.jpg)

| Tab | What it does |
|---|---|
| **Home and Explore** | Greeting, word of the day, points and level; clans, districts, notable Langos, history, heritage, biodiversity, library |
| **Contribute** | Seven guided forms (cultural observation, natural heritage, word, proverb, oral history, document, heritage site) with GPS, camera, audio and files; tracking code; works offline |
| **Learn** | Dictionary (offline once downloaded), translator, proverbs, phrasebook, suggest-a-word |
| **Connect and Support** | Events with tickets, professionals directory, source-bound Cultural Assistant, news; giving pledges and transparency |
| **Me** | Points, Bronze to Platinum levels, badges, my contributions and their status, in-app and push notifications |

## Built to work with no internet

![fig|Figure 6.1: Offline-first behaviour. A contribution is saved on the phone, queued, and sent when the connection returns, with a unique reference so it can never be stored twice.](figures/fig-offline.svg)

**Stale-while-revalidate:** every page shows the last saved copy at once and refreshes in the background. **Offline dictionary:** one tap downloads all published words, versioned so an update downloads nothing when nothing changed. **Offline contributions:** photo, audio and files wait in a queue and send automatically, **never duplicated**. Tickets and tracking codes open offline, and the sign-in token sits in the phone's secure store.

## Distribution and what comes next

One **Expo / React Native** codebase builds **Android**, **iOS** and an **installable web app** that needs no store, published in the Foundation's own store accounts, in English and Leb Lango. After launch: SMS and USSD fallback, one-time-code sign-in, clan groups, maps and 360 tours in the app, live mobile-money payments.
