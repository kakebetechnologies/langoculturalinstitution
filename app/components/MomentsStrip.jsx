// "Moments from Lango": a row of photographs on Home that opens the Moments screen. The pictures are bundled with the app (constants/moments.js), so the strip shows
// at once and works with no internet.
import { Pressable, ScrollView, StyleSheet, View, useWindowDimensions } from 'react-native';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import AppText from './AppText';
import SectionHeader from './SectionHeader';
import { COLORS, FONTS, RADIUS, SHADOW_SM, SPACING } from '../constants/theme';
import { MOMENTS } from '../constants/moments';
import { useT } from '../lib/i18n';

export default function MomentsStrip({ style }) {
  const { t } = useT();
  const { width } = useWindowDimensions();
  const w = Math.min(Math.round(width * 0.62), 280);
  const shown = MOMENTS.filter((m) => !m.historical);

  return (
    <View style={style}>
      <SectionHeader title={t('Moments from Lango')} actionLabel={t('All')} onAction={() => router.push('/moments')} style={{ paddingHorizontal: SPACING.md }} />
      <ScrollView horizontal showsHorizontalScrollIndicator={false} snapToInterval={w + 12} decelerationRate="fast" contentContainerStyle={{ paddingHorizontal: SPACING.md, paddingBottom: 14, gap: 12 }}>
        {shown.map((m) => (
          <Pressable key={m.key} onPress={() => router.push('/moments')} accessibilityRole="button" accessibilityLabel={t(m.caption)} style={({ pressed }) => [styles.card, { width: w, height: Math.round(w * 1.25) }, pressed && { opacity: 0.93 }]}>
            <Image source={m.source} style={StyleSheet.absoluteFill} contentFit="cover" />
            <LinearGradient colors={['rgba(14,16,20,0)', 'rgba(14,16,20,0.82)']} locations={[0.45, 1]} style={StyleSheet.absoluteFill} />
            <AppText style={styles.caption}>{t(m.caption)}</AppText>
          </Pressable>
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  card: { borderRadius: RADIUS.xl, overflow: 'hidden', backgroundColor: COLORS.navy, justifyContent: 'flex-end', ...SHADOW_SM },
  caption: { color: COLORS.white, fontFamily: FONTS.bold, fontSize: 16, lineHeight: 21, padding: 14 },
});
