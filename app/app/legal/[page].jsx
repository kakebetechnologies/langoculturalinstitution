// Privacy policy and terms of use / contribution terms, read inside the app. These mirror the website's pages (myculture.online/privacy and /terms).
// i18n:all (the phrases in this file are listed for translation by `npm run strings`)
import { StyleSheet, View } from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Screen from '../../components/Screen';
import { SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';

const PAGES = {
  privacy: {
    title: 'Privacy policy',
    updated: 'Last updated: 1 November 2026.',
    sections: [
      ['Who we are', 'Lango is a free app and website about Lango language, clans, heritage and community, run by Kakebe Technologies Limited, Uganda.'],
      ['What we collect', 'Account details: your name and email, and (if you add them) phone, country and clan. If you sign in with Google, we receive your name and email from Google. What you share: words, stories, photos, audio recordings, the place you tag if you choose to, clan group messages and quiz answers. How you use the app: points, badges, streaks and quiz scores. If you allow notifications, a notification address for your phone. We do not sell your data, show advertising or track you across other apps or websites.'],
      ['Why we use it', 'To run your account, review what you contribute, show your progress, send the alerts you allow, answer you, keep the service safe, and report in general terms on how Lango is used. A contributor’s first name and clan are shown beside a word only if you tick the credit box.'],
      ['Camera, microphone, photos and location', 'The app asks for these only when you use them: the camera to photograph something to contribute, the microphone to record a story or how a word is said, your photos to attach one, and your location to tag where you saw something. You can say no and still use the rest of the app. Uploaded photos are re-encoded to remove hidden metadata, and moderators can hide the exact place of sensitive sites or endangered species before anything is published.'],
      ['Who else handles your data', 'Our web host, which stores the database and uploads; Google, if you choose Sign in with Google; Expo, which delivers push notifications; and language experts and clan leaders we invite to review contributions. Links to other sites, such as the Lango Bible on bible.com, open in their own pages under their own policies.'],
      ['Keeping it and deleting it', 'We keep your account while you use it. You can delete it any time in the app (Me, Edit profile, Delete account) or at myculture.online/account/delete. Deleting removes your personal details, points, streaks, quiz results, messages and sign-in tokens. Contributions already published in the archive stay, without your name, because they belong to the archive; ask us if you want one taken down.'],
      ['Your rights', 'You can see and correct your details in the app, and ask us to export or delete your data. We follow the principles of Uganda’s Data Protection and Privacy Act, 2019, and general international data-protection practice.'],
      ['Children', 'Lango is for a general audience and is not aimed at children under 13. If you think a child has given us personal data, tell us and we will delete it.'],
      ['Security', 'Connections use HTTPS, passwords are hashed, the app keeps a revocable sign-in token in your phone’s secure storage, uploads are checked, and the database is backed up regularly.'],
      ['Contact', 'Questions or requests about your data: use the Contact page at myculture.online/contact.'],
    ],
  },
  terms: {
    title: 'Terms of use and contribution terms',
    updated: 'Last updated: 1 November 2026.',
    sections: [
      ['Using the app', 'Content in the app is provided to preserve and share Lango culture. Please respect the dignity of the Lango people and their traditions.'],
      ['Contributing', 'When you contribute, you confirm the work is yours or you have permission to share it. You keep your rights. By choosing a sharing option you allow this app to display it in the archive in line with that option. Contributions are moderated and may be edited for clarity, declined, or have sensitive details removed.'],
      ['Sacred and restricted knowledge', 'Some knowledge is private to clans or ceremonies. Please do not submit material that you have not been permitted to share. Moderators may withhold anything culturally sensitive.'],
      ['Data licence', 'Published data may be reused with credit to Lango unless otherwise stated.'],
      ['Donations', 'Pledges are recorded and confirmed once they arrive. Gifts help keep MyCulture running.'],
    ],
  },
};

export default function Legal() {
  const { t } = useT();
  const { page } = useLocalSearchParams();
  const p = PAGES[page] ?? PAGES.privacy;

  return (
    <Screen title={t(p.title)}>
      <AppText variant="caption" style={{ marginBottom: SPACING.lg }}>
        {t(p.updated)}
      </AppText>
      {p.sections.map(([h, body]) => (
        <View key={h} style={{ marginBottom: SPACING.md }}>
          <AppText variant="heading" style={{ marginBottom: 4 }}>
            {t(h)}
          </AppText>
          <AppText>{t(body)}</AppText>
        </View>
      ))}
    </Screen>
  );
}
