# MyCulture on the App Store and TestFlight: everything to fill in

Bundle ID `tech.kakebe.lango` (cannot change), version 1.0.0, built with EAS (`eas build -p ios --profile production --auto-submit`).
App Store Connect app ID 6836253619. Every field below was written from what the app does today; replace anything in [square brackets].
Character limits are Apple's; each block below is within its limit.

## 1. App Information

| Field | Value |
|---|---|
| Name (30 max) | `MyCulture` (the record is currently called "Lango App (69dabd)" because "Lango App" was taken; change it in App Information, Name. If "MyCulture" is also taken, use `MyCulture: Uganda`) |
| Subtitle (30 max) | `Uganda's languages & cultures` |
| Primary language | English (U.K.) |
| Primary category | Education |
| Secondary category | Reference |
| Content rights | "Does your app contain, show, or access third-party content?" Yes: it shows Wikipedia text under CC BY-SA and Wikimedia Commons photos with credits, and links out to bible.com. Say you have the rights or licences (credits are shown in the app) |
| Age rating | Answer the questionnaire (section 5) |

## 2. Version 1.0 page (App Store listing)

**Promotional text (170 max, can be changed any time):**

```
Learn Leb Lango, find your clan, explore Lango and Acholi heritage and add what you know. Free, no ads, and every contribution is reviewed.
```

**Description (4000 max):**

```
MyCulture is a free app for learning, keeping and sharing the languages and cultures of Uganda, one community at a time. It starts with the Lango people of northern Uganda and the Acholi, and is built so that other communities can follow.

Choose your community when you start, and the words, clans or chiefdoms, districts, history and heritage you see are that community's own. The Acholi edition is new and is still being filled in.

LEARN THE LANGUAGE
• Search the dictionary in Leb Lango or English, and save it on your phone to use without internet
• Look up words and set phrases with the translator
• Proverbs, greetings and everyday phrases
• A new word every day, quizzes with sources and a weekly language challenge

KNOW YOUR PEOPLE
• Clans (Lango) and chiefdoms (Acholi), with their totems and traditions
• Districts, notable people and history, with sources and photo credits
• Heritage: attire, crafts, music and ceremonies
• Join the group of your clan and meet other members

ADD WHAT YOU KNOW
• Share a word, proverb, story, photo or recording
• Every contribution is reviewed before it is published
• Works offline: your contribution is sent when you are connected again
• Earn points, badges and streaks

CAREFUL AND TRANSPARENT
• Sources and credits are shown throughout
• Nothing is marked verified until a language expert or community leader says so, and much of the content is still waiting for that review
• Sign in with Google or with your email, and delete your account at any time in the app

MyCulture is an independent community project by Kakebe Technologies Limited. It is free and has no advertising.
```

**Keywords (100 max, comma-separated, no spaces after commas):**

```
lango,acholi,leb lango,luo,dictionary,heritage,clans,proverbs,translator,history,quiz,learn,africa
```

(98 characters. "Uganda", "languages" and "cultures" are left out because the subtitle already carries them.)

**What's New in this version (4000 max):**

```
First release of MyCulture: the Lango and Acholi communities, a dictionary you can save offline, clans and chiefdoms, heritage, history, quizzes and a daily word, and a way for members to add what they know.
```

| Field | Value |
|---|---|
| Support URL | https://myculture.online/contact |
| Marketing URL | https://myculture.online |
| Privacy Policy URL | https://myculture.online/privacy |
| Copyright | `2026 Kakebe Technologies Limited` |
| Version release | Manually release this version |
| Price | Free |
| Availability | All countries and regions (or choose Uganda and the countries you want) |

**Screenshots (required before review, not for TestFlight).** Ready in `app-store/screenshots/`, 8 of each, in the order to upload them:
- `iphone-6.3/01.png` to `08.png`, 1206 x 2622: drop them on **iPhone with Dynamic Island (medium display)** (App Store Connect accepts 1206 x 2622 and 1179 x 2556 there). Apple scales them up for the larger iPhone sizes.
- `ipad-13/01.png` to `08.png`, 2064 x 2752: drop them on **iPad 13" display**. `app.json` has `supportsTablet: true`, so App Store Connect asks for them; set it to `false` in the next build if you want phones only.
- iPhone Duo and Apple Watch: leave empty (not needed).

They are marketing screenshots: a headline, one line under it and real screens of the app (iPhone: one phone; iPad: two). The screens are taken from the app running as a visitor at the exact iPhone size (`source/capture_ios.js`, needs the Expo web server on :8095 and the local site), the pictures are laid out by `source/make_ios.py` and rendered with Edge. A review of the first version corrected the wording to what the app really does: "starting with Lango" (Acholi is new and thin), "Save it once to use offline" (the dictionary is not downloaded automatically), "clearly marked until they are verified" (no clan is verified yet), and no "any word" or "history" claims. Re-check the captions whenever the app changes.

## 3. App Privacy (the "nutrition label")

Tracking: **No** (no advertising, no analytics, no data shared with data brokers). All collected data is **linked to the user** and used for **App Functionality** only.

| Data type (Apple's name) | Collected | Notes |
|---|---|---|
| Contact Info: Name | Yes | Account; shown as credit only if the person ticks the box |
| Contact Info: Email Address | Yes | Account and sign-in |
| Contact Info: Phone Number | Yes | Optional profile detail |
| User Content: Photos or Videos | Yes | Contributions, optional |
| User Content: Audio Data | Yes | Recorded stories and pronunciations, optional |
| User Content: Other User Content | Yes | Words, stories, clan group messages |
| Location: Precise Location | Yes | Only when the person taps to tag a contribution, optional |
| Identifiers: User ID | Yes | Account number |
| Identifiers: Device ID | Yes, if notifications are allowed | Push notification address |
| Usage Data: Product Interaction | Yes | Quiz scores, points, streaks |

Not collected: financial info, health, contacts, browsing history, diagnostics or crash data, purchases.

## 4. App Review Information (needed for the review, not for internal TestFlight)

| Field | Value |
|---|---|
| Sign-in required | Yes: give a reviewer account. Create it yourself in the app or on https://myculture.online/register (an email and a password, not Google; sign-up is instant and needs no email check). Use a password you do not use anywhere else, keep the account only for Apple, and do not reuse a real member's account |
| Contact | Sedrick Otolo (Kakebe Technologies Limited), [phone with country code], [an email you check: info@myculture.online is the one the site's Contact page shows] |

**Notes for the reviewer:**

```
MyCulture documents and teaches the languages and cultures of Ugandan communities. Lango is the first full community; Acholi is new and still being filled in. On the first screens the person chooses Lango or Acholi, then signs in with email and password or with Google. Creating an account is free and instant, and email and password sign-up is always available, so no third-party account is needed. Use the demo account above to see every feature.

Where to look: Home (word of the day), Explore (heritage, clans or chiefdoms, history, districts, notable people, Bible), Learn (dictionary, translator, proverbs, phrasebook, quizzes), Contribute (suggest a word, add a proverb, a story, a photo or a recording) and Me (profile, settings, Delete account). The app needs an internet connection; the dictionary can be saved once in Settings for offline search.

User-generated content: members can send words, photos, recordings and clan group messages. Every contribution is reviewed by moderators before it is published; group messages can be reported (three reports hide a message) and moderators can remove content and accounts. Contact for reports: info@myculture.online.

Account deletion: Me, Edit profile, Delete account (also at https://myculture.online/account/delete).

Permissions: the camera, microphone, photos and location are requested only when the person taps to attach a photo, record or tag a place for a contribution. Location is used only while the app is open.

The Bible screens show chapters from the Bible Society of Uganda's pages on Bible.com inside the app; the app does not store or copy that text. Some sections (radio, podcast, events, news) show content only after the editors add it, so they may be empty. There are no in-app purchases and no ads.
```

Export compliance: already answered in the app (`ITSAppUsesNonExemptEncryption = false`: HTTPS only), so no extra questions.

## 5. Age rating questionnaire (Apple works the rating out from these answers)

Violence, sexual content, nudity, profanity, horror, drugs, alcohol, tobacco, gambling, contests, medical advice: **None**.
User-generated content / messaging between users: **Yes** (clan group messages and contributions, moderated and reportable).
Unrestricted web access: **No** (the Bible screen opens one known website). Expect a rating of about 12+ or 13+ because of user-generated content; the app is not aimed at children.

## 6. TestFlight (internal testing, no review needed)

The EAS upload created the TestFlight group "Team (Expo)" and gave access to the two App Store Connect users it found. Add more people under TestFlight, Internal Testing (they must be App Store Connect users), or under External Testing (needs a short Beta App Review).

| Field | Value |
|---|---|
| Beta App Description | `MyCulture lets people learn and share the languages and cultures of Uganda. Lango is the first full community; Acholi is new and still being filled in.` |
| Feedback email | [an address you check] |
| What to Test | `Choose Lango or Acholi, create an account, search the dictionary, open Clans or Chiefdoms and Heritage, play today's word quiz, and add a contribution (a word, a photo or a recording). Tell us anything that looks wrong or is hard to use.` |
| Beta App Review contact and sign-in | The same reviewer account and notes as section 4 (External Testing only) |

The build appears in TestFlight about 10 to 30 minutes after EAS finishes uploading it (status "Processing", then ready).

## 7. Things the App Store review may raise later (not for TestFlight)

1. **Sign in with Apple.** The app offers "Continue with Google"; Apple's rule 4.8 asks for an equivalent privacy-preserving login (Sign in with Apple, or a login that hides the email and does not track). Email and password sign-up already exists; if the review insists, Sign in with Apple has to be added.
2. **User-generated content (rule 1.2)** wants filtering, a way to report, a way to block abusive users and published contact details. Reporting a group message and moderator removal exist; blocking a member does not yet.
3. **Name on the phone.** Done: build 4 (1.0.0) shows "MyCulture" under its icon. The app still uses the Lango emblem as its logo.
4. **Acholi dictionary.** Live since 18 Nov 2026: 4,252 words, 577 of them tagged "Doubtful, check with a speaker". A reviewer who picks Acholi will see the rest of the Acholi edition thin (no quizzes, no proverbs, no radio), which the description and the notes say plainly. Its source (Blackings 2009) is copyrighted; settle that with the publisher before the public release.
