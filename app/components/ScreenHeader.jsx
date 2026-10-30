// Top bar for screens that sit above the tabs: a round white back button on the left, the title centred, and an optional action on the
// right. `goBack` falls back to the home tab when there is nothing to go back to (for instance after opening from a notification).
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS, FONTS, RADIUS, SHADOW_SM, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import AppText from './AppText';

export function goBack() {
  if (router.canGoBack()) router.back();
  else router.replace('/(tabs)');
}

export function BackButton({ onDark = false, onPress = goBack, icon = 'chevron-back' }) {
  const { t } = useT();

  return (
    <Pressable onPress={onPress} hitSlop={8} accessibilityRole="button" accessibilityLabel={t('Go back')} style={({ pressed }) => [styles.round, onDark ? styles.onDark : styles.onLight, pressed && { opacity: 0.8 }]}>
      <Ionicons name={icon} size={22} color={onDark ? COLORS.white : COLORS.black} />
    </Pressable>
  );
}

/** Round icon button for the right side of a header. */
export function HeaderAction({ icon, onPress, label, badge = 0, onDark = false }) {
  return (
    <Pressable onPress={onPress} hitSlop={8} accessibilityRole="button" accessibilityLabel={label} style={({ pressed }) => [styles.round, onDark ? styles.onDark : styles.onLight, pressed && { opacity: 0.8 }]}>
      <Ionicons name={icon} size={21} color={onDark ? COLORS.white : COLORS.black} />
      {badge > 0 ? (
        <View style={styles.badge}>
          <AppText allowFontScaling={false} style={styles.badgeText}>
            {badge > 9 ? '9+' : badge}
          </AppText>
        </View>
      ) : null}
    </Pressable>
  );
}

export default function ScreenHeader({ title, subtitle, right }) {
  const insets = useSafeAreaInsets();

  return (
    <View style={[styles.bar, { paddingTop: insets.top + 8 }]}>
      <BackButton />
      <View style={[styles.titles, { top: insets.top + 8 }]} pointerEvents="none">
        {title ? (
          <AppText style={styles.title} numberOfLines={1}>
            {title}
          </AppText>
        ) : null}
        {subtitle ? (
          <AppText variant="caption" numberOfLines={1} style={{ textAlign: 'center' }}>
            {subtitle}
          </AppText>
        ) : null}
      </View>
      <View style={styles.right}>{right}</View>
    </View>
  );
}

const styles = StyleSheet.create({
  bar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: SPACING.md, paddingBottom: 10, backgroundColor: COLORS.canvas },
  titles: { position: 'absolute', left: 68, right: 68, height: 44, alignItems: 'center', justifyContent: 'center' },
  title: { fontFamily: FONTS.bold, fontSize: 18, lineHeight: 24, color: COLORS.black, textAlign: 'center' },
  right: { minWidth: 44, alignItems: 'flex-end' },
  round: { width: 44, height: 44, borderRadius: RADIUS.full, alignItems: 'center', justifyContent: 'center' },
  onLight: { backgroundColor: COLORS.white, ...SHADOW_SM },
  onDark: { backgroundColor: 'rgba(255,255,255,0.18)', borderWidth: 1, borderColor: 'rgba(255,255,255,0.3)' },
  badge: { position: 'absolute', top: -2, right: -2, minWidth: 18, height: 18, borderRadius: 9, paddingHorizontal: 4, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center', borderWidth: 2, borderColor: COLORS.canvas },
  badgeText: { fontFamily: FONTS.bold, fontSize: 10, color: COLORS.white },
});
