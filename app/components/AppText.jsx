// Text with the Lango typography (Inter for the interface, Source Serif 4 for headlines) applied through simple variants.
// A screen can pass a bigger fontSize; the line height then grows with it unless one is given, so letters are never cut off.
import { StyleSheet, Text } from 'react-native';
import { COLORS, FONTS, MAX_FONT_SCALE } from '../constants/theme';

const VARIANTS = {
  display: { fontFamily: FONTS.serif, fontSize: 32, lineHeight: 38, color: COLORS.black },
  title: { fontFamily: FONTS.serif, fontSize: 26, lineHeight: 32, color: COLORS.black },
  heading: { fontFamily: FONTS.serif, fontSize: 19, lineHeight: 25, color: COLORS.black },
  subheading: { fontFamily: FONTS.semibold, fontSize: 16, lineHeight: 22, color: COLORS.black },
  body: { fontFamily: FONTS.regular, fontSize: 15, lineHeight: 23, color: COLORS.gray900 },
  label: { fontFamily: FONTS.semibold, fontSize: 13, lineHeight: 18, color: COLORS.black },
  caption: { fontFamily: FONTS.regular, fontSize: 12.5, lineHeight: 17, color: COLORS.gray700 },
  eyebrow: { fontFamily: FONTS.semibold, fontSize: 11.5, lineHeight: 16, letterSpacing: 1.2, textTransform: 'uppercase', color: COLORS.primaryDark },
};

export default function AppText({ variant = 'body', style, children, ...rest }) {
  const base = VARIANTS[variant] ?? VARIANTS.body;
  const own = StyleSheet.flatten(style) ?? {};
  const grown = own.fontSize && own.lineHeight === undefined && base.lineHeight < own.fontSize * 1.25 ? { lineHeight: Math.ceil(own.fontSize * 1.3) } : null;

  return (
    <Text style={[base, style, grown]} maxFontSizeMultiplier={MAX_FONT_SCALE} {...rest}>
      {children}
    </Text>
  );
}
