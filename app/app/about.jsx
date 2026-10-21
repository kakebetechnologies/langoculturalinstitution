// About the Lango Cultural Institution and this app: the mission and vision, how to reach LCI, and who built the app.
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
          {a?.name ?? 'Lango Cultural Institution'}
        </AppText>
        <AppText variant="eyebrow" style={{ marginTop: 4 }}>
          {a?.subtitle ?? 'Won Nyaci me Lango'}
        </AppText>
      </View>
      <StateView loading={loading && !a} error={error} onRetry={retry}>
        {a ? (
          <>
            <AppText style={{ marginBottom: SPACING.md }}>{t('The Lango App puts Lango heritage, language and clan records in your pocket: discover your heritage, register your clan, contribute cultural and natural observations, learn Leb Lango and connect with the global Lango community.')}</AppText>
            {a.mission ? (
              <View style={{ marginBottom: SPACING.md }}>
                <SectionHeader title={t('Our mission')} />
                <AppText>{a.mission}</AppText>
              </View>
            ) : null}
            {a.vision ? (
              <View style={{ marginBottom: SPACING.md }}>
                <SectionHeader title={t('Our vision')} />
                <AppText>{a.vision}</AppText>
              </View>
            ) : null}
            <SectionHeader title={t('Contact')} />
            {a.address ? <InfoRow icon="location-outline" title={a.address} /> : null}
            {a.email ? <InfoRow icon="mail-outline" title={a.email} onPress={() => Linking.openURL(`mailto:${a.email}`).catch(() => {})} /> : null}
            {a.phone ? <InfoRow icon="call-outline" title={a.phone} onPress={() => Linking.openURL(`tel:${a.phone.replace(/\s+/g, '')}`).catch(() => {})} /> : null}
            {a.website ? <InfoRow icon="globe-outline" title={t('Visit the website')} subtitle={a.website} onPress={() => Linking.openURL(a.website).catch(() => {})} /> : null}
          </>
        ) : null}
      </StateView>
      <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.xl }}>
        {t('Lango App {v} · built by Kakebe Technologies Limited for the Lango Cultural Institution', { v: Constants.expoConfig?.version ?? '' })}
      </AppText>
    </Screen>
  );
}

const styles = StyleSheet.create({
  head: { alignItems: 'center', paddingVertical: SPACING.lg, marginBottom: SPACING.md, backgroundColor: COLORS.paper, borderRadius: 12 },
});
