// Small rounded label with an optional icon: the "Verified" / "Pending" / "Community" tags on cards.
// Tones: red, sky, gold, green, dark, grey.
import { StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, RADIUS } from '../constants/theme';
import AppText from './AppText';

const TONES = {
  red: { bg: COLORS.primaryMist, fg: COLORS.primaryDark },
  sky: { bg: COLORS.skyMist, fg: COLORS.skyDark },
  gold: { bg: COLORS.yellowMist, fg: COLORS.yellowInk },
  green: { bg: '#E3F6EC', fg: '#146B3C' },
  dark: { bg: COLORS.navy, fg: COLORS.yellow },
  grey: { bg: COLORS.gray100, fg: COLORS.gray900 },
};

export default function Pill({ label, icon, tone = 'sky', style }) {
  const { bg, fg } = TONES[tone] ?? TONES.sky;

  return (
    <View style={[styles.pill, { backgroundColor: bg }, style]}>
      {icon ? <Ionicons name={icon} size={12} color={fg} /> : null}
      <AppText style={{ fontFamily: FONTS.bold, fontSize: 11, color: fg, letterSpacing: 0.3 }} numberOfLines={1}>
        {label}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  pill: { flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 9, paddingVertical: 4, borderRadius: RADIUS.full, alignSelf: 'flex-start' },
});
