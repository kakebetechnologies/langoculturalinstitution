// One notable Lango (GET /api/v1/people/{slug}): who they were, the years, and their story in full.
import { StyleSheet, View } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Avatar from '../../components/Avatar';
import Button from '../../components/Button';
import DetailScreen from '../../components/DetailScreen';
import Pill from '../../components/Pill';
import RichText from '../../components/RichText';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

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
          ) : null}
          {p.place ? <Pill label={p.place} icon="location" tone="sky" style={{ marginBottom: SPACING.md }} /> : null}
          <RichText html={p.body_html} />
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
  cta: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1, borderColor: COLORS.line, padding: SPACING.md, marginTop: SPACING.lg },
});
