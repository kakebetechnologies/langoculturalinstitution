// A feature card for the Explore, Learn and Contribute grids: white with a soft shadow, a coloured icon on a light tint, the title and a
// line under it, and a small arrow. `tone` picks the colour (see TONES); `full` makes it span the whole row. (The file keeps its old name.)
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, RADIUS, SHADOW, TONES } from '../constants/theme';
import AppText from './AppText';

export default function GradientTile({ icon, title, text, tone = 'red', onPress, full = false, badge = 0, style }) {
  const c = TONES[tone] ?? TONES.red;

  return (
    <Pressable onPress={onPress} accessibilityRole="button" accessibilityLabel={title} style={({ pressed }) => [styles.wrap, full && styles.full, style, pressed && { opacity: 0.92, transform: [{ scale: 0.985 }] }]}>
      <View style={styles.top}>
        <View style={[styles.badge, { backgroundColor: c.bg }]}>
          <Ionicons name={icon} size={24} color={c.fg} />
          {badge > 0 ? (
            <View style={styles.dot}>
              <AppText allowFontScaling={false} style={styles.dotText}>
                {badge > 9 ? '9+' : badge}
              </AppText>
            </View>
          ) : null}
        </View>
        <View style={[styles.arrow, { backgroundColor: c.bg }]}>
          <Ionicons name="arrow-forward" size={14} color={c.fg} />
        </View>
      </View>
      <View style={{ marginTop: 'auto', paddingTop: 12 }}>
        <AppText style={styles.title} numberOfLines={2}>
          {title}
        </AppText>
        {text ? (
          <AppText style={styles.text} numberOfLines={2}>
            {text}
          </AppText>
        ) : null}
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  wrap: { width: '48%', flexGrow: 1, minHeight: 142, borderRadius: RADIUS.lg + 2, padding: 14, backgroundColor: COLORS.white, ...SHADOW },
  full: { width: '100%', minHeight: 0 },
  top: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between' },
  badge: { width: 48, height: 48, borderRadius: 16, alignItems: 'center', justifyContent: 'center' },
  arrow: { width: 28, height: 28, borderRadius: 14, alignItems: 'center', justifyContent: 'center' },
  dot: { position: 'absolute', top: -5, right: -5, minWidth: 20, height: 20, borderRadius: 10, paddingHorizontal: 5, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center', borderWidth: 2, borderColor: COLORS.white },
  dotText: { fontFamily: FONTS.bold, fontSize: 11, color: COLORS.white },
  title: { fontFamily: FONTS.bold, fontSize: 15.5, lineHeight: 20, color: COLORS.black },
  text: { fontFamily: FONTS.regular, fontSize: 12, lineHeight: 16.5, color: COLORS.gray700, marginTop: 2 },
});
