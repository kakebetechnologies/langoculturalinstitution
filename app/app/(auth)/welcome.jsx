// The way in. The app needs an account, so this is the first screen of anyone who is not signed in. It is a dark page: a photograph of Lango fills the top and
// fades into the dark (so a modest photograph still looks right), then the logo and name, "Continue with Google" (when the website has it set up), "Continue with
// email", a link to create an account, and the privacy policy and terms. The photographs are set in the website's admin (Content, App opening photos) and come from
// GET /api/v1/welcome; the app also carries its first five pictures, which it uses if the server cannot be reached. The page the person was heading for is carried
// along as ?next=. Test builds also get a link to change the server address.
import { useEffect, useRef, useState } from 'react';
import { Animated, Pressable, StyleSheet, View, useWindowDimensions } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { router, useLocalSearchParams } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import CommunitySwitch from '../../components/CommunitySwitch';
import GoogleButton from '../../components/GoogleButton';
import Logo from '../../components/Logo';
import PhotoSlideshow from '../../components/PhotoSlideshow';
import { OPENING } from '../../constants/moments';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { isTestServer } from '../../lib/api';
import { useCommunity } from '../../lib/community';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

const DARK = COLORS.navy;
const FOCUS_DOWN = { top: '6%', upper: '24%', centre: '50%', lower: '78%' };

// "Photo: Ngumenawe, via Wikimedia Commons (resized). CC BY-SA 4.0." becomes "Photo: Ngumenawe · CC BY-SA 4.0" so it fits on one line.
const shortCredit = (c) =>
  String(c ?? '')
    .replace(/, via Wikimedia Commons( \(resized\))?\. /, ' · ')
    .replace(/\.$/, '')
    .replace('Photograph supplied for this project; the photographer is being confirmed', 'Photo supplied for this project');

export default function Welcome() {
  const { t } = useT();
  const { info } = useCommunity();
  const insets = useSafeAreaInsets();
  const { height } = useWindowDimensions();
  const { next } = useLocalSearchParams();
  const params = typeof next === 'string' ? { next } : {};
  const { data, loading, error } = useFetch('/welcome');
  const [shown, setShown] = useState(0);
  const appear = useRef(new Animated.Value(0)).current;

  // the photographs the admin has published; the pictures inside the app when the server cannot be reached; none when the admin has published none
  const remote = data?.data ?? null;
  const slides = remote
    ? remote.map((p) => ({ key: `w${p.id}`, source: { uri: p.image }, caption: p.caption ?? '', credit: p.credit ?? '', focus: ['50%', FOCUS_DOWN[p.focus] ?? FOCUS_DOWN.upper] }))
    : error || !loading
      ? OPENING
      : null;
  const ready = slides !== null;
  useEffect(() => {
    if (ready) Animated.timing(appear, { toValue: 1, duration: 700, useNativeDriver: true }).start();
  }, [ready]);

  const photoH = Math.round(height * 0.64);
  const current = slides?.[Math.min(shown, (slides?.length ?? 1) - 1)];

  return (
    <View style={styles.screen}>
      {slides?.length ? (
        <Animated.View style={[styles.photo, { height: photoH, opacity: appear }]}>
          <PhotoSlideshow key={slides.map((s) => s.key).join('|')} slides={slides} bleed dotsTop={insets.top + 12} onChange={setShown} style={StyleSheet.absoluteFill} />
        </Animated.View>
      ) : null}
      {/* the photograph melts into the dark page: a little dark at the top for the status bar, clear in the middle, solid at the bottom */}
      <LinearGradient colors={['rgba(22,24,29,0.55)', 'rgba(22,24,29,0)', 'rgba(22,24,29,0.55)', DARK]} locations={[0, 0.2, 0.62, 0.9]} style={[styles.photo, { height: photoH + 2 }]} pointerEvents="none" />
      {current?.credit ? (
        <AppText style={[styles.credit, { top: insets.top + 10 }]} numberOfLines={1}>
          {shortCredit(current.credit)}
        </AppText>
      ) : null}

      <View style={[styles.content, { paddingTop: insets.top + 10, paddingBottom: insets.bottom + 14 }]}>
        <View style={{ flex: 1 }} />
        <View style={styles.brand}>
          <Logo size={58} tile />
          <AppText style={styles.wordmark}>{info.name}</AppText>
          <AppText style={styles.tagline}>{t('Your culture. Your clan. Your language.')}</AppText>
        </View>

        <CommunitySwitch onDark style={{ marginBottom: SPACING.sm }} />

        <View style={styles.buttons}>
          <GoogleButton next={params.next} divider={false} />
          <Pressable onPress={() => router.push({ pathname: '/(auth)/login', params })} accessibilityRole="button" style={({ pressed }) => [styles.email, pressed && { opacity: 0.85 }]}>
            <AppText style={styles.emailText}>{t('Continue with email')}</AppText>
          </Pressable>
          <Pressable onPress={() => router.push({ pathname: '/(auth)/register', params })} accessibilityRole="button" hitSlop={8} style={styles.new}>
            <AppText style={styles.newText}>
              {t('New here?')} <AppText style={styles.newLink}>{t('Create an account')}</AppText>
            </AppText>
          </Pressable>
        </View>

        <View style={styles.legal}>
          <Pressable onPress={() => router.push('/legal/privacy')} accessibilityRole="link" hitSlop={8}>
            <AppText style={styles.legalText}>{t('Privacy policy')}</AppText>
          </Pressable>
          <Pressable onPress={() => router.push('/legal/terms')} accessibilityRole="link" hitSlop={8}>
            <AppText style={styles.legalText}>{t('Terms of service')}</AppText>
          </Pressable>
        </View>
        {isTestServer() ? <Button title={t('Change server address')} variant="ghostLight" onPress={() => router.push('/settings?server=1')} style={{ minHeight: 34 }} /> : null}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: DARK },
  photo: { position: 'absolute', top: 0, left: 0, right: 0 },
  credit: { position: 'absolute', left: SPACING.lg, right: 90, color: 'rgba(255,255,255,0.7)', fontFamily: FONTS.regular, fontSize: 10.5 },
  content: { flex: 1, paddingHorizontal: SPACING.lg },
  brand: { alignItems: 'center', marginBottom: SPACING.lg },
  wordmark: { fontFamily: FONTS.bold, fontSize: 44, lineHeight: 52, color: COLORS.white, marginTop: 10, letterSpacing: -1 },
  tagline: { fontFamily: FONTS.regular, fontSize: 15, lineHeight: 21, color: 'rgba(255,255,255,0.72)', textAlign: 'center', marginTop: 2 },
  buttons: { marginTop: SPACING.sm },
  email: { minHeight: 54, alignItems: 'center', justifyContent: 'center', borderRadius: RADIUS.full, backgroundColor: 'rgba(255,255,255,0.13)' },
  emailText: { fontFamily: FONTS.semibold, fontSize: 16, color: COLORS.white },
  new: { alignItems: 'center', paddingVertical: 14 },
  newText: { fontFamily: FONTS.regular, fontSize: 14.5, color: 'rgba(255,255,255,0.72)' },
  newLink: { fontFamily: FONTS.bold, color: COLORS.yellow },
  legal: { flexDirection: 'row', justifyContent: 'center', gap: 26, paddingTop: 4, paddingBottom: 4 },
  legalText: { fontFamily: FONTS.semibold, fontSize: 13.5, color: 'rgba(255,255,255,0.5)' },
});
