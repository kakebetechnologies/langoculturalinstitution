// Lango brand tokens for the app: the official logo colours (black, red, yellow, sky blue, cream) in the FaithFellows layout language —
// a soft canvas, white rounded cards with a gentle shadow, pill buttons, deep gradient headers and vivid feature tiles.
// Fonts: Clarity City for the interface, Inter wherever Leb Lango letters (ɛ ɔ ŋ) must show: Clarity City has no glyphs for them, so
// AppText swaps to Inter for dictionary content and whenever the interface itself is in Leb Lango.

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
  paper2: '#F2EEE3',
  black: '#1B1E25',
  ink: '#2B2E36',
  gray900: '#575C66',
  gray700: '#7B8390',
  gray500: '#A9AFB9',
  gray300: '#D9DDE3',
  gray100: '#F0F1F4',
  white: '#FFFFFF',
  onColor: '#FFFFFF',
  success: '#1E9E5A',
  successMist: '#E3F6EC',
  warning: '#E8A317',
  warningMist: '#FFF3E0',
  danger: '#D63031',
  dangerMist: '#FDECEC',
  // Screen background behind the white cards, and the thin lines inside them.
  canvas: '#F7F5F0',
  line: '#ECE9E1',
  track: '#EAE6DB',
};

// The deep header gradient (logo black into a dark red) and the feature tile gradients. Every tile takes white text.
export const GRADIENTS = {
  deep: ['#14161B', '#2B1015', '#7A0F18'],
  red: ['#F23A44', '#B30E18'],
  dark: ['#3A3F4A', '#14161B'],
  sky: ['#22B8F2', '#0679A8'],
  amber: ['#F0A91B', '#B46B00'],
  green: ['#2DB36F', '#0F6B3C'],
  slate: ['#5B6676', '#2B313C'],
};

export const FONTS = {
  regular: 'ClarityCity_400Regular',
  medium: 'ClarityCity_500Medium',
  semibold: 'ClarityCity_600SemiBold',
  bold: 'ClarityCity_700Bold',
  // Headlines: the same bold face as the FaithFellows app. (Kept under the old names so every screen follows.)
  serif: 'ClarityCity_700Bold',
  serifSemi: 'ClarityCity_600SemiBold',
};

// Where a Clarity City name must become Inter (Leb Lango text): same weight, same slot.
export const INTER_FOR = {
  ClarityCity_400Regular: 'Inter_400Regular',
  ClarityCity_500Medium: 'Inter_500Medium',
  ClarityCity_600SemiBold: 'Inter_600SemiBold',
  ClarityCity_700Bold: 'Inter_700Bold',
};

// A phone set to a large font still grows our words, but never past this multiple, so nothing is cut off.
export const MAX_FONT_SCALE = 1.1;

export const SPACING = { xs: 4, sm: 8, md: 16, lg: 24, xl: 32, xxl: 48 };

export const RADIUS = { sm: 6, md: 10, lg: 16, xl: 24, full: 9999 };

export const SHADOW = {
  shadowColor: '#16181D',
  shadowOpacity: 0.08,
  shadowRadius: 14,
  shadowOffset: { width: 0, height: 5 },
  elevation: 3,
};
