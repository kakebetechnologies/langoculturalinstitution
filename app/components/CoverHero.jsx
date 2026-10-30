// The top of a detail screen: a round back button, then a large rounded picture card (or the Lango photo as the placeholder) with a
// small yellow label on it, and the title and a line of text under the picture. The three flag colours run along the picture's foot.
import { StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../constants/theme';
import { mediaUrl } from '../lib/api';
import AppText from './AppText';
import { BackButton } from './ScreenHeader';

export default function CoverHero({ image, eyebrow, title, subtitle, fallback = require('../assets/welcome/hill.jpg'), height = 230 }) {
  const insets = useSafeAreaInsets();

  return (
    <View style={{ paddingTop: insets.top + 8, paddingHorizontal: SPACING.md, backgroundColor: COLORS.canvas }}>
      <View style={styles.bar}>
        <BackButton />
      </View>
      <View style={[styles.card, { height }]}>
        <Image source={image ? { uri: mediaUrl(image) } : fallback} style={StyleSheet.absoluteFill} contentFit="cover" transition={300} />
        <LinearGradient colors={['rgba(22,24,29,0)', 'rgba(22,24,29,0.35)']} locations={[0.5, 1]} style={StyleSheet.absoluteFill} />
        {eyebrow ? (
          <View style={styles.tag}>
            <AppText style={styles.tagText} numberOfLines={1}>
              {eyebrow}
            </AppText>
          </View>
        ) : null}
        <View style={styles.flag}>
          <View style={{ flex: 1, backgroundColor: COLORS.primary }} />
          <View style={{ flex: 1, backgroundColor: COLORS.yellow }} />
          <View style={{ flex: 1, backgroundColor: COLORS.sky }} />
        </View>
      </View>
      <AppText style={styles.title}>{title}</AppText>
      {subtitle ? <AppText style={styles.sub}>{subtitle}</AppText> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  bar: { marginBottom: SPACING.sm + 2 },
  card: { borderRadius: RADIUS.xl, overflow: 'hidden', backgroundColor: COLORS.navy, ...SHADOW },
  tag: { position: 'absolute', left: 12, top: 12, maxWidth: '80%', backgroundColor: COLORS.yellow, paddingHorizontal: 12, paddingVertical: 6, borderRadius: RADIUS.full },
  tagText: { fontFamily: FONTS.bold, fontSize: 11, letterSpacing: 0.8, textTransform: 'uppercase', color: COLORS.navy },
  flag: { position: 'absolute', left: 0, right: 0, bottom: 0, height: 4, flexDirection: 'row' },
  title: { fontFamily: FONTS.bold, fontSize: 26, lineHeight: 32, color: COLORS.black, marginTop: SPACING.md },
  sub: { fontFamily: FONTS.regular, fontSize: 14.5, lineHeight: 21, color: COLORS.gray900, marginTop: 4 },
});
