// Sign in with email or phone number and password (POST /api/v1/auth/login). After signing in the person goes to the page they
// came from (?next=) or the home tab.
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';
import { Link, router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import KeyboardScroll from '../../components/KeyboardScroll';
import Logo from '../../components/Logo';
import { BackButton } from '../../components/ScreenHeader';
import TextField from '../../components/TextField';
import { COLORS, SPACING } from '../../constants/theme';
import { errorMessage, fieldErrors } from '../../lib/api';
import { useAuth } from '../../lib/auth';
import { useT } from '../../lib/i18n';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

export default function Login() {
  const { t } = useT();
  const { signIn } = useAuth();
  const insets = useSafeAreaInsets();
  const { next } = useLocalSearchParams();
  const [login, setLogin] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [errors, setErrors] = useState({});

  async function submit() {
    if (!login.trim() || !password) {
      setErrors({ login: login.trim() ? undefined : t('Enter your email or phone number.'), password: password ? undefined : t('Enter your password.') });
      return;
    }
    setLoading(true);
    setError(null);
    setErrors({});
    try {
      await signIn(login, password);
      router.replace(typeof next === 'string' && next.startsWith('/') ? next : '/(tabs)');
    } catch (e) {
      const fields = fieldErrors(e);
      setErrors(fields);
      if (!Object.keys(fields).length) setError(errorMessage(e));
    } finally {
      setLoading(false);
    }
  }

  return (
    <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <KeyboardScroll contentContainerStyle={[styles.scroll, { paddingTop: insets.top + 12 }]} keyboardShouldPersistTaps="handled">
        <BackButton onPress={() => (router.canGoBack() ? router.back() : router.replace('/(auth)/welcome'))} />
        <View style={styles.hero}>
          <Logo size={78} />
          <AppText variant="title" style={{ marginTop: SPACING.md }}>
            {t('Welcome back')}
          </AppText>
          <AppText style={{ textAlign: 'center', marginTop: 4 }}>{t('Sign in to contribute, track your contributions and earn points.')}</AppText>
        </View>

        {error ? (
          <View style={styles.banner} accessibilityRole="alert">
            <AppText variant="label" style={{ color: COLORS.danger }}>
              {error}
            </AppText>
          </View>
        ) : null}

        <TextField label={t('Email or phone number')} value={login} onChangeText={setLogin} error={errors.login} autoCapitalize="none" autoComplete="username" keyboardType="email-address" placeholder="you@example.com" returnKeyType="next" />
        <TextField label={t('Password')} value={password} onChangeText={setPassword} error={errors.password} secureTextEntry autoComplete="password" placeholder={t('Your password')} returnKeyType="go" onSubmitEditing={submit} />

        <Button title={t('Sign in')} onPress={submit} loading={loading} style={{ marginTop: SPACING.sm }} />

        <View style={styles.switch}>
          <AppText>{t('New here?')} </AppText>
          <Link href={{ pathname: '/(auth)/register', params: typeof next === 'string' ? { next } : {} }} style={styles.link}>
            {t('Create an account')}
          </Link>
        </View>
      </KeyboardScroll>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1, backgroundColor: COLORS.canvas },
  scroll: { flexGrow: 1, padding: SPACING.lg, paddingBottom: SPACING.xxl },
  hero: { alignItems: 'center', paddingVertical: SPACING.lg },
  banner: { backgroundColor: COLORS.primaryMist, borderRadius: 8, padding: SPACING.md, marginBottom: SPACING.md },
  switch: { flexDirection: 'row', justifyContent: 'center', flexWrap: 'wrap', marginTop: SPACING.lg },
  link: { color: COLORS.primaryDark, fontWeight: '700' },
});
