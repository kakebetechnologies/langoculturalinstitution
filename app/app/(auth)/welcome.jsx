// The way in. The app needs an account, so this is the first screen of anyone who is not signed in. It is kept simple: a photograph of Lango fills the top of the
// screen (the pictures change slowly), and a white sheet rises over it with one line, a short sentence and the ways in: "Continue with Google" (when the website
// has it set up), "Sign in" and a link to "Create an account" (the page the person was heading for is carried along as ?next=). Test builds also get a link
// to change the server address.
import { useState } from 'react';
import { Pressable, StyleSheet, View, useWindowDimensions } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import GoogleButton from '../../components/GoogleButton';
import Logo from '../../components/Logo';
import PhotoSlideshow from '../../components/PhotoSlideshow';
import { OPENING } from '../../constants/moments';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { isTestServer } from '../../lib/api';
import { useT } from '../../lib/i18n';

// "Photo: Ngumenawe, via Wikimedia Commons (resized). CC BY-SA 4.0." becomes "Photo: Ngumenawe · CC BY-SA 4.0" so it fits on one line.
const shortCredit = (c) => c.replace(/, via Wikimedia Commons( \(resized\))?\. /, ' · ').replace(/\.$/, '').replace('Photograph supplied for this project; the photographer is being confirmed', 'Photo supplied for this project');

export default function Welcome() {
  const { t } = useT();
  const insets = useSafeAreaInsets();
  const { height } = useWindowDimensions();
  const { next } = useLocalSearchParams();
  const params = typeof next === 'string' ? { next } : {};
  const [sheetH, setSheetH] = useState(380);
  const [shown, setShown] = useState(0);

  return (
    <View style={styles.screen}>
      <PhotoSlideshow slides={OPENING} bleed dotsTop={insets.top + 30} onChange={setShown} style={{ position: 'absolute', top: 0, left: 0, right: 0, height: Math.max(260, height - sheetH + 40) }} />
      <View style={[styles.logo, { top: insets.top + 14 }]} pointerEvents="none">
        <Logo size={46} tile />
      </View>
      <AppText style={[styles.credit, { bottom: sheetH + 8 }]} numberOfLines={1}>
        {shortCredit(OPENING[shown].credit)}
      </AppText>

      <View style={[styles.sheet, { paddingBottom: insets.bottom + 18 }]} onLayout={(e) => setSheetH(Math.round(e.nativeEvent.layout.height))}>
        <AppText style={styles.headline}>{t('Your culture. Your clan. Your language.')}</AppText>
        <AppText style={styles.sub}>{t('Sign in or create a free account to learn Leb Lango, find your clan and share what you know.')}</AppText>
        <GoogleButton next={params.next} divider={false} />
        <Button title={t('Sign in')} onPress={() => router.push({ pathname: '/(auth)/login', params })} style={styles.pill} />
        <Pressable onPress={() => router.push({ pathname: '/(auth)/register', params })} accessibilityRole="button" hitSlop={8} style={styles.new}>
          <AppText style={styles.newText}>
            {t('New here?')} <AppText style={styles.newLink}>{t('Create an account')}</AppText>
          </AppText>
        </Pressable>
        {isTestServer() ? <Button title={t('Change server address')} variant="ghost" onPress={() => router.push('/settings?server=1')} style={{ minHeight: 36, marginTop: 2 }} /> : null}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.navy },
  logo: { position: 'absolute', left: SPACING.lg },
  credit: { position: 'absolute', left: SPACING.lg, right: SPACING.lg, color: 'rgba(255,255,255,0.78)', fontFamily: FONTS.regular, fontSize: 11 },
  sheet: { position: 'absolute', left: 0, right: 0, bottom: 0, backgroundColor: COLORS.white, borderTopLeftRadius: 34, borderTopRightRadius: 34, paddingHorizontal: SPACING.lg, paddingTop: 28 },
  headline: { fontFamily: FONTS.bold, fontSize: 28, lineHeight: 34, color: COLORS.black, textAlign: 'center' },
  sub: { fontFamily: FONTS.regular, fontSize: 14.5, lineHeight: 21, color: COLORS.gray700, textAlign: 'center', marginTop: 8, marginBottom: 20 },
  pill: { borderRadius: RADIUS.full },
  new: { alignItems: 'center', paddingVertical: 14 },
  newText: { fontFamily: FONTS.regular, fontSize: 14.5, color: COLORS.gray700 },
  newLink: { fontFamily: FONTS.bold, color: COLORS.primaryDark },
});
