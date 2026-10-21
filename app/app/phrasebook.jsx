// Phrasebook: everyday greetings and their replies, from the worked examples in the Practical Lango Grammar
// (GET /api/v1/greetings). Spelling follows the book. Tap a phrase to see it in the translator.
import { router } from 'expo-router';
import { StyleSheet, View } from 'react-native';
import AppText from '../components/AppText';
import Card from '../components/Card';
import Screen from '../components/Screen';
import StateView from '../components/StateView';
import { COLORS, FONTS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function Phrasebook() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/greetings');
  const rows = data?.data ?? [];

  return (
    <Screen title={t('Phrasebook')} onRefresh={reload} refreshing={refreshing}>
      <AppText style={{ marginBottom: SPACING.md }}>{t('Real greetings and replies from the worked examples of the Practical Lango Grammar. Spelling follows the book.')}</AppText>
      <StateView loading={loading} error={error} onRetry={retry} empty={!rows.length} emptyIcon="chatbubbles-outline" emptyTitle="No phrases yet">
        {rows.map((g) => (
          <Card key={g.lango} onPress={() => router.push({ pathname: '/translate', params: { text: g.lango, dir: 'lango-en' } })} style={{ marginBottom: 10 }}>
            <View style={styles.row}>
              <AppText style={styles.lango} selectable>
                {g.lango}
              </AppText>
            </View>
            <AppText style={{ marginTop: 2 }}>{g.english}</AppText>
          </Card>
        ))}
        {rows.length ? (
          <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.md }}>
            {t('Source: {s}', { s: data.source })}
          </AppText>
        ) : null}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center' },
  lango: { fontFamily: FONTS.serif, fontSize: 20, lineHeight: 26, color: COLORS.black },
});
