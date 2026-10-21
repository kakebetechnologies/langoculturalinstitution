// One program (GET /api/v1/programs/{slug}): its description, progress and funding, and "Fund this program".
import { StyleSheet, View } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import Card from '../../components/Card';
import DetailScreen from '../../components/DetailScreen';
import RichText from '../../components/RichText';
import { COLORS, FONTS, SPACING } from '../../constants/theme';
import { money } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Program() {
  const { t } = useT();
  const { slug } = useLocalSearchParams();
  const { data, loading, error, retry } = useFetch(`/programs/${slug}`);
  const p = data?.data;
  const pct = p ? (p.goal_amount > 0 ? Math.min(100, Math.round((p.raised_amount / p.goal_amount) * 100)) : p.progress) : 0;

  return (
    <DetailScreen loading={loading} error={error} onRetry={retry} hero={p ? { image: p.image, eyebrow: t(p.category_label), title: p.title, subtitle: p.summary, fallback: require('../../assets/welcome/hill.jpg') } : null}>
      {p ? (
        <>
          <Card style={{ marginBottom: SPACING.md }}>
            <AppText variant="label">{t('Progress')}</AppText>
            <View style={styles.bar}>
              <View style={[styles.fill, { width: `${pct}%` }]} />
            </View>
            <AppText style={{ fontFamily: FONTS.semibold, color: COLORS.black }}>
              {pct}% {t('complete')}
            </AppText>
            {p.goal_amount > 0 ? (
              <AppText variant="caption" style={{ marginTop: 4 }}>
                {t('{raised} raised of {goal}', { raised: money(p.raised_amount), goal: money(p.goal_amount) })}
              </AppText>
            ) : null}
          </Card>
          <RichText html={p.body_html} />
          {p.accepts_donations ? <Button title={t('Fund this program')} icon="heart" onPress={() => router.push({ pathname: '/donate', params: { program: String(p.id) } })} style={{ marginTop: SPACING.lg }} /> : null}
        </>
      ) : null}
    </DetailScreen>
  );
}

const styles = StyleSheet.create({
  bar: { height: 8, backgroundColor: COLORS.gray100, borderRadius: 4, overflow: 'hidden', marginVertical: 8 },
  fill: { height: 8, backgroundColor: COLORS.yellow },
});
