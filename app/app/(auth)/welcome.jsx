// The way in for people who are not signed in: the logo over a photograph, "Create an account" and "Sign in" (with the page the
// person was heading for carried along as ?next=), and "Keep browsing", because the dictionary, clans, history and districts
// never need an account.
import { StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { router, useLocalSearchParams } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import Logo from '../../components/Logo';
import { COLORS, FONTS, SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';

export default function Welcome() {
  const { t } = useT();
  const insets = useSafeAreaInsets();
  const { next } = useLocalSearchParams();
  const params = typeof next === 'string' ? { next } : {};

  return (
    <View style={styles.screen}>
      <Image source={require('../../assets/welcome/ridge.jpg')} style={StyleSheet.absoluteFill} contentFit="cover" />
      <LinearGradient colors={['rgba(22,24,29,0.6)', 'rgba(22,24,29,0.15)', 'rgba(22,24,29,0.96)']} locations={[0, 0.3, 0.8]} style={StyleSheet.absoluteFill} />
      <View style={[styles.top, { paddingTop: insets.top + 18 }]}>
        <Logo size={52} tile />
      </View>
      <View style={[styles.bottom, { paddingBottom: insets.bottom + 22 }]}>
        <AppText variant="eyebrow" style={{ color: COLORS.yellow }}>
          Won Nyaci me Lango
        </AppText>
        <AppText style={styles.headline}>{t('Your culture, your clan, your language — in your pocket')}</AppText>
        <AppText style={styles.sub}>{t('Join the Lango community to contribute heritage, learn Leb Lango and connect with your clan.')}</AppText>
        <Button title={t('Create an account')} onPress={() => router.push({ pathname: '/(auth)/register', params })} />
        <Button title={t('Sign in')} variant="light" onPress={() => router.push({ pathname: '/(auth)/login', params })} />
        <Button title={t('Keep browsing')} variant="ghost" onPress={() => router.replace('/(tabs)')} style={{ minHeight: 40 }} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.navy },
  top: { paddingHorizontal: SPACING.lg },
  bottom: { flex: 1, justifyContent: 'flex-end', paddingHorizontal: SPACING.lg, gap: 12 },
  headline: { fontFamily: FONTS.serif, fontSize: 31, lineHeight: 37, color: COLORS.white },
  sub: { fontFamily: FONTS.regular, fontSize: 15.5, lineHeight: 23, color: 'rgba(255,255,255,0.86)', marginBottom: 6 },
});
