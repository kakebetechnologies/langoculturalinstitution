// A white soft-shadow tile with a coloured icon on a light tint and its name underneath: the shortcut grids on Home (four on a row).
// `badge` shows a small count.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, RADIUS, SHADOW, TONES } from '../constants/theme';
import AppText from './AppText';

export default function IconTile({ icon, label, tone = 'red', onPress, badge = 0 }) {
  const c = TONES[tone] ?? TONES.red;

  return (
    <Pressable onPress={onPress} accessibilityRole="button" accessibilityLabel={label} style={({ pressed }) => [styles.wrap, pressed && { opacity: 0.88, transform: [{ scale: 0.97 }] }]}>
      <View style={styles.box}>
        <View style={[styles.icon, { backgroundColor: c.bg }]}>
          <Ionicons name={icon} size={22} color={c.fg} />
          {badge > 0 ? (
            <View style={styles.dot}>
              <AppText allowFontScaling={false} style={styles.dotText}>
                {badge > 9 ? '9+' : badge}
              </AppText>
            </View>
          ) : null}
        </View>
        <AppText numberOfLines={1} style={styles.label}>
          {label}
        </AppText>
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  wrap: { width: '25%', paddingHorizontal: 4, marginBottom: 8 },
  box: { alignItems: 'center', justifyContent: 'center', gap: 8, height: 90, borderRadius: RADIUS.lg, backgroundColor: COLORS.white, borderWidth: 1, borderColor: '#FFFFFF', ...SHADOW, shadowOpacity: 0.06, shadowRadius: 12, shadowOffset: { width: 0, height: 5 } },
  icon: { width: 40, height: 40, borderRadius: 14, alignItems: 'center', justifyContent: 'center' },
  dot: { position: 'absolute', top: -5, right: -5, minWidth: 18, height: 18, borderRadius: 9, paddingHorizontal: 4, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center', borderWidth: 2, borderColor: COLORS.white },
  dotText: { fontFamily: FONTS.bold, fontSize: 10, color: COLORS.white },
  label: { fontFamily: FONTS.semibold, fontSize: 11.5, color: COLORS.black, paddingHorizontal: 2 },
});
