// Privacy policy and terms of use / contribution terms, read inside the app. These mirror the website's pages, which are drafts
// for legal review before launch (and the screens say so).
// i18n:all (the phrases in this file are listed for translation by `npm run strings`)
import { StyleSheet, View } from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Screen from '../../components/Screen';
import { COLORS, SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';

const PAGES = {
  privacy: {
    title: 'Privacy policy',
    sections: [
      ['What we collect', 'Only what you give us: your name, email, phone, country and clan when you create an account; content you contribute (photos, recordings, text, locations); donation pledges and event registrations. We do not sell personal data.'],
      ['How we use it', 'To run the Lango archive, review contributions, reply to you, administer events and programs, and report on our impact. Donor names are shown publicly only if you choose to be recognised. A contributor’s first name and clan are shown beside a word only if you tick the credit box.'],
      ['Photos and location', 'Uploaded photos are re-encoded to remove hidden metadata. GPS coordinates are stored only when you add them yourself, and exact locations of sensitive sites or endangered species can be hidden by our moderators before publication. The app asks for your location, camera and microphone only when you use them.'],
      ['Your rights', 'You can edit your profile in the app, and delete your account in Me → Edit profile → Delete account. You can also ask us to see, correct or delete your data at any time. We aim to follow international data-protection standards, including GDPR principles.'],
      ['Security', 'Passwords are stored hashed, the app signs in with a revocable token kept in your phone’s secure storage, uploads are validated, and the database is backed up regularly.'],
    ],
  },
  terms: {
    title: 'Terms of use and contribution terms',
    sections: [
      ['Using the app', 'Content in the app is provided to preserve and share Lango culture. Please respect the dignity of the Lango people and their traditions.'],
      ['Contributing', 'When you contribute, you confirm the work is yours or you have permission to share it. You keep your rights. By choosing a sharing option you allow LCI to display it in the archive in line with that option. Contributions are moderated and may be edited for clarity, declined, or have sensitive details removed.'],
      ['Sacred and restricted knowledge', 'Some knowledge is private to clans or ceremonies. Please do not submit material that you have not been permitted to share. Moderators may withhold anything culturally sensitive.'],
      ['Data licence', 'Published data may be reused with credit to the Lango Cultural Institution unless otherwise stated.'],
      ['Donations', 'Pledges are recorded and confirmed by the LCI finance office. Funds support the programs described in the app.'],
    ],
  },
};

export default function Legal() {
  const { t } = useT();
  const { page } = useLocalSearchParams();
  const p = PAGES[page] ?? PAGES.privacy;

  return (
    <Screen title={t(p.title)}>
      <View style={styles.draft}>
        <AppText variant="label" style={{ color: COLORS.yellowInk }}>
          {t('Draft for legal review before launch.')}
        </AppText>
      </View>
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

const styles = StyleSheet.create({
  draft: { backgroundColor: COLORS.yellowMist, borderRadius: 8, padding: SPACING.md, marginBottom: SPACING.lg },
});
