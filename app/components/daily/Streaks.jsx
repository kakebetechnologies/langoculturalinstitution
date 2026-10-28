// Streaks: the flame chip on Home, the "Your streak" card on Me, and the celebration shown when a milestone is reached.
// A streak counts days in a row on which the member opened the app; one missed day is forgiven once every seven days (the "streak freeze").
import { StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import AppText from '../AppText';
import Button from '../Button';
import Card from '../Card';
import Sheet from '../Sheet';
import { COLORS, FONTS, SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';

export function StreakChip({ streaks }) {
  const { t } = useT();
  if (!streaks || !streaks.current) return null;

  return (
    <View style={styles.chip} accessibilityLabel={t('{n}-day streak', { n: streaks.current })}>
      <Ionicons name="flame" size={16} color={COLORS.yellow} />
      <AppText style={styles.chipText}>{streaks.current}</AppText>
    </View>
  );
}

export function StreakCard({ streaks, style }) {
  const { t } = useT();
  if (!streaks) return null;
  const next = streaks.next_milestone;
  const pct = next ? Math.min(1, streaks.current / next) : 1;

  return (
    <Card style={style}>
      <View style={styles.top}>
        <View style={styles.flame}>
          <Ionicons name="flame" size={28} color={COLORS.white} />
        </View>
        <View style={{ flex: 1 }}>
          <AppText style={styles.big}>{streaks.current === 1 ? t('1 day in a row') : t('{n} days in a row', { n: streaks.current })}</AppText>
          <AppText variant="caption">{t('Best: {n} · Words learned: {w} days in a row', { n: streaks.best, w: streaks.words })}</AppText>
        </View>
      </View>
      {next ? (
        <View style={{ marginTop: SPACING.md }}>
          <View style={styles.bar}>
            <View style={[styles.fill, { width: `${Math.round(pct * 100)}%` }]} />
          </View>
          <AppText variant="caption" style={{ marginTop: 6 }}>
            {t('{n} more days to reach {m}', { n: next - streaks.current, m: next })}
          </AppText>
        </View>
      ) : null}
      <AppText variant="caption" style={{ marginTop: SPACING.sm }}>
        {streaks.freeze_available ? t('Streak freeze ready: one missed day will not break your streak.') : t('Streak freeze used recently: it comes back after six more days.')}
      </AppText>
    </Card>
  );
}

export function MilestoneSheet({ days, onClose }) {
  const { t } = useT();

  return (
    <Sheet visible={!!days} title={t('Keep it going!')} onClose={onClose}>
      <View style={{ alignItems: 'center', paddingVertical: SPACING.md }}>
        <View style={[styles.flame, { width: 84, height: 84, borderRadius: 42 }]}>
          <Ionicons name="flame" size={44} color={COLORS.white} />
        </View>
        <AppText style={[styles.big, { fontSize: 26, marginTop: SPACING.md }]}>{t('{n}-day streak', { n: days })}</AppText>
        <AppText style={{ textAlign: 'center', marginTop: 6 }}>{t('You have opened the Lango App {n} days in a row. Apwoyo matek!', { n: days })}</AppText>
        <Button title={t('Continue')} onPress={onClose} style={{ marginTop: SPACING.lg, alignSelf: 'stretch' }} />
      </View>
    </Sheet>
  );
}

const styles = StyleSheet.create({
  chip: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: 'rgba(255,255,255,0.14)', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 999 },
  chipText: { fontFamily: FONTS.bold, fontSize: 14, color: COLORS.white },
  top: { flexDirection: 'row', alignItems: 'center', gap: 14 },
  flame: { width: 52, height: 52, borderRadius: 18, backgroundColor: '#F26A1B', alignItems: 'center', justifyContent: 'center' },
  big: { fontFamily: FONTS.bold, fontSize: 19, lineHeight: 25, color: COLORS.black },
  bar: { height: 8, backgroundColor: COLORS.track, borderRadius: 4, overflow: 'hidden' },
  fill: { height: 8, backgroundColor: '#F26A1B', borderRadius: 4 },
});
