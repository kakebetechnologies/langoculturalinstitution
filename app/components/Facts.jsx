// A strip of key facts at the top of a detail screen (population, area, created…): big number or word, small label under it.
import { StyleSheet, View } from 'react-native';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../constants/theme';
import AppText from './AppText';

export default function Facts({ items }) {
  const shown = items.filter((i) => i && i.value !== null && i.value !== undefined && i.value !== '');
  if (!shown.length) return null;

  return (
    <View style={styles.box}>
      {shown.map((i) => (
        <View key={i.label} style={styles.cell}>
          <AppText style={styles.value} numberOfLines={1} adjustsFontSizeToFit>
            {i.value}
          </AppText>
          <AppText variant="caption" numberOfLines={2}>
            {i.label}
          </AppText>
        </View>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  box: { flexDirection: 'row', flexWrap: 'wrap', backgroundColor: COLORS.white, borderRadius: RADIUS.lg, overflow: 'hidden', ...SHADOW },
  cell: { width: '50%', flexGrow: 1, padding: SPACING.md - 2, borderRightWidth: 1, borderBottomWidth: 1, borderColor: COLORS.line },
  value: { fontFamily: FONTS.bold, fontSize: 20, lineHeight: 26, color: COLORS.black },
});
