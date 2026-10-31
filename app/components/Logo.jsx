// The Lango mark (assets/logo-mark.png): a bold L on black with a red, yellow and blue bar. `tile` puts it on a white rounded tile for dark or photo backgrounds.
import { StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';

export default function Logo({ size = 72, tile = false }) {
  const mark = <Image source={require('../assets/logo-mark.png')} style={tile ? { width: size * 0.84, height: size * 0.84, borderRadius: size * 0.16 } : { width: size, height: size, borderRadius: size * 0.22 }} contentFit="contain" accessibilityLabel="Lango" />;

  if (!tile) return mark;

  return <View style={[styles.tile, { width: size, height: size, borderRadius: size * 0.22 }]}>{mark}</View>;
}

const styles = StyleSheet.create({
  tile: { backgroundColor: '#FFFFFF', alignItems: 'center', justifyContent: 'center' },
});
