// The picture on each intro slide (the FaithFellows intro look, drawn from the logo colours): a soft round backdrop with a ring, a big
// gradient tile holding one icon, and three small white badges floating around it. Pure shapes and icons: nothing is a portrait or an emblem.
import { StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, GRADIENTS, SHADOW } from '../constants/theme';

export default function Illustration({ icon, tone = 'red', orbit = [], size = 280 }) {
  const c = size / 2;
  const spots = [
    { x: c - size * 0.42, y: c - size * 0.2 },
    { x: c + size * 0.3, y: c - size * 0.32 },
    { x: c + size * 0.2, y: c + size * 0.3 },
  ];

  return (
    <View style={{ width: size, height: size }}>
      <View style={[styles.round, { width: size, height: size, borderRadius: c, backgroundColor: COLORS.primaryMist }]} />
      <View style={[styles.round, { width: size * 0.74, height: size * 0.74, borderRadius: size * 0.37, left: size * 0.13, top: size * 0.13, backgroundColor: 'rgba(255,255,255,0.75)' }]} />
      <View style={[styles.dot, { width: 16, height: 16, left: size * 0.08, top: size * 0.12, backgroundColor: COLORS.yellow }]} />
      <View style={[styles.dot, { width: 11, height: 11, right: size * 0.1, bottom: size * 0.16, backgroundColor: COLORS.sky }]} />
      <View style={[styles.dot, { width: 9, height: 9, right: size * 0.06, top: size * 0.28, backgroundColor: COLORS.primary }]} />
      <LinearGradient colors={GRADIENTS[tone] ?? GRADIENTS.red} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={[styles.core, { width: size * 0.4, height: size * 0.4, left: c - size * 0.2, top: c - size * 0.2, borderRadius: size * 0.12 }]}>
        <Ionicons name={icon} size={size * 0.2} color={COLORS.white} />
      </LinearGradient>
      {orbit.slice(0, 3).map(([name, color], i) => (
        <View key={name} style={[styles.chip, { left: spots[i].x, top: spots[i].y }]}>
          <Ionicons name={name} size={22} color={color} />
        </View>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  round: { position: 'absolute' },
  dot: { position: 'absolute', borderRadius: 8 },
  core: { position: 'absolute', alignItems: 'center', justifyContent: 'center', ...SHADOW, shadowColor: COLORS.primary, shadowOpacity: 0.3 },
  chip: { position: 'absolute', width: 50, height: 50, borderRadius: 25, backgroundColor: COLORS.white, alignItems: 'center', justifyContent: 'center', ...SHADOW },
});
