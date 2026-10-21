// Button: solid red (primary), dark, gold, outlined or light, with an optional icon and a built-in loading spinner.
import { ActivityIndicator, Pressable, StyleSheet } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';
import AppText from './AppText';

const LOOKS = {
  primary: { bg: COLORS.primary, fg: COLORS.white, border: COLORS.primary },
  dark: { bg: COLORS.navy, fg: COLORS.white, border: COLORS.navy },
  gold: { bg: COLORS.yellow, fg: COLORS.navy, border: COLORS.yellow },
  light: { bg: COLORS.white, fg: COLORS.navy, border: COLORS.white },
  outline: { bg: COLORS.white, fg: COLORS.navy, border: COLORS.gray300 },
  ghost: { bg: 'transparent', fg: COLORS.primaryDark, border: 'transparent' },
};

export default function Button({ title, onPress, variant = 'primary', icon, loading = false, disabled = false, small = false, style }) {
  const look = LOOKS[variant] ?? LOOKS.primary;
  const inactive = disabled || loading;

  return (
    <Pressable
      accessibilityRole="button"
      accessibilityState={{ disabled: inactive, busy: loading }}
      disabled={inactive}
      onPress={onPress}
      style={({ pressed }) => [styles.base, small && styles.small, { backgroundColor: look.bg, borderColor: look.border }, inactive && styles.inactive, pressed && { opacity: 0.85 }, style]}
    >
      {loading ? (
        <ActivityIndicator color={look.fg} />
      ) : (
        <>
          {icon ? <Ionicons name={icon} size={small ? 16 : 18} color={look.fg} /> : null}
          <AppText style={{ fontFamily: FONTS.semibold, fontSize: small ? 13 : 15, color: look.fg }}>{title}</AppText>
        </>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: { minHeight: 50, paddingHorizontal: SPACING.lg, borderRadius: RADIUS.md, borderWidth: 1.5, flexDirection: 'row', gap: 8, alignItems: 'center', justifyContent: 'center' },
  small: { minHeight: 38, paddingHorizontal: SPACING.md },
  inactive: { opacity: 0.55 },
});
