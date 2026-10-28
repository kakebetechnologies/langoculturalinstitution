// Button: a pill in solid red (primary), dark, gold, outlined, light or text-only, with an optional icon and a built-in loading spinner.
import { ActivityIndicator, Pressable, StyleSheet } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';
import AppText from './AppText';

const LOOKS = {
  primary: { bg: COLORS.primary, fg: COLORS.white, border: COLORS.primary },
  dark: { bg: COLORS.navy, fg: COLORS.white, border: COLORS.navy },
  gold: { bg: COLORS.yellow, fg: COLORS.navy, border: COLORS.yellow },
  light: { bg: COLORS.white, fg: COLORS.navy, border: COLORS.white },
  glass: { bg: 'rgba(255,255,255,0.14)', fg: COLORS.white, border: 'rgba(255,255,255,0.45)' },
  outline: { bg: 'transparent', fg: COLORS.primaryDark, border: COLORS.primary },
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
  base: { minHeight: 52, paddingHorizontal: SPACING.lg, borderRadius: RADIUS.full, borderWidth: 1.5, flexDirection: 'row', gap: 8, alignItems: 'center', justifyContent: 'center' },
  small: { minHeight: 38, paddingHorizontal: SPACING.md },
  inactive: { opacity: 0.55 },
});
