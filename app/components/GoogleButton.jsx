// "Continue with Google": opens Google sign-in in the browser. Shows nothing until the website says Google sign-in is set up.
// A member who is already registered goes straight in; a new person is asked for their clan first (app/(auth)/google-clan.jsx).
import { useState } from 'react';
import { ActivityIndicator, Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../constants/theme';
import { errorMessage } from '../lib/api';
import { useAuth } from '../lib/auth';
import { GoogleSignInError, useGoogleAvailable } from '../lib/google';
import { useT } from '../lib/i18n';
import AppText from './AppText';

export default function GoogleButton({ next, title }) {
  const { t } = useT();
  const { signInWithGoogle } = useAuth();
  const available = useGoogleAvailable();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  if (!available) return null;

  async function press() {
    setLoading(true);
    setError(null);
    try {
      const result = await signInWithGoogle();
      if (result?.pending) router.push({ pathname: '/(auth)/google-clan', params: typeof next === 'string' ? { next } : {} });
      else if (result?.user) router.replace(typeof next === 'string' && next.startsWith('/') ? next : '/(tabs)');
    } catch (e) {
      setError(e instanceof GoogleSignInError ? e.message : errorMessage(e));
    } finally {
      setLoading(false);
    }
  }

  const label = title ?? t('Continue with Google');

  return (
    <View style={{ marginBottom: SPACING.md }}>
      <Pressable accessibilityRole="button" accessibilityLabel={label} accessibilityState={{ busy: loading, disabled: loading }} disabled={loading} onPress={press} style={({ pressed }) => [styles.button, pressed && { opacity: 0.85 }]}>
        {loading ? <ActivityIndicator color={COLORS.black} /> : <Ionicons name="logo-google" size={20} color="#4285F4" />}
        <AppText style={styles.text}>{loading ? t('Opening Google…') : label}</AppText>
      </Pressable>
      {error ? (
        <View style={styles.error} accessibilityRole="alert">
          <AppText variant="label" style={{ color: COLORS.danger }}>
            {error}
          </AppText>
        </View>
      ) : null}
      <View style={styles.or}>
        <View style={styles.line} />
        <AppText variant="caption">{t('or with your email')}</AppText>
        <View style={styles.line} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  button: { minHeight: 54, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 10, borderRadius: RADIUS.full, backgroundColor: COLORS.white, ...SHADOW, borderWidth: 1, borderColor: COLORS.gray100 },
  text: { fontFamily: FONTS.semibold, fontSize: 16, color: COLORS.black },
  error: { backgroundColor: COLORS.dangerMist, borderRadius: RADIUS.md, padding: SPACING.md, marginTop: SPACING.sm },
  or: { flexDirection: 'row', alignItems: 'center', gap: 12, marginTop: SPACING.md },
  line: { flex: 1, height: 1, backgroundColor: COLORS.line },
});
