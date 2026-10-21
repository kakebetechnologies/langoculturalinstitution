// The top of a detail screen: a picture (or the logo-coloured placeholder) under a dark gradient, with the title, a line of
// small tags and a round back button on top.
import { StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS, FONTS, SPACING } from '../constants/theme';
import { mediaUrl } from '../lib/api';
import AppText from './AppText';
import { BackButton } from './ScreenHeader';

export default function CoverHero({ image, eyebrow, title, subtitle, fallback = require('../assets/welcome/hill.jpg'), height = 250 }) {
  const insets = useSafeAreaInsets();

  return (
    <View style={{ height: height + insets.top, backgroundColor: COLORS.navy }}>
      <Image source={image ? { uri: mediaUrl(image) } : fallback} style={StyleSheet.absoluteFill} contentFit="cover" transition={300} />
      <LinearGradient colors={['rgba(22,24,29,0.55)', 'rgba(22,24,29,0.15)', 'rgba(22,24,29,0.92)']} locations={[0, 0.35, 1]} style={StyleSheet.absoluteFill} />
      <View style={[styles.back, { top: insets.top + 8 }]}>
        <BackButton onDark />
      </View>
      <View style={styles.text}>
        {eyebrow ? <AppText variant="eyebrow" style={{ color: COLORS.yellow }}>{eyebrow}</AppText> : null}
        <AppText style={styles.title} numberOfLines={3}>
          {title}
        </AppText>
        {subtitle ? <AppText style={styles.sub} numberOfLines={2}>{subtitle}</AppText> : null}
      </View>
      <View style={styles.flag}>
        <View style={{ flex: 1, backgroundColor: COLORS.primary }} />
        <View style={{ flex: 1, backgroundColor: COLORS.yellow }} />
        <View style={{ flex: 1, backgroundColor: COLORS.sky }} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  back: { position: 'absolute', left: SPACING.md },
  text: { position: 'absolute', left: SPACING.md, right: SPACING.md, bottom: SPACING.md + 6 },
  title: { fontFamily: FONTS.serif, fontSize: 27, lineHeight: 33, color: COLORS.white, marginTop: 2 },
  sub: { fontFamily: FONTS.regular, fontSize: 14, lineHeight: 20, color: 'rgba(255,255,255,0.85)', marginTop: 4 },
  flag: { position: 'absolute', left: 0, right: 0, bottom: 0, height: 4, flexDirection: 'row' },
});
