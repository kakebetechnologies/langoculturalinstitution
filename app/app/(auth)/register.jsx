// Create an account (POST /api/v1/auth/register): name, email, optional phone and country, the member's clan (required, with
// "my clan is not listed" and "not sure" options) and a password. Signs the person in straight away.
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
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
  const [form, setForm] = useState({ name: '', email: '', phone: '', country: 'Uganda', password: '', clan_id: '', clan_other: '' });
  const [agree, setAgree] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [errors, setErrors] = useState({});
  const set = (k) => (v) => setForm((f) => ({ ...f, [k]: v }));

  async function submit() {
    const e = {};
    if (!form.name.trim()) e.name = t('Please enter your name.');
    if (!/^\S+@\S+\.\S+$/.test(form.email.trim())) e.email = t('Please enter a valid email address.');
    if (!form.clan_id) e.clan_id = t('Please choose your clan.');
    if (form.clan_id === 'other' && !form.clan_other.trim()) e.clan_other = t('Please type the name of your clan.');
    if (form.password.length < 8) e.password = t('Choose a password of at least 8 characters.');
    if (!agree) e.agree = t('Please accept the terms to continue.');
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
    <AuthFrame title={t('Join the Lango community')} subtitle={t('Earn points and badges, and track every contribution you make.')}>
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
      <ClanPicker value={{ clan_id: form.clan_id, clan_other: form.clan_other }} onChange={(v) => setForm((f) => ({ ...f, ...v }))} error={errors.clan_id} otherError={errors.clan_other} />
      <View style={{ flexDirection: 'row', gap: 12 }}>
        <TextField label={t('Country')} value={form.country} onChangeText={set('country')} style={{ flex: 1 }} />
        <TextField label={t('Phone (optional)')} value={form.phone} onChangeText={set('phone')} keyboardType="phone-pad" style={{ flex: 1.2 }} placeholder="+256…" />
      </View>
      <TextField label={t('Password')} value={form.password} onChangeText={set('password')} error={errors.password} secureTextEntry autoComplete="new-password" hint={t('At least 8 characters.')} />

      <Pressable onPress={() => setAgree((a) => !a)} style={styles.agree} accessibilityRole="checkbox" accessibilityState={{ checked: agree }}>
        <View style={[styles.box, agree && styles.boxOn]}>{agree ? <AppText style={{ color: COLORS.white, fontSize: 13, lineHeight: 15 }}>✓</AppText> : null}</View>
        <AppText style={{ flex: 1 }}>
          {t('I agree to the')}{' '}
          <AppText style={styles.link} onPress={() => router.push('/legal/terms')}>
            {t('Terms of use')}
          </AppText>{' '}
          {t('and')}{' '}
          <AppText style={styles.link} onPress={() => router.push('/legal/privacy')}>
            {t('Privacy policy')}
          </AppText>
          .
        </AppText>
      </Pressable>
      {errors.agree ? (
        <AppText variant="caption" style={{ color: COLORS.danger, marginBottom: 8 }}>
          {errors.agree}
        </AppText>
      ) : null}

      <Button title={t('Create account')} onPress={submit} loading={loading} style={{ marginTop: SPACING.sm }} />

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
  agree: { flexDirection: 'row', gap: 10, alignItems: 'flex-start', marginBottom: 8 },
  box: { width: 22, height: 22, borderRadius: 6, borderWidth: 2, borderColor: COLORS.gray300, alignItems: 'center', justifyContent: 'center', marginTop: 1, backgroundColor: COLORS.white },
  boxOn: { backgroundColor: COLORS.primary, borderColor: COLORS.primary },
  switch: { flexDirection: 'row', justifyContent: 'center', flexWrap: 'wrap', marginTop: SPACING.lg },
  link: { color: COLORS.primaryDark, fontFamily: FONTS.bold },
});
