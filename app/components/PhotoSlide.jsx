// The picture on each intro slide: a photograph in a rounded frame with a soft shadow, and a round gradient badge holding the slide's icon at its corner. It replaces
// the drawn illustration of the first version of the intro. The photographs are bundled with the app (constants/moments.js) and are Lango's own, so another
// community (Acholi...) gets no `source`: a soft tinted panel with a large icon and three small icons around it, which shows no one else's pictures.
import { StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, GRADIENTS, SHADOW, TONES } from '../constants/theme';

// where the three small icons sit on the tinted panel (as a share of its width and height)
const SPOTS = [
  { left: 0.12, top: 0.14 },
  { left: 0.72, top: 0.1 },
  { left: 0.78, top: 0.62 },
];

export default function PhotoSlide({ source, icon, tone = 'red', orbit = [], width = 320, height = 300, label }) {
  const soft = TONES[tone] ?? TONES.red;
  const big = Math.min(width, height) * 0.42;
  return (
    <View style={{ width, height }}>
      <View style={[styles.frame, { width, height }]}>
        {source ? (
          <Image source={source} style={StyleSheet.absoluteFill} contentFit="cover" accessibilityLabel={label} />
        ) : (
          <View style={[StyleSheet.absoluteFill, { backgroundColor: soft.bg }]} accessibilityLabel={label}>
            <View style={[styles.bigCircle, { width: big, height: big, borderRadius: big / 2, left: (width - big) / 2, top: (height - big) / 2 - 6 }]}>
              <Ionicons name={icon} size={big * 0.5} color={soft.fg} />
            </View>
            {orbit.slice(0, SPOTS.length).map(([name, color], i) => (
              <View key={name} style={[styles.smallCircle, { left: SPOTS[i].left * width, top: SPOTS[i].top * height }]}>
                <Ionicons name={name} size={22} color={color} />
              </View>
            ))}
          </View>
        )}
        <View style={styles.flag} pointerEvents="none">
          <View style={{ flex: 1, backgroundColor: COLORS.primary }} />
          <View style={{ flex: 1, backgroundColor: COLORS.yellow }} />
          <View style={{ flex: 1, backgroundColor: COLORS.sky }} />
        </View>
      </View>
      <LinearGradient colors={GRADIENTS[tone] ?? GRADIENTS.red} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.badge}>
        <Ionicons name={icon} size={28} color={COLORS.white} />
      </LinearGradient>
    </View>
  );
}

const styles = StyleSheet.create({
  frame: { overflow: 'hidden', borderRadius: 32, backgroundColor: COLORS.primaryMist, ...SHADOW },
  flag: { position: 'absolute', top: 0, left: 0, right: 0, height: 6, flexDirection: 'row' },
  badge: { position: 'absolute', right: -8, bottom: -14, width: 68, height: 68, borderRadius: 34, alignItems: 'center', justifyContent: 'center', borderWidth: 4, borderColor: COLORS.white, ...SHADOW },
  bigCircle: { position: 'absolute', backgroundColor: COLORS.white, alignItems: 'center', justifyContent: 'center', ...SHADOW },
  smallCircle: { position: 'absolute', width: 46, height: 46, borderRadius: 23, backgroundColor: COLORS.white, alignItems: 'center', justifyContent: 'center', ...SHADOW },
});
