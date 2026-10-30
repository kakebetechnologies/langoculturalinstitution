// One book of the Lango Bible (GET /api/v1/bible/{book}): the numbers of its chapters that have text, as a grid. Tap a number to read.
import { Pressable, StyleSheet, View } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../../components/AppText';
import Screen from '../../../components/Screen';
import StateView from '../../../components/StateView';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../../../constants/theme';
import { useT } from '../../../lib/i18n';
import { useFetch } from '../../../lib/useFetch';

export default function BibleBook() {
  const { t } = useT();
  const { book } = useLocalSearchParams();
  const { data, loading, error, retry, reload, refreshing } = useFetch(`/bible/${book}`);
  const b = data?.data?.book;
  const chapters = data?.data?.available ?? [];

  return (
    <Screen title={b?.name ?? ''} subtitle={b && b.name_en !== b.name ? b.name_en : undefined} onRefresh={reload} refreshing={refreshing}>
      <StateView loading={loading} error={error} onRetry={retry} empty={!!data && !chapters.length} emptyIcon="book-outline" emptyTitle="Nothing to read here yet" emptyMessage="This book has no text yet.">
        <AppText variant="label" style={{ marginBottom: SPACING.sm }}>
          {t('Choose a chapter')}
        </AppText>
        <View style={styles.grid}>
          {chapters.map((n) => (
            <Pressable key={n} onPress={() => router.push(`/bible/${book}/${n}`)} accessibilityRole="button" accessibilityLabel={`${b?.name ?? ''} ${n}`} style={({ pressed }) => [styles.cell, pressed && { opacity: 0.8 }]}>
              <AppText style={styles.num}>{n}</AppText>
            </Pressable>
          ))}
        </View>
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  cell: { width: 56, height: 56, borderRadius: RADIUS.lg, backgroundColor: COLORS.white, alignItems: 'center', justifyContent: 'center', ...SHADOW },
  num: { fontFamily: FONTS.bold, fontSize: 17, color: COLORS.black },
});
