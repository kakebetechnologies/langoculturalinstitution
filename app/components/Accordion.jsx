// An expandable section (the History chapters): number, era, title and summary on a tap-to-open bar; the body shows below.
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../constants/theme';
import AppText from './AppText';

export default function Accordion({ number, eyebrow, title, summary, defaultOpen = false, children }) {
  const [open, setOpen] = useState(defaultOpen);

  return (
    <View style={styles.box}>
      <Pressable onPress={() => setOpen((o) => !o)} accessibilityRole="button" accessibilityState={{ expanded: open }} style={styles.head}>
        {number ? <AppText style={styles.num}>{number}</AppText> : null}
        <View style={{ flex: 1 }}>
          {eyebrow ? <AppText variant="eyebrow" style={{ color: COLORS.skyDark }}>{eyebrow}</AppText> : null}
          <AppText style={styles.title}>{title}</AppText>
          {summary && !open ? <AppText variant="caption" numberOfLines={2} style={{ marginTop: 2 }}>{summary}</AppText> : null}
        </View>
        <Ionicons name={open ? 'chevron-up' : 'chevron-down'} size={20} color={COLORS.gray700} />
      </Pressable>
      {open ? <View style={styles.body}>{children}</View> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  box: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1, borderColor: COLORS.line, marginBottom: 10, overflow: 'hidden', ...SHADOW },
  head: { flexDirection: 'row', alignItems: 'center', gap: 12, padding: SPACING.md },
  num: { fontFamily: FONTS.serif, fontSize: 22, color: COLORS.primaryDark, minWidth: 30 },
  title: { fontFamily: FONTS.serif, fontSize: 17, lineHeight: 23, color: COLORS.black },
  body: { paddingHorizontal: SPACING.md, paddingBottom: SPACING.md, borderTopWidth: 1, borderTopColor: COLORS.line, paddingTop: SPACING.md },
});
