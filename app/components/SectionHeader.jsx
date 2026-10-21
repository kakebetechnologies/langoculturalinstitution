// Heading above a section of a screen, with an optional "See all" link on the right.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, SPACING } from '../constants/theme';
import AppText from './AppText';

export default function SectionHeader({ title, eyebrow, actionLabel, onAction, style }) {
  return (
    <View style={[styles.row, style]}>
      <View style={{ flex: 1 }}>
        {eyebrow ? <AppText variant="eyebrow">{eyebrow}</AppText> : null}
        <AppText variant="heading">{title}</AppText>
      </View>
      {actionLabel ? (
        <Pressable onPress={onAction} hitSlop={8} accessibilityRole="button" style={styles.action}>
          <AppText variant="label" style={{ color: COLORS.primaryDark }}>
            {actionLabel}
          </AppText>
          <Ionicons name="chevron-forward" size={14} color={COLORS.primaryDark} />
        </Pressable>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'flex-end', justifyContent: 'space-between', marginBottom: SPACING.sm + 4, gap: 8 },
  action: { flexDirection: 'row', alignItems: 'center', gap: 2, paddingBottom: 3 },
});
