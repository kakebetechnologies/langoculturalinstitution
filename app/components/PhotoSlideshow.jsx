// A framed slideshow of photographs for the opening screen: rounded corners, a red, yellow and blue edge like the Home banner, a slow fade from one picture
// to the next with a gentle zoom, the caption of the picture on show and a dot for each picture. The pictures are bundled with the app (constants/moments.js), so
// it shows at once and with no internet. With "reduce motion" on, it stays on the first picture.
import { useEffect, useRef, useState } from 'react';
import { AccessibilityInfo, Animated, Easing, Platform, StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import AppText from './AppText';
import { COLORS, FONTS } from '../constants/theme';
import { useT } from '../lib/i18n';

const native = Platform.OS !== 'web';

export default function PhotoSlideshow({ slides, interval = 5200, style, children }) {
  const { t } = useT();
  const [index, setIndex] = useState(0);
  const [still, setStill] = useState(false);
  const fades = useRef(slides.map((_, i) => new Animated.Value(i === 0 ? 1 : 0))).current;
  const zooms = useRef(slides.map(() => new Animated.Value(1))).current;

  useEffect(() => {
    AccessibilityInfo.isReduceMotionEnabled?.().then(setStill).catch(() => {});
  }, []);

  // the slow zoom of the picture on show
  useEffect(() => {
    zooms[index].setValue(1);
    const z = Animated.timing(zooms[index], { toValue: 1.1, duration: interval + 1200, easing: Easing.linear, useNativeDriver: native });
    if (!still) z.start();
    return () => z.stop();
  }, [index, still]);

  // on to the next picture
  useEffect(() => {
    if (still || slides.length < 2) return undefined;
    const id = setTimeout(() => {
      const next = (index + 1) % slides.length;
      Animated.parallel([
        Animated.timing(fades[next], { toValue: 1, duration: 1000, useNativeDriver: native }),
        Animated.timing(fades[index], { toValue: 0, duration: 1000, useNativeDriver: native }),
      ]).start(() => setIndex(next));
    }, interval);
    return () => clearTimeout(id);
  }, [index, still]);

  return (
    <View style={[styles.frame, style]}>
      {slides.map((s, i) => (
        <Animated.View key={s.key} pointerEvents="none" style={[StyleSheet.absoluteFill, { opacity: fades[i], transform: [{ scale: zooms[i] }] }]}>
          <Image source={s.source} style={StyleSheet.absoluteFill} contentFit="cover" contentPosition={{ left: '50%', top: s.focus ?? '50%' }} accessibilityLabel={t(s.caption)} />
        </Animated.View>
      ))}
      <LinearGradient colors={['rgba(14,16,20,0.35)', 'rgba(14,16,20,0)', 'rgba(14,16,20,0.8)']} locations={[0, 0.4, 1]} style={StyleSheet.absoluteFill} pointerEvents="none" />
      <View style={styles.flag} pointerEvents="none">
        <View style={{ flex: 1, backgroundColor: COLORS.primary }} />
        <View style={{ flex: 1, backgroundColor: COLORS.yellow }} />
        <View style={{ flex: 1, backgroundColor: COLORS.sky }} />
      </View>
      {children}
      <View style={styles.dots} pointerEvents="none">
        {slides.map((s, i) => (
          <View key={s.key} style={[styles.dot, i === index && styles.dotOn]} />
        ))}
      </View>
      <View style={styles.foot} pointerEvents="none">
        <AppText style={styles.caption} numberOfLines={1}>
          {t(slides[index].caption)}
        </AppText>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  frame: { overflow: 'hidden', borderRadius: 32, backgroundColor: COLORS.navy },
  flag: { position: 'absolute', top: 0, left: 0, right: 0, height: 6, flexDirection: 'row' },
  foot: { position: 'absolute', left: 18, right: 18, bottom: 16 },
  caption: { color: COLORS.white, fontFamily: FONTS.semibold, fontSize: 15 },
  dots: { position: 'absolute', top: 30, right: 18, flexDirection: 'row', gap: 6 },
  dot: { width: 7, height: 7, borderRadius: 4, backgroundColor: 'rgba(255,255,255,0.45)' },
  dotOn: { width: 20, backgroundColor: COLORS.yellow },
});
