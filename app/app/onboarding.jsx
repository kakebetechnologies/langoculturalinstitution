// First run: three welcome slides over the site's own photography ("Preserve your heritage", "Contribute your knowledge",
// "Connect with your clan"), then a choice of language (English or Leb Lango). Seen once; the choice can be changed in Settings.
// i18n:all (the phrases in this file are listed for translation by `npm run strings`)
import { useRef, useState } from 'react';
import { FlatList, Pressable, StyleSheet, View, useWindowDimensions } from 'react-native';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Logo from '../components/Logo';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { setStored } from '../lib/storage';

const SLIDES = [
  { key: 'a', image: require('../assets/welcome/hill.jpg'), title: 'Preserve your heritage', text: 'Clans, ceremonies, history and the places that make Lango — kept safe for the next generation.' },
  { key: 'b', image: require('../assets/welcome/field.jpg'), title: 'Contribute your knowledge', text: 'Photograph a heritage site, record an elder, suggest a word. Reviewers check it and add it to the archive.' },
  { key: 'c', image: require('../assets/welcome/aerial.jpg'), title: 'Connect with your clan', text: 'Choose your clan, learn Leb Lango, find events and meet the Lango community at home and abroad.' },
];

export default function Onboarding() {
  const { t, lang, setLang } = useT();
  const insets = useSafeAreaInsets();
  const { width, height } = useWindowDimensions();
  const list = useRef(null);
  const [page, setPage] = useState(0);
  const last = SLIDES.length; // the language page

  const go = (n) => {
    list.current?.scrollToIndex({ index: n, animated: true });
    setPage(n);
  };

  async function finish() {
    await setStored('onboarded_v1', true);
    router.replace('/(tabs)');
  }

  const data = [...SLIDES, { key: 'lang' }];

  return (
    <View style={styles.screen}>
      <FlatList
        ref={list}
        data={data}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        keyExtractor={(s) => s.key}
        onMomentumScrollEnd={(e) => setPage(Math.round(e.nativeEvent.contentOffset.x / width))}
        getItemLayout={(_, i) => ({ length: width, offset: width * i, index: i })}
        renderItem={({ item }) =>
          item.key === 'lang' ? (
            <View style={[styles.page, { width, height, backgroundColor: COLORS.paper, paddingTop: insets.top + 60 }]}>
              <Logo size={96} />
              <AppText variant="title" style={{ marginTop: SPACING.lg, textAlign: 'center' }}>
                {t('Choose your language')}
              </AppText>
              <AppText style={{ textAlign: 'center', marginTop: 6, marginBottom: SPACING.lg }}>{t('You can change it any time in Settings.')}</AppText>
              {[
                ['en', 'English', 'Show the app in English'],
                ['lg', 'Leb Lango', 'Show translated text where it is ready; the rest stays in English'],
              ].map(([code, name, sub]) => (
                <Pressable key={code} onPress={() => setLang(code)} accessibilityRole="button" accessibilityState={{ selected: lang === code }} style={[styles.lang, lang === code && styles.langOn]}>
                  <View style={{ flex: 1 }}>
                    <AppText style={{ fontFamily: FONTS.serif, fontSize: 20, color: COLORS.black }}>{name}</AppText>
                    <AppText variant="caption">{sub}</AppText>
                  </View>
                  <View style={[styles.radio, lang === code && styles.radioOn]} />
                </Pressable>
              ))}
              <View style={{ flex: 1 }} />
              <Button title={t('Get started')} onPress={finish} style={{ alignSelf: 'stretch', marginBottom: insets.bottom + SPACING.md }} />
            </View>
          ) : (
            <View style={{ width, height }}>
              <Image source={item.image} style={StyleSheet.absoluteFill} contentFit="cover" />
              <LinearGradient colors={['rgba(22,24,29,0.55)', 'rgba(22,24,29,0.1)', 'rgba(22,24,29,0.94)']} locations={[0, 0.3, 0.85]} style={StyleSheet.absoluteFill} />
              <View style={[styles.top, { paddingTop: insets.top + 16 }]}>
                <Logo size={46} tile />
              </View>
              <View style={[styles.copy, { paddingBottom: insets.bottom + 110 }]}>
                <AppText style={styles.h}>{t(item.title)}</AppText>
                <AppText style={styles.p}>{t(item.text)}</AppText>
              </View>
            </View>
          )
        }
      />

      {page < last ? (
        <View style={[styles.footer, { paddingBottom: insets.bottom + 18 }]} pointerEvents="box-none">
          <View style={styles.dots}>
            {data.map((s, i) => (
              <View key={s.key} style={[styles.dot, i === page && styles.dotOn, page === last && { backgroundColor: i === page ? COLORS.primary : COLORS.gray300 }]} />
            ))}
          </View>
          {page < last ? (
            <View style={styles.actions}>
              <Pressable onPress={() => go(last)} hitSlop={10} accessibilityRole="button">
                <AppText style={styles.skip}>{t('Skip')}</AppText>
              </Pressable>
              <Button title={t('Next')} variant="light" small onPress={() => go(page + 1)} />
            </View>
          ) : null}
        </View>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.navy },
  page: { paddingHorizontal: SPACING.lg, alignItems: 'center' },
  top: { paddingHorizontal: SPACING.lg },
  copy: { flex: 1, justifyContent: 'flex-end', paddingHorizontal: SPACING.lg },
  h: { fontFamily: FONTS.serif, fontSize: 34, lineHeight: 40, color: COLORS.white },
  p: { fontFamily: FONTS.regular, fontSize: 16, lineHeight: 24, color: 'rgba(255,255,255,0.88)', marginTop: 10 },
  footer: { position: 'absolute', left: 0, right: 0, bottom: 0, paddingHorizontal: SPACING.lg },
  dots: { flexDirection: 'row', gap: 6, marginBottom: 14 },
  dot: { width: 7, height: 7, borderRadius: 4, backgroundColor: 'rgba(255,255,255,0.4)' },
  dotOn: { width: 22, backgroundColor: COLORS.white },
  actions: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  skip: { fontFamily: FONTS.semibold, fontSize: 15, color: 'rgba(255,255,255,0.85)' },
  lang: { flexDirection: 'row', alignItems: 'center', alignSelf: 'stretch', gap: 12, padding: SPACING.md, borderRadius: RADIUS.lg, borderWidth: 1.5, borderColor: COLORS.gray300, backgroundColor: COLORS.white, marginBottom: 12 },
  langOn: { borderColor: COLORS.primary, backgroundColor: COLORS.primaryMist },
  radio: { width: 22, height: 22, borderRadius: 11, borderWidth: 2, borderColor: COLORS.gray300 },
  radioOn: { borderColor: COLORS.primary, backgroundColor: COLORS.primary },
});
