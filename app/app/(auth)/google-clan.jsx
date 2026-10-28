// The last step for a new person who signed up with Google: choose the clan they belong to (every member has one, and the clan
// group is for people of the same clan), accept the terms, and the account is created. Opened from GoogleButton.
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import { Redirect, router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import AuthFrame from '../../components/AuthFrame';
import Button from '../../components/Button';
import ClanPicker from '../../components/ClanPicker';
import { COLORS, FONTS, SPACING } from '../../constants/theme';
import { errorMessage, fieldErrors } from '../../lib/api';
import { useAuth } from '../../lib/auth';
import { getPendingGoogle, setPendingGoogle } from '../../lib/google';
import { useT } from '../../lib/i18n';

export default function GoogleClan() {
  const { t } = useT();
  const { completeGoogle } = useAuth();
  const { next } = useLocalSearchParams();
  const pending = getPendingGoogle();
  const [form, setForm] = useState({ clan_id: '', clan_other: '' });
  const [agree, setAgree] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [errors, setErrors] = useState({});

  if (!pending) return <Redirect href="/(auth)/welcome" />;

  async function submit() {
    const e = {};
    if (!form.clan_id) e.clan_id = t('Please choose your clan.');
    if (form.clan_id === 'other' && !form.clan_other.trim()) e.clan_other = t('Please type the name of your clan.');
    if (!agree) e.agree = t('Please accept the terms to continue.');
    setErrors(e);
    if (Object.keys(e).length) return;

    setLoading(true);
    setError(null);
    try {
      await completeGoogle(pending, form);
      setPendingGoogle(null);
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
    <AuthFrame title={t('One more step')} subtitle={pending.profile?.email}>
      <AppText variant="heading">{t('Which clan do you belong to?')}</AppText>
      <AppText variant="caption" style={{ marginTop: 4, marginBottom: SPACING.md }}>
        {t('Every member has a clan. People of the same clan can join their clan group, see who else is in it and talk together.')}
      </AppText>

      {error ? (
        <View style={styles.banner} accessibilityRole="alert">
          <AppText variant="label" style={{ color: COLORS.danger }}>
            {error}
          </AppText>
        </View>
      ) : null}

      <ClanPicker value={form} onChange={(v) => setForm((f) => ({ ...f, ...v }))} error={errors.clan_id} otherError={errors.clan_other} />

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

      <Button title={t('Join the Lango community')} onPress={submit} loading={loading} style={{ marginTop: SPACING.sm }} />
    </AuthFrame>
  );
}

const styles = StyleSheet.create({
  banner: { backgroundColor: COLORS.dangerMist, borderRadius: 12, padding: SPACING.md, marginBottom: SPACING.md },
  agree: { flexDirection: 'row', gap: 10, alignItems: 'flex-start', marginBottom: 8 },
  box: { width: 22, height: 22, borderRadius: 6, borderWidth: 2, borderColor: COLORS.gray300, alignItems: 'center', justifyContent: 'center', marginTop: 1, backgroundColor: COLORS.white },
  boxOn: { backgroundColor: COLORS.primary, borderColor: COLORS.primary },
  link: { color: COLORS.primaryDark, fontFamily: FONTS.bold },
});
