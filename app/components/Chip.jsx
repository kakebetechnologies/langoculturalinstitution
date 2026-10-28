// A small pill you tap to pick an option or filter (selected = filled dark).
import { Pressable, StyleSheet } from 'react-native';
import { COLORS, FONTS, RADIUS, SHADOW } from '../constants/theme';
import AppText from './AppText';

export default function Chip({ label, on = false, onPress, style }) {
  return (
    <Pressable onPress={onPress} style={[styles.chip, on && styles.on, style]} accessibilityRole="button" accessibilityState={{ selected: on }}>
      <AppText style={[styles.text, on && { color: COLORS.white }]}>{label}</AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  chip: { paddingHorizontal: 15, paddingVertical: 9, borderRadius: RADIUS.full, backgroundColor: COLORS.white, ...SHADOW, shadowOpacity: 0.05, shadowRadius: 8, shadowOffset: { width: 0, height: 2 }, elevation: 1 },
  on: { backgroundColor: COLORS.navy },
  text: { fontFamily: FONTS.semibold, fontSize: 13, color: COLORS.black },
});
