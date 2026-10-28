// The frame of the sign-in and sign-up screens (the FaithFellows look): a deep gradient header with the logo, a title and a line of
// text, and a white sheet with rounded top corners that holds the form. Scrolls above the keyboard.
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import AppText from './AppText';
import HeroHeader from './HeroHeader';
import KeyboardScroll from './KeyboardScroll';
import Logo from './Logo';
import { BackButton } from './ScreenHeader';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';

export default function AuthFrame({ title, subtitle, children }) {
  return (
    <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <KeyboardScroll contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
        <HeroHeader style={styles.hero}>
          <View style={styles.back}>
            <BackButton onDark onPress={() => (router.canGoBack() ? router.back() : router.replace('/(auth)/welcome'))} />
          </View>
          <View style={styles.center}>
            <Logo tile size={58} />
            <AppText style={styles.title}>{title}</AppText>
            {subtitle ? <AppText style={styles.sub}>{subtitle}</AppText> : null}
          </View>
        </HeroHeader>
        <View style={styles.sheet}>{children}</View>
      </KeyboardScroll>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: COLORS.white },
  scroll: { flexGrow: 1 },
  hero: { paddingBottom: 54 },
  back: { alignSelf: 'flex-start' },
  center: { alignItems: 'center', marginTop: 4 },
  title: { fontFamily: FONTS.bold, fontSize: 26, lineHeight: 32, color: COLORS.white, marginTop: 14, textAlign: 'center' },
  sub: { fontFamily: FONTS.regular, fontSize: 14.5, lineHeight: 21, color: 'rgba(255,255,255,0.82)', marginTop: 4, textAlign: 'center' },
  sheet: { flexGrow: 1, marginTop: -30, backgroundColor: COLORS.white, borderTopLeftRadius: RADIUS.xl + 4, borderTopRightRadius: RADIUS.xl + 4, padding: SPACING.lg, paddingBottom: SPACING.xxl },
});
