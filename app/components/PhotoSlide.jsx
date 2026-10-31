// The picture on each intro slide: a photograph in a rounded frame with a soft shadow, and a round gradient badge holding the slide's icon at its corner. It replaces
// the drawn illustration of the first version of the intro. The photographs are bundled with the app (constants/moments.js).
import { StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, GRADIENTS, SHADOW } from '../constants/theme';

export default function PhotoSlide({ source, icon, tone = 'red', width = 320, height = 300, label }) {
  return (
    <View style={{ width, height }}>
      <View style={[styles.frame, { width, height }]}>
        <Image source={source} style={StyleSheet.absoluteFill} contentFit="cover" accessibilityLabel={label} />
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
});
