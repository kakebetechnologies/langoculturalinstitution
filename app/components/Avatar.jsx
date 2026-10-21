// A person's round photo, or their initials on a soft logo-palette tile (so everyone looks different even without a photo).
import { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';
import { COLORS, FONTS } from '../constants/theme';
import { mediaUrl } from '../lib/api';
import { initials } from '../lib/format';
import AppText from './AppText';

const TILES = [
  { bg: COLORS.primaryMist, fg: COLORS.primaryDark },
  { bg: COLORS.skyMist, fg: COLORS.skyDark },
  { bg: COLORS.yellowMist, fg: COLORS.yellowInk },
  { bg: COLORS.gray100, fg: COLORS.navy },
];

export const tileFor = (name = '') => TILES[[...String(name)].reduce((s, ch) => s + ch.charCodeAt(0), 0) % TILES.length];

export default function Avatar({ name, photo, size = 48, square = false }) {
  const address = mediaUrl(photo);
  const [broken, setBroken] = useState(null);
  const uri = address && broken !== address ? address : null;
  const tile = tileFor(name);
  const box = { width: size, height: size, borderRadius: square ? size * 0.2 : size / 2, backgroundColor: tile.bg };

  return (
    <View style={[box, styles.center]} accessibilityLabel={name}>
      {uri ? (
        <Image source={{ uri }} onError={() => setBroken(address)} style={StyleSheet.absoluteFill} contentFit="cover" contentPosition="top" transition={200} />
      ) : (
        <AppText allowFontScaling={false} numberOfLines={1} style={{ fontFamily: FONTS.serif, fontSize: size * 0.36, color: tile.fg, textAlign: 'center' }}>
          {initials(name)}
        </AppText>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  center: { alignItems: 'center', justifyContent: 'center', overflow: 'hidden' },
});
