// Support LCI: donate, sponsor a program, see how funds are used (transparency totals) and apply for scholarships or grants
// (those forms live on the website and open in the browser).
import { Linking, StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import Row from '../components/Row';
import Screen from '../components/Screen';
import SectionHeader from '../components/SectionHeader';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';
import { SITE_URL } from '../lib/api';
import { money } from '../lib/format';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function Support() {
  const { t } = useT();
  const programs = useFetch('/programs');
  const totals = useFetch('/transparency');
  const rows = (programs.data?.data ?? []).filter((p) => p.accepts_donations).slice(0, 4);
  const tt = totals.data?.data;

  return (
    <Screen title={t('Support LCI')} onRefresh={() => { programs.reload(); totals.reload(); }} refreshing={programs.refreshing}>
      <View style={styles.hero}>
        <AppText variant="eyebrow" style={{ color: COLORS.yellow }}>
          {t('Be part of the story')}
        </AppText>
        <AppText style={styles.title}>{t('Your gift keeps Lango culture, language and heritage alive for the next generation.')}</AppText>
        <Button title={t('Donate now')} variant="gold" icon="heart" onPress={() => router.push('/donate')} style={{ alignSelf: 'flex-start', marginTop: 10 }} />
      </View>

      {tt ? (
        <Card style={{ marginTop: SPACING.md }}>
          <AppText variant="eyebrow">{t('Transparency')}</AppText>
          <AppText variant="subheading" style={{ marginTop: 2 }}>
            {t('{n} confirmed donations', { n: tt.confirmed_donations })}
          </AppText>
          <AppText style={{ fontFamily: FONTS.serif, fontSize: 20, color: COLORS.primaryDark, marginTop: 2 }}>
            {money(tt.ugx)} · {money(tt.usd, 'USD')}
          </AppText>
          <AppText variant="caption">{t('Pledges are confirmed by the LCI finance office before they are counted.')}</AppText>
        </Card>
      ) : null}

      <View style={{ marginTop: SPACING.lg }}>
        <SectionHeader title={t('Sponsor a program')} actionLabel={t('All programs')} onAction={() => router.push('/programs')} />
        {rows.map((p) => (
          <Row key={p.id} image={p.image} icon={p.image ? undefined : 'leaf-outline'} title={p.title} subtitle={p.summary} meta={`${p.progress}%`} onPress={() => router.push(`/programs/${p.slug}`)} />
        ))}
      </View>

      <View style={{ marginTop: SPACING.md }}>
        <SectionHeader title={t('Get involved')} />
        <Row icon="school-outline" iconTone="sky" title={t('Scholarships')} subtitle={t('Apply for LCI education support (opens the website)')} onPress={() => Linking.openURL(`${SITE_URL}/apply/scholarship`).catch(() => {})} />
        <Row icon="document-text-outline" iconTone="gold" title={t('Research grants')} subtitle={t('Students and researchers of Lango heritage')} onPress={() => Linking.openURL(`${SITE_URL}/apply/grant`).catch(() => {})} />
        <Row icon="hand-left-outline" iconTone="red" title={t('Volunteer')} subtitle={t('Tech camps, internships and field work')} onPress={() => Linking.openURL(`${SITE_URL}/apply/volunteer`).catch(() => {})} />
        <Row icon="globe-outline" iconTone="dark" title={t('Diaspora partners')} subtitle={t('Associations abroad, join the network')} onPress={() => Linking.openURL(`${SITE_URL}/diaspora`).catch(() => {})} />
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  hero: { backgroundColor: COLORS.skyDark, borderRadius: RADIUS.lg, padding: SPACING.lg },
  title: { fontFamily: FONTS.serif, fontSize: 21, lineHeight: 28, color: COLORS.white, marginTop: 4 },
});
