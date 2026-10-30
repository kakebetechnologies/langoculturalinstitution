// The frame of the sign-in and sign-up screens: the logo, a title and a line of text on the soft grey page, and the form in a white
// rounded card. Scrolls above the keyboard.
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import AppText from './AppText';
import KeyboardScroll from './KeyboardScroll';
import Logo from './Logo';
import { BackButton } from './ScreenHeader';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../constants/theme';

export default function AuthFrame({ title, subtitle, children }) {
  const insets = useSafeAreaInsets();

  return (
    <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <KeyboardScroll contentContainerStyle={styles.scroll} keyboardShouldPersistTaps="handled">
        <View style={{ paddingTop: insets.top + 8, paddingHorizontal: SPACING.md }}>
          <BackButton onPress={() => (router.canGoBack() ? router.back() : router.replace('/(auth)/welcome'))} />
        </View>
        <View style={styles.center}>
          <Logo tile size={62} />
          <AppText style={styles.title}>{title}</AppText>
          {subtitle ? <AppText style={styles.sub}>{subtitle}</AppText> : null}
        </View>
        <View style={styles.sheet}>{children}</View>
      </KeyboardScroll>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: COLORS.canvas },
  scroll: { flexGrow: 1, paddingBottom: SPACING.xxl },
  center: { alignItems: 'center', paddingHorizontal: SPACING.lg, marginTop: 2, marginBottom: SPACING.lg },
  title: { fontFamily: FONTS.bold, fontSize: 26, lineHeight: 32, color: COLORS.black, marginTop: 14, textAlign: 'center' },
  sub: { fontFamily: FONTS.regular, fontSize: 14.5, lineHeight: 21, color: COLORS.gray900, marginTop: 4, textAlign: 'center' },
  sheet: { marginHorizontal: SPACING.md, backgroundColor: COLORS.white, borderRadius: RADIUS.xl + 2, padding: SPACING.lg, ...SHADOW },
});
