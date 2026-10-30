// Button: a rounded rectangle in solid red (primary, with a soft red glow), dark, yellow (gold), white, outlined or text-only (ghost, or ghostLight on photos), with an
// optional icon and a built-in loading spinner.
import { ActivityIndicator, Pressable, StyleSheet } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, SHADOW_RED, SHADOW_SM, SPACING } from '../constants/theme';
import AppText from './AppText';

const LOOKS = {
  primary: { bg: COLORS.primary, fg: COLORS.white, border: COLORS.primary, shadow: SHADOW_RED },
  dark: { bg: COLORS.navy, fg: COLORS.white, border: COLORS.navy, shadow: SHADOW_SM },
  gold: { bg: COLORS.yellow, fg: COLORS.navy, border: COLORS.yellow, shadow: SHADOW_SM },
  light: { bg: COLORS.white, fg: COLORS.navy, border: COLORS.white, shadow: SHADOW_SM },
  glass: { bg: 'rgba(255,255,255,0.14)', fg: COLORS.white, border: 'rgba(255,255,255,0.45)' },
  outline: { bg: COLORS.white, fg: COLORS.black, border: '#DDE0E6' },
  ghost: { bg: 'transparent', fg: COLORS.primaryDark, border: 'transparent' },
  ghostLight: { bg: 'transparent', fg: COLORS.white, border: 'transparent' },
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
      style={({ pressed }) => [styles.base, small && styles.small, look.shadow, { backgroundColor: look.bg, borderColor: look.border }, inactive && styles.inactive, pressed && { opacity: 0.88, transform: [{ scale: 0.985 }] }, style]}
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
  base: { minHeight: 54, paddingHorizontal: SPACING.lg, borderRadius: 18, borderWidth: 1.5, flexDirection: 'row', gap: 8, alignItems: 'center', justifyContent: 'center' },
  small: { minHeight: 40, paddingHorizontal: SPACING.md, borderRadius: 14 },
  inactive: { opacity: 0.55 },
});
