// Heading above a section of a screen, with an optional "See all" link on the right (red text and a small round arrow).
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, SHADOW_SM, SPACING } from '../constants/theme';
import AppText from './AppText';

export default function SectionHeader({ title, eyebrow, actionLabel, onAction, style }) {
  return (
    <View style={[styles.row, style]}>
      <View style={{ flex: 1 }}>
        {eyebrow ? <AppText variant="eyebrow">{eyebrow}</AppText> : null}
        <AppText style={styles.title}>{title}</AppText>
      </View>
      {actionLabel ? (
        <Pressable onPress={onAction} hitSlop={8} accessibilityRole="button" style={styles.action}>
          <AppText style={styles.actionText}>{actionLabel}</AppText>
          <View style={styles.arrow}>
            <Ionicons name="chevron-forward" size={13} color={COLORS.primaryDark} />
          </View>
        </Pressable>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', marginBottom: SPACING.sm + 2, marginTop: SPACING.xs },
  title: { fontFamily: FONTS.bold, fontSize: 19, lineHeight: 25, color: COLORS.black },
  action: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  actionText: { fontFamily: FONTS.semibold, fontSize: 13, color: COLORS.primaryDark },
  arrow: { width: 24, height: 24, borderRadius: 12, backgroundColor: COLORS.white, alignItems: 'center', justifyContent: 'center', ...SHADOW_SM },
});
