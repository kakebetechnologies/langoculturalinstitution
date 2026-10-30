// A settings-style list: a small heading, then one white rounded card whose rows are separated by hairlines. MenuRow is one row
// (a coloured icon on a light tint, a title, an optional second line and a chevron). Used on Me and wherever a list of links reads better as one block.
import { Children, cloneElement } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING, TONES } from '../constants/theme';
import AppText from './AppText';

export function MenuRow({ icon, tone = 'slate', title, subtitle, onPress, last = false }) {
  const c = TONES[tone] ?? TONES.slate;

  return (
    <Pressable onPress={onPress} accessibilityRole="button" style={({ pressed }) => [styles.row, !last && styles.divider, pressed && { backgroundColor: COLORS.canvas }]}>
      <View style={[styles.icon, { backgroundColor: c.bg }]}>
        <Ionicons name={icon} size={20} color={c.fg} />
      </View>
      <View style={{ flex: 1 }}>
        <AppText style={[styles.title, tone === 'red' && { color: COLORS.primaryDark }]} numberOfLines={1}>
          {title}
        </AppText>
        {subtitle ? (
          <AppText variant="caption" numberOfLines={1}>
            {subtitle}
          </AppText>
        ) : null}
      </View>
      <Ionicons name="chevron-forward" size={18} color={COLORS.gray500} />
    </Pressable>
  );
}

export default function MenuGroup({ title, children, style }) {
  const rows = Children.toArray(children);

  return (
    <View style={style}>
      {title ? <AppText style={styles.heading}>{title}</AppText> : null}
      <View style={styles.card}>{rows.map((r, i) => cloneElement(r, { last: i === rows.length - 1 }))}</View>
    </View>
  );
}

const styles = StyleSheet.create({
  heading: { fontFamily: FONTS.bold, fontSize: 19, lineHeight: 25, color: COLORS.black, marginBottom: SPACING.sm + 2 },
  card: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg + 2, overflow: 'hidden', ...SHADOW },
  row: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 12, paddingHorizontal: 14 },
  divider: { borderBottomWidth: 1, borderBottomColor: COLORS.line },
  icon: { width: 40, height: 40, borderRadius: 13, alignItems: 'center', justifyContent: 'center' },
  title: { fontFamily: FONTS.semibold, fontSize: 15, lineHeight: 20, color: COLORS.black },
});
