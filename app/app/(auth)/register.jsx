// Create an account (POST /api/v1/auth/register): name, email and a password, and the person is signed in straight away. The clan is
// optional here (it can be chosen later in Me), and there is no checkbox to tick: agreeing to the terms is stated under the button.
import { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { Link, router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import AuthFrame from '../../components/AuthFrame';
import Button from '../../components/Button';
import ClanPicker from '../../components/ClanPicker';
import GoogleButton from '../../components/GoogleButton';
import TextField from '../../components/TextField';
import { COLORS, FONTS, SPACING } from '../../constants/theme';
import { errorMessage, fieldErrors } from '../../lib/api';
import { useAuth } from '../../lib/auth';
import { useT } from '../../lib/i18n';

export default function Register() {
  const { t } = useT();
  const { signUp } = useAuth();
  const { next } = useLocalSearchParams();
  const [form, setForm] = useState({ name: '', email: '', password: '', clan_id: '', clan_other: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [errors, setErrors] = useState({});
  const set = (k) => (v) => setForm((f) => ({ ...f, [k]: v }));

  async function submit() {
    const e = {};
    if (!form.name.trim()) e.name = t('Please enter your name.');
    if (!/^\S+@\S+\.\S+$/.test(form.email.trim())) e.email = t('Please enter a valid email address.');
    if (form.clan_id === 'other' && !form.clan_other.trim()) e.clan_other = t('Please type the name of your clan.');
    if (form.password.length < 8) e.password = t('Choose a password of at least 8 characters.');
    setErrors(e);
    if (Object.keys(e).length) return;

    setLoading(true);
    setError(null);
    try {
      await signUp(form);
      router.replace(typeof next === 'string' && next.startsWith('/') ? next : '/(tabs)');
    } catch (err) {
      const fields = fieldErrors(err);
      setErrors(fields);
      if (!Object.keys(fields).length) setError(errorMessage(err));
    } finally {
      setLoading(false);
    }
  }

  return (
    <AuthFrame title={t('Join the Lango community')} subtitle={t('It takes a minute, and you are signed in straight away.')}>
      <GoogleButton next={next} />

      {error ? (
        <View style={styles.banner} accessibilityRole="alert">
          <AppText variant="label" style={{ color: COLORS.danger }}>
            {error}
          </AppText>
        </View>
      ) : null}

      <TextField label={t('Full name')} value={form.name} onChangeText={set('name')} error={errors.name} autoComplete="name" placeholder={t('Your name')} />
      <TextField label={t('Email')} value={form.email} onChangeText={set('email')} error={errors.email} autoCapitalize="none" autoComplete="email" keyboardType="email-address" placeholder="you@example.com" />
      <TextField label={t('Password')} value={form.password} onChangeText={set('password')} error={errors.password} secureTextEntry autoComplete="new-password" hint={t('At least 8 characters.')} />
      <ClanPicker label={t('Your clan (optional)')} value={{ clan_id: form.clan_id, clan_other: form.clan_other }} onChange={(v) => setForm((f) => ({ ...f, ...v }))} error={errors.clan_id} otherError={errors.clan_other} />

      <Button title={t('Create account')} onPress={submit} loading={loading} style={{ marginTop: SPACING.sm }} />

      <AppText variant="caption" style={styles.terms}>
        {t('By creating an account you agree to the')}{' '}
        <AppText variant="caption" style={styles.link} onPress={() => router.push('/legal/terms')}>
          {t('Terms of use')}
        </AppText>{' '}
        {t('and')}{' '}
        <AppText variant="caption" style={styles.link} onPress={() => router.push('/legal/privacy')}>
          {t('Privacy policy')}
        </AppText>
        .
      </AppText>

      <View style={styles.switch}>
        <AppText>{t('Already have an account?')} </AppText>
        <Link href={{ pathname: '/(auth)/login', params: typeof next === 'string' ? { next } : {} }} style={styles.link}>
          {t('Sign in')}
        </Link>
      </View>
    </AuthFrame>
  );
}

const styles = StyleSheet.create({
  banner: { backgroundColor: COLORS.dangerMist, borderRadius: 12, padding: SPACING.md, marginBottom: SPACING.md },
  terms: { textAlign: 'center', marginTop: SPACING.md },
  switch: { flexDirection: 'row', justifyContent: 'center', flexWrap: 'wrap', marginTop: SPACING.lg },
  link: { color: COLORS.primaryDark, fontFamily: FONTS.bold },
});
