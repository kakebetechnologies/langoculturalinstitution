// Edit profile (PATCH /api/v1/me): name, phone, country and clan; change password (POST /me/password); delete the account
// (DELETE /me — the app stores require it; contributions stay in the archive without the person's link).
import { useState } from 'react';
import { Alert, KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import ClanPicker from '../components/ClanPicker';
import KeyboardScroll from '../components/KeyboardScroll';
import ScreenHeader from '../components/ScreenHeader';
import TextField from '../components/TextField';
import { COLORS, SPACING } from '../constants/theme';
import api, { errorMessage, fieldErrors } from '../lib/api';
import { useAuth } from '../lib/auth';
import { useT } from '../lib/i18n';

export default function Profile() {
  const { t } = useT();
  const { user, setUser, signOut } = useAuth();
  const [f, setF] = useState({ name: user?.name ?? '', phone: user?.phone ?? '', country: user?.country ?? '' });
  const [clan, setClan] = useState({ clan_id: user?.clan ? String(user.clan.id) : user?.clan_other ? 'other' : '', clan_other: user?.clan_other ?? '' });
  const [pw, setPw] = useState({ current_password: '', password: '' });
  const [busy, setBusy] = useState('');
  const [msg, setMsg] = useState({ ok: true, text: '' });
  const [errors, setErrors] = useState({});
  const [delPw, setDelPw] = useState('');
  const [showDelete, setShowDelete] = useState(false);

  async function save() {
    setBusy('save');
    setErrors({});
    try {
      const { data } = await api.patch('/me', { ...f, clan_id: clan.clan_id || undefined, clan_other: clan.clan_other });
      setUser(data.data);
      setMsg({ ok: true, text: t('Profile saved.') });
    } catch (e) {
      setErrors(fieldErrors(e));
      setMsg({ ok: false, text: errorMessage(e) });
    } finally {
      setBusy('');
    }
  }

  async function changePassword() {
    setBusy('pw');
    setErrors({});
    try {
      await api.post('/me/password', pw);
      setPw({ current_password: '', password: '' });
      setMsg({ ok: true, text: t('Password changed.') });
    } catch (e) {
      setErrors(fieldErrors(e));
      setMsg({ ok: false, text: errorMessage(e) });
    } finally {
      setBusy('');
    }
  }

  async function deleteAccount() {
    setBusy('del');
    try {
      await api.delete('/me', { data: { password: delPw } });
      await signOut();
      router.replace('/(tabs)');
    } catch (e) {
      setErrors(fieldErrors(e));
      setMsg({ ok: false, text: errorMessage(e) });
    } finally {
      setBusy('');
    }
  }

  function askDelete() {
    const go = () => setShowDelete(true);
    if (globalThis.confirm) {
      if (globalThis.confirm(t('Delete your account? This cannot be undone.'))) go();
      return;
    }
    Alert.alert(t('Delete account'), t('Delete your account? This cannot be undone.'), [
      { text: t('Cancel'), style: 'cancel' },
      { text: t('Continue'), style: 'destructive', onPress: go },
    ]);
  }

  return (
    <KeyboardAvoidingView style={styles.screen} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScreenHeader title={t('Edit profile')} />
      <KeyboardScroll contentContainerStyle={{ padding: SPACING.md, paddingBottom: SPACING.xxl }} keyboardShouldPersistTaps="handled">
        {msg.text ? (
          <View style={[styles.msg, { backgroundColor: msg.ok ? COLORS.skyMist : COLORS.primaryMist }]} accessibilityRole="alert">
            <AppText variant="label" style={{ color: msg.ok ? COLORS.skyDark : COLORS.danger }}>
              {msg.text}
            </AppText>
          </View>
        ) : null}
        <TextField label={t('Full name')} value={f.name} onChangeText={(v) => setF((x) => ({ ...x, name: v }))} error={errors.name} />
        <ClanPicker value={clan} onChange={setClan} error={errors.clan_id} otherError={errors.clan_other} />
        <TextField label={t('Phone')} value={f.phone} onChangeText={(v) => setF((x) => ({ ...x, phone: v }))} keyboardType="phone-pad" />
        <TextField label={t('Country')} value={f.country} onChangeText={(v) => setF((x) => ({ ...x, country: v }))} />
        <Button title={t('Save profile')} onPress={save} loading={busy === 'save'} />

        <Card style={{ marginTop: SPACING.xl }}>
          <AppText variant="heading" style={{ marginBottom: 10 }}>
            {t('Change password')}
          </AppText>
          <TextField label={t('Current password')} value={pw.current_password} onChangeText={(v) => setPw((x) => ({ ...x, current_password: v }))} error={errors.current_password} secureTextEntry />
          <TextField label={t('New password')} value={pw.password} onChangeText={(v) => setPw((x) => ({ ...x, password: v }))} error={errors.password} secureTextEntry hint={t('At least 8 characters.')} />
          <Button title={t('Change password')} variant="outline" onPress={changePassword} loading={busy === 'pw'} />
        </Card>

        <Card style={{ marginTop: SPACING.lg, borderColor: '#F6C7C9' }}>
          <AppText variant="heading" style={{ marginBottom: 6 }}>
            {t('Delete account')}
          </AppText>
          <AppText variant="caption" style={{ marginBottom: 10 }}>
            {t('Deletes your account, points and badges. Contributions already in the archive stay, without your name link.')}
          </AppText>
          {showDelete ? (
            <>
              <TextField label={t('Enter your password to confirm')} value={delPw} onChangeText={setDelPw} error={errors.password} secureTextEntry />
              <Button title={t('Delete my account')} onPress={deleteAccount} loading={busy === 'del'} />
            </>
          ) : (
            <Button title={t('Delete account')} variant="outline" onPress={askDelete} />
          )}
        </Card>
      </KeyboardScroll>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  msg: { borderRadius: 8, padding: SPACING.md, marginBottom: SPACING.md },
});
