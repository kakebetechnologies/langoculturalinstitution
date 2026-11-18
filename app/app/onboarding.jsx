// First run, in the FaithFellows intro style: three light slides ("Preserve your heritage", "Contribute your knowledge",
// "Connect with your clan"), each with a soft round picture, a headline and a line of text, then a last page. A Lango | Acholi switch (the same one as on the
// welcome screen) sits at the top of every page: choosing a community changes the wording of the slides at once (Lango to Acholi, clan to chiefdom, Leb Lango to
// Leb Acholi), keeps that community for the sign-in screen and the rest of the app, and shows its own content only: Lango's photographs appear for Lango alone.
// The last page is the choice of interface language (English or Leb Lango) for Lango, and a welcome to the community for the others, whose interface is English.
// Seen once; the choices can be changed on the sign-in screen and in Settings.
// i18n:all (the phrases in this file are listed for translation by `npm run strings`)
import { useRef, useState } from 'react';
import { FlatList, Pressable, StyleSheet, View, useWindowDimensions } from 'react-native';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import AppText from '../components/AppText';
import Button from '../components/Button';
import CommunitySwitch from '../components/CommunitySwitch';
import Logo from '../components/Logo';
import PhotoSlide from '../components/PhotoSlide';
import { MOMENTS } from '../constants/moments';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../constants/theme';
import { useAuth } from '../lib/auth';
import { useCommunity } from '../lib/community';
import { useT } from '../lib/i18n';
import { setStored } from '../lib/storage';

const SLIDES = [
  { key: 'a', photo: 'dancers-group', icon: 'earth', tone: 'red', orbit: [['book', COLORS.yellowInk], ['map', COLORS.skyDark], ['time', COLORS.primary]], title: 'Preserve your heritage', text: 'Clans, ceremonies, history and the places that make Lango — kept safe for the next generation.' },
  { key: 'b', photo: 'harvest', icon: 'camera', tone: 'sky', orbit: [['mic', COLORS.primary], ['create', COLORS.yellowInk], ['location', COLORS.skyDark]], title: 'Contribute your knowledge', text: 'Photograph a heritage site, record an elder, suggest a word. Reviewers check it and add it to the archive.' },
  { key: 'c', photo: 'pot-dance', icon: 'people', tone: 'green', orbit: [['language', COLORS.skyDark], ['calendar', COLORS.primary], ['chatbubbles', COLORS.yellowInk]], title: 'Connect with your clan', text: 'Choose your clan, learn Leb Lango, find events and meet the Lango community at home and abroad.' },
];

export default function Onboarding() {
  const { t, lang, setLang } = useT();
  const { info, features } = useCommunity();
  const insets = useSafeAreaInsets();
  const { width, height } = useWindowDimensions();
  const list = useRef(null);
  const { token } = useAuth();
  const [page, setPage] = useState(0);
  const last = SLIDES.length; // the language page
  const illustration = Math.min(330, width - 64, height * 0.36);

  const go = (n) => {
    list.current?.scrollToIndex({ index: n, animated: true });
    setPage(n);
  };

  async function finish() {
    await setStored('onboarded_v1', true);
    router.replace(token ? '/(tabs)' : '/(auth)/welcome');
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
            <View style={[styles.page, { width, height, paddingTop: insets.top + 96 }]}>
              <Logo size={92} />
              <AppText style={styles.h}>{features.languageSwitch ? t('Choose your language') : t('Welcome to Lango')}</AppText>
              <AppText style={[styles.p, { marginBottom: SPACING.lg }]}>{features.languageSwitch ? t('You can change it any time in Settings.') : t('The app is shown in English. Its dictionary and word lists are in Leb Lango.')}</AppText>
              {(features.languageSwitch
                ? [
                    ['en', 'English', 'Show the app in English'],
                    ['lg', 'Leb Lango', 'Show translated text where it is ready; the rest stays in English'],
                  ]
                : []
              ).map(([code, name, sub]) => (
                <Pressable key={code} onPress={() => setLang(code)} accessibilityRole="button" accessibilityState={{ selected: lang === code }} style={[styles.lang, lang === code && styles.langOn]}>
                  <View style={{ flex: 1 }}>
                    <AppText lango style={{ fontFamily: FONTS.bold, fontSize: 19, color: COLORS.black }}>
                      {name}
                    </AppText>
                    <AppText variant="caption">{sub}</AppText>
                  </View>
                  <View style={[styles.radio, lang === code && styles.radioOn]} />
                </Pressable>
              ))}
              <View style={{ flex: 1 }} />
              <Button title={t('Get started')} onPress={finish} style={{ alignSelf: 'stretch', marginBottom: insets.bottom + SPACING.md }} />
            </View>
          ) : (
            <View style={[styles.page, { width, height, paddingTop: insets.top + 104 }]}>
              <PhotoSlide source={features.moments ? MOMENTS.find((m) => m.key === item.photo).source : undefined} icon={item.icon} tone={item.tone} orbit={item.orbit} width={Math.min(330, width - 64)} height={illustration} label={features.moments ? t(MOMENTS.find((m) => m.key === item.photo).caption) : t(item.title)} />
              <AppText style={[styles.h, { marginTop: SPACING.xl }]}>{t(item.title)}</AppText>
              <AppText style={styles.p}>{t(item.text)}</AppText>
            </View>
          )
        }
      />

      {/* the community switch is on every page, so a person can change tribe wherever they are in the intro */}
      <View style={[styles.switchRow, { top: insets.top + 8 }]} pointerEvents="box-none">
        <CommunitySwitch compact />
      </View>

      {page < last ? (
        <>
          <Pressable onPress={() => go(last)} hitSlop={12} accessibilityRole="button" style={[styles.skip, { top: insets.top + 14 }]}>
            <AppText style={styles.skipText}>{t('Skip')}</AppText>
          </Pressable>
          <View style={[styles.footer, { paddingBottom: insets.bottom + 18 }]}>
            <View style={styles.dots}>
              {data.map((s, i) => (
                <View key={s.key} style={[styles.dot, i === page && styles.dotOn]} />
              ))}
            </View>
            <Button title={t('Next')} onPress={() => go(page + 1)} />
          </View>
        </>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.white },
  page: { paddingHorizontal: SPACING.lg, alignItems: 'center', backgroundColor: COLORS.white },
  h: { fontFamily: FONTS.bold, fontSize: 27, lineHeight: 34, color: COLORS.black, textAlign: 'center', marginTop: SPACING.lg },
  p: { fontFamily: FONTS.regular, fontSize: 15.5, lineHeight: 23, color: COLORS.gray900, textAlign: 'center', marginTop: 10, maxWidth: 340 },
  switchRow: { position: 'absolute', left: 0, right: 0, alignItems: 'center', zIndex: 5 },
  skip: { position: 'absolute', right: SPACING.lg },
  skipText: { fontFamily: FONTS.semibold, fontSize: 15, color: COLORS.gray700 },
  footer: { position: 'absolute', left: 0, right: 0, bottom: 0, paddingHorizontal: SPACING.lg, gap: 18 },
  dots: { flexDirection: 'row', gap: 7, justifyContent: 'center' },
  dot: { width: 8, height: 8, borderRadius: 4, backgroundColor: COLORS.primaryMist },
  dotOn: { width: 26, backgroundColor: COLORS.primary },
  lang: { flexDirection: 'row', alignItems: 'center', alignSelf: 'stretch', gap: 12, padding: SPACING.md, borderRadius: RADIUS.lg, backgroundColor: COLORS.white, marginBottom: 12, borderWidth: 1.5, borderColor: 'transparent', ...SHADOW },
  langOn: { borderColor: COLORS.primary, backgroundColor: COLORS.primaryMist },
  radio: { width: 22, height: 22, borderRadius: 11, borderWidth: 2, borderColor: COLORS.gray300 },
  radioOn: { borderColor: COLORS.primary, backgroundColor: COLORS.primary },
});
