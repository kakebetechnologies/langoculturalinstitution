// A thin banner shown while contributions are waiting for internet: "2 contributions will be sent when you are online". Tap to try now.
import { ActivityIndicator, Pressable, StyleSheet } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { flush, useOutbox } from '../lib/outbox';
import AppText from './AppText';

export default function SyncBanner() {
  const { t } = useT();
  const { pending, flushing } = useOutbox();
  if (!pending) return null;

  return (
    <Pressable onPress={() => flush()} style={styles.bar} accessibilityRole="button" accessibilityLabel={t('Send waiting contributions now')}>
      {flushing ? <ActivityIndicator size="small" color={COLORS.navy} /> : <Ionicons name="cloud-upload-outline" size={18} color={COLORS.navy} />}
      <AppText variant="label" style={{ flex: 1 }}>
        {pending === 1 ? t('1 contribution will be sent when you are online.') : t('{n} contributions will be sent when you are online.', { n: pending })}
      </AppText>
      <AppText variant="label" style={{ color: COLORS.primaryDark }}>
        {t('Try now')}
      </AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  bar: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.yellowMist, borderBottomWidth: 1, borderBottomColor: '#F1E39A', paddingHorizontal: SPACING.md, paddingVertical: 10 },
});
