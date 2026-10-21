// A row of 2–4 connected options (Both / Leb Lango / English): the active one is filled dark.
import { Pressable, StyleSheet, View } from 'react-native';
import { COLORS, FONTS, RADIUS } from '../constants/theme';
import AppText from './AppText';

export default function Segmented({ options, value, onChange, style }) {
  return (
    <View style={[styles.row, style]} accessibilityRole="tablist">
      {options.map((o) => {
        const on = o.value === value;
        return (
          <Pressable key={o.value} onPress={() => onChange(o.value)} accessibilityRole="tab" accessibilityState={{ selected: on }} style={[styles.item, on && styles.on]}>
            <AppText numberOfLines={1} style={[styles.text, on && { color: COLORS.white }]}>
              {o.label}
            </AppText>
          </Pressable>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', backgroundColor: COLORS.paper2, borderRadius: RADIUS.full, padding: 3 },
  item: { flex: 1, alignItems: 'center', justifyContent: 'center', paddingVertical: 8, paddingHorizontal: 8, borderRadius: RADIUS.full },
  on: { backgroundColor: COLORS.navy },
  text: { fontFamily: FONTS.semibold, fontSize: 13, color: COLORS.black },
});
