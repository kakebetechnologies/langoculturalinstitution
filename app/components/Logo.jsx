// The Lango logo, the coat-style emblem (assets/logo-mark.png), never redrawn or recoloured.
// `tile` puts it on a white rounded tile so it keeps its own colours on dark or photo backgrounds.
import { StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';
import AppText from './AppText';
import { useCommunity } from '../lib/community';

export default function Logo({ size = 72, tile = false }) {
  const { info } = useCommunity();
  if (info.code !== 'lango') {
    return (
      <View style={[styles.tile, { width: size, height: size * 1.1, borderRadius: size * 0.18 }]} accessibilityLabel={info.name}>
        <AppText style={{ fontSize: size * 0.5, lineHeight: size * 0.62, color: '#16181D', fontFamily: 'Inter_700Bold' }}>{info.name.slice(0, 1)}</AppText>
      </View>
    );
  }
  const mark = <Image source={require('../assets/logo-mark.png')} style={tile ? { width: size * 0.62, height: size * 0.82 } : { width: size * 0.72, height: size }} contentFit="contain" accessibilityLabel="Lango" />;

  if (!tile) return mark;

  return <View style={[styles.tile, { width: size, height: size * 1.1, borderRadius: size * 0.18 }]}>{mark}</View>;
}

const styles = StyleSheet.create({
  tile: { backgroundColor: '#FFFFFF', alignItems: 'center', justifyContent: 'center' },
});
