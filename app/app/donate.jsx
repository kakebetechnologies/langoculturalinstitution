// Donate (POST /api/v1/donations): choose an amount and currency, a payment method (MTN MoMo, Airtel Money, Visa, Mastercard,
// PayPal, bank transfer), once / monthly / yearly, and where it goes: a program, "where it is needed most" or keeping the app running.
// The pledge is recorded and the LCI finance office confirms it once it arrives. How to pay with each method is what LCI wrote in Site
// settings (GET /giving); the screen shows it, and gives the reference to quote when paying.
import { useMemo, useState } from 'react';
import { KeyboardAvoidingView, Linking, Platform, Pressable, StyleSheet, View } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import Chip from '../components/Chip';
import KeyboardScroll from '../components/KeyboardScroll';
import ScreenHeader from '../components/ScreenHeader';
import Segmented from '../components/Segmented';
import Select from '../components/Select';
import TextField from '../components/TextField';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';
import api, { errorMessage, fieldErrors } from '../lib/api';
import { useAuth } from '../lib/auth';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

const METHODS = [['mtn_momo', 'MTN Mobile Money'], ['airtel_money', 'Airtel Money'], ['visa', 'Visa'], ['mastercard', 'Mastercard'], ['paypal', 'PayPal'], ['bank', 'Bank transfer']];
const PRESETS = { UGX: [10000, 20000, 50000, 100000, 200000], USD: [5, 10, 25, 50, 100] };

export default function Donate() {
  const { t } = useT();
  const { user } = useAuth();
  const { program, purpose } = useLocalSearchParams();
  const programs = useFetch('/programs');
  const giving = useFetch('/giving'); // how to pay with each method, as LCI wrote it in the admin panel
  const info = giving.data?.data;
  const [cur, setCur] = useState('UGX');
  // "Give to" is a program, "where it is needed most", or the running of the app itself (program_id "app")
  const [f, setF] = useState({ amount: '', method: 'mtn_momo', recurring: 'once', program_id: purpose === 'app' ? 'app' : typeof program === 'string' ? program : '', donor_name: user?.name ?? '', email: user?.email ?? '', phone: user?.phone ?? '', message: '', anonymous: false });
  const [busy, setBusy] = useState(false);
  const [errors, setErrors] = useState({});
  const [banner, setBanner] = useState('');
  const [done, setDone] = useState(null);
  const set = (k) => (v) => setF((x) => ({ ...x, [k]: v }));
  const how = info?.methods?.find((m) => m.key === f.method);
  const progOptions = useMemo(() => [{ value: '', label: t('Where it is needed most') }, { value: 'app', label: t('Keeping the Lango App running') }, ...(programs.data?.data ?? []).filter((p) => p.accepts_donations).map((p) => ({ value: String(p.id), label: p.title }))], [programs.data, t]);

  async function submit() {
    setBusy(true);
    setBanner('');
    setErrors({});
    try {
      const forApp = f.program_id === 'app';
      const { data } = await api.post('/donations', { ...f, program_id: forApp ? '' : f.program_id, purpose: forApp ? 'app' : 'general', currency: cur, amount: f.amount.replace(/,/g, ''), anonymous: f.anonymous ? 1 : 0 });
      setDone(data.data);
    } catch (e) {
      setErrors(fieldErrors(e));
      setBanner(errorMessage(e));
    } finally {
      setBusy(false);
    }
  }

  if (done) {
    return (
      <View style={styles.screen}>
        <ScreenHeader title={t('Thank you')} />
        <View style={{ padding: SPACING.lg, alignItems: 'center' }}>
          <AppText variant="title" style={{ textAlign: 'center' }}>
            {t('Thank you for your generosity')}
          </AppText>
          <AppText style={{ textAlign: 'center', marginTop: 8 }}>{done.message}</AppText>
          <Card style={{ alignSelf: 'stretch', alignItems: 'center', marginTop: SPACING.lg, borderColor: COLORS.yellow, borderWidth: 2, borderStyle: 'dashed' }}>
            <AppText style={{ fontFamily: FONTS.serif, fontSize: 20, color: COLORS.black }}>
              {done.currency} {Number(done.amount).toLocaleString('en-US')} · {done.method}
            </AppText>
            <AppText variant="caption">{t('Your reference')}</AppText>
            <AppText style={styles.ref} selectable>
              {done.reference}
            </AppText>
          </Card>
          {done.pay?.instructions ? (
            <Card style={{ alignSelf: 'stretch', marginTop: SPACING.md }}>
              <AppText variant="label">{t('How to pay')}</AppText>
              <AppText selectable style={{ marginTop: 4 }}>
                {done.pay.instructions}
              </AppText>
            </Card>
          ) : null}
          {done.pay?.url ? <Button title={t('Open the payment page')} icon="open-outline" onPress={() => Linking.openURL(done.pay.url).catch(() => {})} style={{ alignSelf: 'stretch', marginTop: SPACING.md }} /> : null}
          <Button title={t('Done')} variant={done.pay?.url ? 'outline' : 'primary'} onPress={() => router.replace('/(tabs)')} style={{ alignSelf: 'stretch', marginTop: SPACING.md }} />
        </View>
      </View>
    );
  }

  return (
    <KeyboardAvoidingView style={styles.screen} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScreenHeader title={t('Donate')} />
      <KeyboardScroll contentContainerStyle={{ padding: SPACING.md, paddingBottom: SPACING.xxl }} keyboardShouldPersistTaps="handled">
        {banner ? (
          <View style={styles.banner} accessibilityRole="alert">
            <AppText variant="label" style={{ color: COLORS.danger }}>
              {banner}
            </AppText>
          </View>
        ) : null}
        <Segmented value={cur} onChange={(v) => { setCur(v); set('amount')(''); }} options={[{ value: 'UGX', label: 'UGX' }, { value: 'USD', label: 'USD' }]} style={{ marginBottom: 12 }} />
        <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginBottom: 12 }}>
          {PRESETS[cur].map((a) => (
            <Chip key={a} label={a.toLocaleString('en-US')} on={f.amount === String(a)} onPress={() => set('amount')(String(a))} />
          ))}
        </View>
        <TextField label={t('Amount ({c})', { c: cur })} value={f.amount} onChangeText={(v) => set('amount')(v.replace(/[^\d.,]/g, ''))} error={errors.amount} keyboardType="decimal-pad" placeholder={cur === 'UGX' ? '50000' : '25'} />
        <Select label={t('Payment method')} value={f.method} options={METHODS.map(([v, l]) => ({ value: v, label: l }))} onChange={set('method')} />
        {how?.instructions ? (
          <Card style={{ marginBottom: 12 }}>
            <AppText variant="label">{t('How to pay')}</AppText>
            <AppText selectable style={{ marginTop: 4 }}>
              {how.instructions}
            </AppText>
          </Card>
        ) : null}
        <AppText variant="label" style={{ marginBottom: 6 }}>
          {t('How often?')}
        </AppText>
        <Segmented value={f.recurring} onChange={set('recurring')} options={[{ value: 'once', label: t('Once') }, { value: 'monthly', label: t('Monthly') }, { value: 'yearly', label: t('Yearly') }]} style={{ marginBottom: 16 }} />
        <Select label={t('Give to')} value={f.program_id} options={progOptions} onChange={set('program_id')} />
        {f.program_id === 'app' && info?.app_text ? (
          <AppText variant="caption" style={{ marginBottom: 12 }}>
            {info.app_text}
          </AppText>
        ) : null}
        <TextField label={t('Your name')} value={f.donor_name} onChangeText={set('donor_name')} error={errors.donor_name} />
        <TextField label={t('Email')} value={f.email} onChangeText={set('email')} error={errors.email} autoCapitalize="none" keyboardType="email-address" />
        <TextField label={t('Phone (for mobile money instructions)')} value={f.phone} onChangeText={set('phone')} keyboardType="phone-pad" />
        <TextField label={t('Message (optional)')} value={f.message} onChangeText={set('message')} multiline maxLength={500} />
        <Pressable onPress={() => set('anonymous')(!f.anonymous)} style={styles.agree} accessibilityRole="checkbox" accessibilityState={{ checked: f.anonymous }}>
          <View style={[styles.box, f.anonymous && styles.boxOn]}>{f.anonymous ? <AppText style={{ color: COLORS.white, fontSize: 13, lineHeight: 15 }}>✓</AppText> : null}</View>
          <AppText style={{ flex: 1 }}>{t('Give anonymously')}</AppText>
        </Pressable>
        <Button title={t('Record my pledge')} icon="heart" onPress={submit} loading={busy} />
        <AppText variant="caption" style={{ marginTop: 10 }}>
          {how?.instructions || how?.url
            ? t('Your pledge is recorded now. Pay as shown above and quote your reference; LCI confirms your gift when it arrives.')
            : t('Online payment is being connected. Your pledge is recorded now and the LCI finance office will contact you with payment instructions for your chosen method.')}
        </AppText>
      </KeyboardScroll>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  banner: { backgroundColor: COLORS.primaryMist, borderRadius: RADIUS.md, padding: SPACING.md, marginBottom: SPACING.md },
  ref: { fontFamily: FONTS.serif, fontSize: 30, letterSpacing: 4, color: COLORS.primaryDark, marginTop: 2 },
  agree: { flexDirection: 'row', gap: 10, alignItems: 'center', marginBottom: 16 },
  box: { width: 22, height: 22, borderRadius: 6, borderWidth: 2, borderColor: COLORS.gray300, alignItems: 'center', justifyContent: 'center', backgroundColor: COLORS.white },
  boxOn: { backgroundColor: COLORS.primary, borderColor: COLORS.primary },
});
