// The deep gradient header at the top of Home and Me: the logo-black fading into a dark red, with soft translucent circles and
// rounded bottom corners (the FaithFellows hero). It pads itself for the status bar; children sit inside it.
import { StyleSheet, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { GRADIENTS, RADIUS, SPACING } from '../constants/theme';

export default function HeroHeader({ children, style }) {
  const insets = useSafeAreaInsets();

  return (
    <LinearGradient colors={GRADIENTS.deep} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={[styles.hero, { paddingTop: insets.top + 12 }, style]}>
      <View style={[styles.circle, { width: 230, height: 230, top: -70, right: -80 }]} />
      <View style={[styles.circle, { width: 170, height: 170, bottom: -50, left: -60 }]} />
      {children}
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  hero: { overflow: 'hidden', borderBottomLeftRadius: RADIUS.xl + 8, borderBottomRightRadius: RADIUS.xl + 8, paddingHorizontal: SPACING.md, paddingBottom: SPACING.lg },
  circle: { position: 'absolute', borderRadius: 999, backgroundColor: 'rgba(255,255,255,0.06)' },
});
