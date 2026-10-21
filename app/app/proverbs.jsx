// Proverbs & sayings (GET /api/v1/proverbs): only sayings that came from Lango speakers and were reviewed by LCI's language team.
// While the collection is empty the screen explains that, and invites an elder or speaker to add one.
import { StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Pill from '../components/Pill';
import Screen from '../components/Screen';
import StateView from '../components/StateView';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function Proverbs() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/proverbs');
  const rows = data?.data ?? [];
  const add = () => router.push({ pathname: '/contribute/new', params: { type: 'language', kind: 'proverb' } });

  return (
    <Screen title={t('Proverbs & sayings')} onRefresh={reload} refreshing={refreshing}>
      <StateView loading={loading} error={error} onRetry={retry}>
        {rows.length ? (
          <>
            {rows.map((p) => (
              <View key={p.id} style={styles.card}>
                <AppText style={styles.quote}>“</AppText>
                <AppText style={styles.text} selectable>
                  {p.lango}
                </AppText>
                {p.english ? (
                  <AppText style={{ marginTop: 8 }}>
                    <AppText style={{ fontFamily: FONTS.bold }}>{t('In English')}: </AppText>
                    {p.english}
                  </AppText>
                ) : null}
                {p.meaning ? (
                  <AppText style={{ marginTop: 4 }}>
                    <AppText style={{ fontFamily: FONTS.bold }}>{t('Meaning')}: </AppText>
                    {p.meaning}
                  </AppText>
                ) : null}
                <View style={{ flexDirection: 'row', gap: 8, marginTop: 10, flexWrap: 'wrap' }}>
                  {p.verified ? <Pill label={t('Checked')} icon="checkmark" tone="sky" /> : null}
                  {p.credit ? <Pill label={p.credit} tone="grey" /> : null}
                </View>
              </View>
            ))}
            <Button title={t('Add a proverb')} icon="add" onPress={add} style={{ marginTop: SPACING.md }} />
          </>
        ) : (
          <View style={styles.begin}>
            <AppText variant="eyebrow">{t('A collection waiting for its elders')}</AppText>
            <AppText variant="title" style={{ marginTop: 4 }}>
              {t('Help us write down the proverbs of Lango')}
            </AppText>
            <AppText style={{ marginTop: 10 }}>{t('Proverbs are how the Lango teach children, settle disputes and speak about life. We would rather publish nothing than publish one that is wrong, so this list only shows sayings that come from Lango speakers and have been reviewed by the LCI language team.')}</AppText>
            <View style={{ marginTop: 12, gap: 6 }}>
              {['Write the proverb in Leb Lango, exactly as you heard it.', 'Add its English meaning and when it is used.', 'Choose your clan so the saying is credited to where it comes from.'].map((s, i) => (
                <AppText key={s}>
                  {i + 1}. {t(s)}
                </AppText>
              ))}
            </View>
            <Button title={t('Add a proverb')} icon="add" onPress={add} style={{ marginTop: SPACING.lg }} />
          </View>
        )}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1, borderColor: COLORS.line, padding: SPACING.md + 2, paddingTop: 30, marginBottom: 12 },
  quote: { position: 'absolute', top: -4, left: 14, fontFamily: FONTS.serif, fontSize: 56, lineHeight: 64, color: COLORS.yellow },
  text: { fontFamily: FONTS.serifSemi, fontSize: 18, lineHeight: 26, color: COLORS.black },
  begin: { backgroundColor: COLORS.paper, borderRadius: RADIUS.xl, borderWidth: 1, borderColor: '#EFE7BF', padding: SPACING.lg },
});
