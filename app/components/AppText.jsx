// Text with the Lango typography (Clarity City, like FaithFellows) applied through simple variants.
// Clarity City has no ɛ ɔ ŋ, so the same weight in Inter is used for Leb Lango content (`lango`) and for the whole interface while it is shown in Leb Lango.
// A screen can pass a bigger fontSize; the line height then grows with it unless one is given, so letters are never cut off.
import { StyleSheet, Text } from 'react-native';
import { COLORS, FONTS, INTER_FOR, MAX_FONT_SCALE } from '../constants/theme';
import { useT } from '../lib/i18n';

const VARIANTS = {
  display: { fontFamily: FONTS.bold, fontSize: 30, lineHeight: 37, color: COLORS.black },
  title: { fontFamily: FONTS.bold, fontSize: 25, lineHeight: 31, color: COLORS.black },
  heading: { fontFamily: FONTS.bold, fontSize: 18, lineHeight: 24, color: COLORS.black },
  subheading: { fontFamily: FONTS.semibold, fontSize: 16, lineHeight: 22, color: COLORS.black },
  body: { fontFamily: FONTS.regular, fontSize: 15, lineHeight: 23, color: COLORS.gray900 },
  label: { fontFamily: FONTS.semibold, fontSize: 13, lineHeight: 18, color: COLORS.black },
  caption: { fontFamily: FONTS.regular, fontSize: 12.5, lineHeight: 17, color: COLORS.gray700 },
  eyebrow: { fontFamily: FONTS.bold, fontSize: 11.5, lineHeight: 16, letterSpacing: 1.1, textTransform: 'uppercase', color: COLORS.primaryDark },
};

// Leb Lango letters Clarity City cannot draw: any text that holds one is set in Inter, wherever it appears.
const LANGO_LETTERS = /[ɛɔŋƐƆŊ]/;
const hasLangoLetters = (node) => (typeof node === 'string' ? LANGO_LETTERS.test(node) : Array.isArray(node) ? node.some(hasLangoLetters) : false);

export default function AppText({ variant = 'body', style, lango = false, children, ...rest }) {
  const { lang } = useT();
  const base = VARIANTS[variant] ?? VARIANTS.body;
  const own = StyleSheet.flatten(style) ?? {};
  const grown = own.fontSize && own.lineHeight === undefined && base.lineHeight < own.fontSize * 1.25 ? { lineHeight: Math.ceil(own.fontSize * 1.3) } : null;
  const family = own.fontFamily ?? base.fontFamily;
  const swap = lango || lang === 'lg' || hasLangoLetters(children) ? { fontFamily: INTER_FOR[family] ?? family } : null;

  return (
    <Text style={[base, style, grown, swap]} maxFontSizeMultiplier={MAX_FONT_SCALE} {...rest}>
      {children}
    </Text>
  );
}
