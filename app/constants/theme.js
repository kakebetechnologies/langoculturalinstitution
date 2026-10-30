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
  // Screen background behind the white cards, and the thin lines inside them. A cool, light grey so the white cards and the logo
  // colours stand out; `field` is the soft grey that text boxes are filled with.
  canvas: '#F4F5F7',
  line: '#E8EAEE',
  track: '#E5E7EB',
  field: '#EDEFF3',
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

export const RADIUS = { sm: 8, md: 14, lg: 20, xl: 26, full: 9999 };

// Soft, wide shadows: cards and tiles float a little above the grey canvas.
export const SHADOW = {
  shadowColor: '#0F172A',
  shadowOpacity: 0.07,
  shadowRadius: 18,
  shadowOffset: { width: 0, height: 8 },
  elevation: 3,
};

// A smaller version for chips, round buttons and small tiles.
export const SHADOW_SM = {
  shadowColor: '#0F172A',
  shadowOpacity: 0.08,
  shadowRadius: 9,
  shadowOffset: { width: 0, height: 3 },
  elevation: 2,
};

// The red glow under the main buttons.
export const SHADOW_RED = {
  shadowColor: '#EE1A26',
  shadowOpacity: 0.28,
  shadowRadius: 12,
  shadowOffset: { width: 0, height: 6 },
  elevation: 4,
};

// Icon tones for tiles and rows: a light tint behind the icon and the icon's own colour.
export const TONES = {
  red: { bg: '#FDE8E9', fg: '#C9111C' },
  amber: { bg: '#FFF1D6', fg: '#B46B00' },
  gold: { bg: '#FFF6C4', fg: '#7D6200' },
  sky: { bg: '#E3F6FE', fg: '#0786B8' },
  green: { bg: '#E3F6EC', fg: '#146B3C' },
  slate: { bg: '#ECEEF2', fg: '#262A32' },
  dark: { bg: '#ECEEF2', fg: '#262A32' },
};
