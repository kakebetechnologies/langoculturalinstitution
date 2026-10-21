// Lango brand tokens for the app: colours from the official logo (black, red, yellow, sky blue, cream), fonts, spacing, radii, shadow.
// Mirrors the website (web/assets/css/site.css): Inter for the interface, Source Serif 4 for headlines, flat cards, 8px-radius buttons.

export const COLORS = {
  navy: '#16181D',
  navy2: '#262A32',
  primary: '#EE1A26',
  primaryDark: '#C9111C',
  primaryMist: '#FDE8E9',
  yellow: '#FDDD16',
  yellowMist: '#FFF6C4',
  yellowInk: '#7D6200',
  sky: '#00ADF1',
  skyDark: '#0786B8',
  skyMist: '#E3F6FE',
  paper: '#FBF8EC',
  paper2: '#F4EFD9',
  black: '#1B1E25',
  gray900: '#4F5662',
  gray700: '#7B8390',
  gray500: '#A9AFB9',
  gray300: '#D9DDE3',
  gray100: '#EEF0F3',
  white: '#FFFFFF',
  success: '#1E9E5A',
  warning: '#E8A317',
  danger: '#D63031',
  // Screen background behind the white cards.
  canvas: '#F7F5EE',
  line: '#E5E7EB',
};

export const FONTS = {
  regular: 'Inter_400Regular',
  medium: 'Inter_500Medium',
  semibold: 'Inter_600SemiBold',
  bold: 'Inter_700Bold',
  serif: 'SourceSerif4_700Bold',
  serifSemi: 'SourceSerif4_600SemiBold',
};

// A phone set to a large font still grows our words, but never past this multiple, so nothing is cut off.
export const MAX_FONT_SCALE = 1.15;

export const SPACING = { xs: 4, sm: 8, md: 16, lg: 24, xl: 32, xxl: 48 };

export const RADIUS = { sm: 6, md: 8, lg: 12, xl: 20, full: 9999 };

export const SHADOW = {
  shadowColor: '#16181D',
  shadowOpacity: 0.06,
  shadowRadius: 12,
  shadowOffset: { width: 0, height: 4 },
  elevation: 2,
};
