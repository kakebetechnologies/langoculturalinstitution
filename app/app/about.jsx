// About Lango and this app: what it is, how to reach us, and who built it.
// The details come from the website's Site settings (GET /api/v1/about), so editing them there updates the app.
import { Linking, StyleSheet, View } from 'react-native';
import Constants from 'expo-constants';
import AppText from '../components/AppText';
import InfoRow from '../components/InfoRow';
import Logo from '../components/Logo';
import Screen from '../components/Screen';
import SectionHeader from '../components/SectionHeader';
import StateView from '../components/StateView';
import { COLORS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function About() {
  const { t } = useT();
  const { data, loading, error, retry } = useFetch('/about');
  const a = data?.data;

  return (
    <Screen title={t('About')}>
      <View style={styles.head}>
        <Logo size={84} />
        <AppText variant="title" style={{ textAlign: 'center', marginTop: SPACING.md }}>
          {a?.name ?? 'Lango'}
        </AppText>
        <AppText variant="eyebrow" style={{ marginTop: 4 }}>
          {a?.subtitle || t('Everything about Lango')}
        </AppText>
      </View>
      <StateView loading={loading && !a} error={error} onRetry={retry}>
        {a ? (
          <>
            <AppText style={{ marginBottom: SPACING.md }}>{t('The Lango App puts Lango heritage, language and clan records in your pocket: discover your heritage, register your clan, contribute cultural and natural observations, learn Leb Lango and connect with the global Lango community.')}</AppText>
            <SectionHeader title={t('Contact')} />
            {a.address ? <InfoRow icon="location-outline" title={a.address} /> : null}
            {a.email ? <InfoRow icon="mail-outline" title={a.email} onPress={() => Linking.openURL(`mailto:${a.email}`).catch(() => {})} /> : null}
            {a.phone ? <InfoRow icon="call-outline" title={a.phone} onPress={() => Linking.openURL(`tel:${a.phone.replace(/\s+/g, '')}`).catch(() => {})} /> : null}
            {a.website ? <InfoRow icon="globe-outline" title={t('Visit the website')} subtitle={a.website} onPress={() => Linking.openURL(a.website).catch(() => {})} /> : null}
          </>
        ) : null}
      </StateView>
      <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.xl }}>
        {t('Lango App {v} · built by Kakebe Technologies Limited', { v: Constants.expoConfig?.version ?? '' })}
      </AppText>
    </Screen>
  );
}

const styles = StyleSheet.create({
  head: { alignItems: 'center', paddingVertical: SPACING.lg, marginBottom: SPACING.md, backgroundColor: COLORS.paper, borderRadius: 12 },
});
