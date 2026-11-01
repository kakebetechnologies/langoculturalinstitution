# Lango on Google Play: submission pack

Package `tech.kakebe.lango`, version 1.0.0, built with EAS (`eas build -p android --profile production`, an app bundle `.aab` signed with the EAS-managed upload key).
Everything below was written from what the app actually does; replace anything in [square brackets].

## 1. Files in this folder

| File | Use in Play Console |
|---|---|
| `icon-512.png` | Store listing, App icon (512 x 512) |
| `feature-graphic-1024x500.png` | Store listing, Feature graphic |
| `screenshots/phone-01.png` to `phone-08.png` | Store listing, Phone screenshots (1080 x 1920, 9:16) |
| `source/` | `make_assets.py` and `render_assets.js` rebuild all of the above from the briefing's real screenshots |

## 2. Store listing text

**App name (30 max):** Lango: Language & Culture

**Short description (80 max, 78 used):** Learn Leb Lango, find your clan, explore Lango heritage and add what you know.

**Full description (4000 max):**

```
Lango is a free app about the Lango people of northern Uganda: their language, clans, heritage and community, all in one place.

LEARN LEB LANGO
• Search the dictionary in Leb Lango or English, even without internet
• Look up words and set phrases with the translator
• Proverbs, greetings and everyday phrases
• A new word every day, quizzes with sources and a weekly language challenge

KNOW YOUR CLAN AND YOUR PEOPLE
• Clans with their totems and prohibitions, clearly marked until language experts and clan leaders have verified them
• Notable Langos, history and districts, with sources and photo credits
• Heritage: attire, crafts, music and ceremonies
• Join the group of your clan

LISTEN, READ AND FOLLOW
• Lango Radio and Podcast, which keep playing in the background
• Events, anniversaries and festivals, with a countdown
• The Lango Bible, opened at the chapter you choose

ADD WHAT YOU KNOW
• Share a word, proverb, story, photo or recording
• Every contribution is reviewed before it is published
• Works offline: your contribution is sent when you are connected again
• Earn points, badges and streaks

CAREFUL AND TRANSPARENT
• Sources and credits are shown throughout
• Nothing is marked verified until a language expert or clan leader says so
• Sign in with Google or your email, and delete your account at any time in the app

Lango is an independent community project. It is free and has no advertising.
```

**Release notes (500 max):** First release of Lango: dictionary, clans, heritage, quizzes, radio and podcast, events and community contributions.

**Category:** Education (second choice: Books & Reference). **Tags:** Education, Language learning, Culture.
**Default language:** English. **Contact:** website https://myculture.online, email [a monitored address, for example info@myculture.online once the mailbox exists].
**Privacy policy URL:** https://myculture.online/privacy

## 3. App content declarations

| Section | Answer |
|---|---|
| App access | Restricted: the app needs an account. Give a reviewer login (see section 5) |
| Ads | No ads |
| Content rating (IARC) | Violence, sex, bad language, drugs, gambling: none. User-generated content: yes (clan group messages and contributions, reviewed by moderators; three reports hide a message). Shares real-time location with others: no. Expect "Everyone" or a low teen rating |
| Target audience | 13 and over (the app is not aimed at children) |
| News app / Government app / Health app / Financial features | No |
| Data safety | See section 4 |
| Account deletion | In the app: Me, Edit profile, Delete account. On the web: https://myculture.online/account/delete |

## 4. Data safety form

Collected, all encrypted in transit (HTTPS), none sold, none used for ads, none shared with other companies (Expo delivers notifications and the web host stores data as service providers; Google supplies the name and email only when a person chooses Sign in with Google). Users can request deletion (in the app and at the web address above).

| Data type | Collected | Why | Required or optional |
|---|---|---|---|
| Name | Yes | Account, credit if the person ticks the credit box | Required |
| Email address | Yes | Account and sign-in | Required |
| Phone number | Yes | Optional profile detail | Optional |
| Other personal info (country, clan) | Yes | Profile and clan group | Optional |
| User IDs (account number) | Yes | Account | Required |
| Photos | Yes | Contributions | Optional |
| Voice or sound recordings | Yes | Contributions (stories, pronunciation) | Optional |
| Precise location | Yes | Tag where a plant, animal, ceremony or site was seen, only when the person adds it | Optional |
| Other user-generated content | Yes | Words, stories, clan group messages | Optional |
| App interactions (quiz scores, streaks, points) | Yes | App functionality | Required for those features |
| Device or other IDs (push notification address) | Yes, if notifications are allowed | Notifications | Optional |

Not collected: financial info, health, contacts, calendar, web browsing history, crash logs or analytics (none are built in).

## 5. Review access (App content, App access)

Create the reviewer account yourself on https://myculture.online (sign up with an email and password, not Google), then paste into Play Console: the email, the password and one line such as "Open the app, choose Continue with email, sign in. All features are then available. Radio and Podcast show content once the editors have added audio."
Do not reuse a real member's account.

## 6. Declarations Google will ask for

- **Foreground service, media playback** (Lango Radio and Podcast): "The service starts only when the person taps play on Radio or Podcast and shows playback controls in the notification." Google asks for a short screen recording: open Radio, play, leave the app, show the notification controls.
- **Location:** foreground only (while the app is open), used only to tag a contribution. No background location.
- **Camera, microphone, photos:** asked only when the person taps to take a photo, record or attach one.
- **Giving ("Support the Lango App"):** the app only shows how to give outside the app and records a pledge; it does not take payments. If Google asks, say exactly that.

## 7. Before you press "Send for review"

1. Deploy the latest commits to myculture.online (the new privacy policy, the delete-account page and the API changes must be live, because the store checks those links and the app talks to the live site).
2. In Google Cloud, add the two redirect addresses (`https://myculture.online/auth/google/callback` and the local one) and enter the Google keys in Admin, Site settings, Google sign-in on the live site, otherwise "Continue with Google" will not appear.
3. Create the reviewer account and add it under App access.
4. Create the support mailbox and put the address in the store listing.
5. Record the media-playback screen recording.
6. Read the privacy policy once: it names Kakebe Technologies Limited as the operator and says the app is for people 13 and over. Change either if it is not right.
7. Android push notifications will not arrive until Firebase is set up (a `google-services.json` in the app folder and the FCM key uploaded to Expo). The app works without them.

## 8. Upload

1. Download the `.aab` from the EAS build page (expo.dev, project lango-app, Builds).
2. Play Console, Create app, fill in sections 2 to 4 above.
3. Testing, Internal testing (or Closed testing), Create release, upload the `.aab`, accept Play App Signing. EAS's key becomes the upload key.
4. Test on a real phone through the internal testing link, then promote to Production.
5. A personal developer account created after November 2023 must first run a closed test with at least 12 testers for 14 days before Production is allowed; organisation accounts are exempt. Check which type you have.
6. For later versions: `eas build -p android --profile production` (the version code goes up by itself), then upload the new `.aab` the same way.
