// A small pill you tap to pick an option or filter: white, and the chosen one turns the Lango yellow.
import { Pressable, StyleSheet } from 'react-native';
import { COLORS, FONTS, RADIUS, SHADOW_SM } from '../constants/theme';
import AppText from './AppText';

export default function Chip({ label, on = false, onPress, style }) {
  return (
    <Pressable onPress={onPress} style={[styles.chip, on && styles.on, style]} accessibilityRole="button" accessibilityState={{ selected: on }}>
      <AppText style={[styles.text, on && { color: COLORS.navy }]}>{label}</AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  chip: { paddingHorizontal: 16, paddingVertical: 9, borderRadius: RADIUS.full, backgroundColor: COLORS.white, borderWidth: 1, borderColor: '#E6E8EC', ...SHADOW_SM, shadowOpacity: 0.04 },
  on: { backgroundColor: COLORS.yellow, borderColor: COLORS.yellow, ...SHADOW_SM, shadowOpacity: 0.12 },
  text: { fontFamily: FONTS.semibold, fontSize: 13, color: COLORS.gray900 },
});
