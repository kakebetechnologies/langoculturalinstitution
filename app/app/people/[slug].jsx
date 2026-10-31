// One notable Lango (GET /api/v1/people/{slug}): who they were, the years, and their story in full.
import { Linking, Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Avatar from '../../components/Avatar';
import Button from '../../components/Button';
import DetailScreen from '../../components/DetailScreen';
import Pill from '../../components/Pill';
import RichText from '../../components/RichText';
import { COLORS, FONTS, RADIUS, SPACING, SHADOW } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

// icon and label for each kind of further reading
const LINK_ICONS = { wikipedia: 'globe-outline', article: 'newspaper-outline', profile: 'person-outline', documentary: 'play-circle-outline', record: 'stats-chart-outline', research: 'school-outline' };
const LINK_KINDS = { wikipedia: 'Wikipedia', article: 'Article', profile: 'Profile', documentary: 'Documentary', record: 'Record', research: 'Research' };

export default function Person() {
  const { t } = useT();
  const { slug } = useLocalSearchParams();
  const { data, loading, error, retry } = useFetch(`/people/${slug}`);
  const p = data?.data;
  const years = p?.born ? `${p.born}${p.died ? ' – ' + p.died : ' – ' + t('present')}` : '';

  return (
    <DetailScreen loading={loading} error={error} onRetry={retry} hero={p ? { image: null, eyebrow: t(p.category_label), title: p.name, subtitle: p.title, height: 210, fallback: require('../../assets/welcome/ridge.jpg') } : null}>
      {p ? (
        <>
          <View style={styles.head}>
            <Avatar name={p.short_name} photo={p.photo} size={84} square />
            <View style={{ flex: 1 }}>
              {years ? <AppText variant="eyebrow">{years}</AppText> : null}
              <AppText style={styles.lead}>{p.summary}</AppText>
            </View>
          </View>
          {!p.photo ? (
            <AppText variant="caption" style={{ marginBottom: SPACING.md }}>
              {t('A portrait will be added once a properly licensed photograph is confirmed.')}
            </AppText>
          ) : p.photo_credit ? (
            <Pressable onPress={p.photo_source ? () => Linking.openURL(p.photo_source).catch(() => {}) : undefined} accessibilityRole={p.photo_source ? 'link' : undefined} style={{ marginBottom: SPACING.md }}>
              <AppText variant="caption">
                {p.photo_credit}
                {p.photo_source ? <AppText variant="caption" style={{ color: COLORS.skyDark, textDecorationLine: 'underline' }}>{` ${t('Photo page and licence')}`}</AppText> : null}
              </AppText>
            </Pressable>
          ) : null}
          {p.place ? <Pill label={p.place} icon="location" tone="sky" style={{ marginBottom: SPACING.md }} /> : null}
          <RichText html={p.body_html} />
          {p.links?.length ? (
            <View style={styles.reading}>
              <AppText variant="subheading">{t('Read and watch more')}</AppText>
              {p.links.map((l) => (
                <Pressable key={l.url} onPress={() => Linking.openURL(l.url).catch(() => {})} accessibilityRole="link" style={({ pressed }) => [styles.link, pressed && { opacity: 0.7 }]}>
                  <Ionicons name={LINK_ICONS[l.kind] ?? 'link-outline'} size={20} color={COLORS.skyDark} style={{ marginTop: 2 }} />
                  <View style={{ flex: 1 }}>
                    <AppText style={styles.linkTitle}>{l.title}</AppText>
                    <AppText variant="caption">{[t(LINK_KINDS[l.kind] ?? 'Link'), l.source].filter(Boolean).join(' · ')}</AppText>
                  </View>
                  <Ionicons name="open-outline" size={16} color={COLORS.gray500} style={{ marginTop: 3 }} />
                </Pressable>
              ))}
              <AppText variant="caption" style={{ marginTop: SPACING.sm }}>
                {t('These links open other websites, which are not ours. The story above is written in our own words from these sources; Wikipedia text is available under the Creative Commons Attribution-ShareAlike 4.0 licence.')}
              </AppText>
            </View>
          ) : null}
          <Button small variant="outline" icon="help-circle-outline" title={t('Test what you know: Lango quizzes')} onPress={() => router.push('/quiz')} style={{ alignSelf: 'flex-start', marginTop: SPACING.md }} />
          <View style={styles.cta}>
            <AppText variant="subheading">{t('Know more about them?')}</AppText>
            <AppText variant="caption" style={{ marginVertical: 6 }}>
              {t('Families, elders and historians can add stories, dates and photographs.')}
            </AppText>
            <Button small title={t('Share what you know')} onPress={() => router.push({ pathname: '/contribute/new', params: { type: 'oral_history' } })} style={{ alignSelf: 'flex-start' }} />
          </View>
        </>
      ) : null}
    </DetailScreen>
  );
}

const styles = StyleSheet.create({
  head: { flexDirection: 'row', gap: 14, alignItems: 'center', backgroundColor: COLORS.paper, borderRadius: RADIUS.lg, padding: SPACING.md, marginBottom: SPACING.md },
  lead: { fontFamily: FONTS.serifSemi, fontSize: 16.5, lineHeight: 23, color: COLORS.black, marginTop: 2 },
  reading: { backgroundColor: COLORS.paper, borderRadius: RADIUS.lg, padding: SPACING.md, marginTop: SPACING.lg },
  link: { flexDirection: 'row', gap: 10, alignItems: 'flex-start', paddingVertical: 10, borderTopWidth: StyleSheet.hairlineWidth, borderTopColor: COLORS.gray300 },
  linkTitle: { fontFamily: FONTS.semibold, fontSize: 15.5, lineHeight: 21, color: COLORS.black },
  cta: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg, padding: SPACING.md, marginTop: SPACING.lg, ...SHADOW },
});
