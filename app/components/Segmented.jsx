// A row of 2–4 connected options (Both / Leb Lango / English) on a soft grey track: the active one is a white pill that sits up a little.
import { Pressable, StyleSheet, View } from 'react-native';
import { COLORS, FONTS, RADIUS, SHADOW_SM } from '../constants/theme';
import AppText from './AppText';

export default function Segmented({ options, value, onChange, style, dark = false }) {
  return (
    <View style={[styles.row, dark && styles.rowDark, style]} accessibilityRole="tablist">
      {options.map((o) => {
        const on = o.value === value;
        return (
          <Pressable key={o.value} onPress={() => onChange(o.value)} accessibilityRole="tab" accessibilityState={{ selected: on }} style={[styles.item, on && styles.on]}>
            <AppText numberOfLines={1} style={[styles.text, dark && { color: 'rgba(255,255,255,0.88)' }, on && { color: COLORS.navy }]}>
              {o.label}
            </AppText>
          </Pressable>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', backgroundColor: '#E9EBEF', borderRadius: RADIUS.full, padding: 4 },
  rowDark: { backgroundColor: 'rgba(255,255,255,0.14)' },
  item: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingVertical: 9, paddingHorizontal: 8, borderRadius: RADIUS.full },
  on: { backgroundColor: COLORS.white, ...SHADOW_SM, shadowOpacity: 0.1 },
  text: { fontFamily: FONTS.semibold, fontSize: 13, color: COLORS.gray900 },
});
