// Professionals directory (GET /api/v1/professionals): verified Lango professionals by industry and expertise. Members can add
// themselves (POST /api/v1/professionals); a moderator verifies each profile before it appears.
import { useState } from 'react';
import { ScrollView, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import Chip from '../components/Chip';
import Row from '../components/Row';
import Screen from '../components/Screen';
import Sheet from '../components/Sheet';
import StateView from '../components/StateView';
import TextField from '../components/TextField';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS, SPACING } from '../constants/theme';
import api, { errorMessage, fieldErrors } from '../lib/api';
import { useAuth } from '../lib/auth';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';
import { router } from 'expo-router';

export default function Professionals() {
  const { t } = useT();
  const { user } = useAuth();
  const [q, setQ] = useState('');
  const [industry, setIndustry] = useState('');
  const { data, loading, error, retry, reload, refreshing } = useFetch('/professionals', { params: { q: q.trim() || undefined, industry: industry || undefined } });
  const rows = data?.data ?? [];
  const [join, setJoin] = useState(false);
  const [f, setF] = useState({ industry: '', expertise: '', location: '', bio: '' });
  const [busy, setBusy] = useState(false);
  const [msg, setMsg] = useState({ ok: false, text: '' });
  const [errors, setErrors] = useState({});

  async function submit() {
    setBusy(true);
    setMsg({ ok: false, text: '' });
    try {
      const { data: res } = await api.post('/professionals', { ...f, clan_id: user?.clan ? String(user.clan.id) : undefined });
      setMsg({ ok: true, text: res.message });
      setErrors({});
    } catch (e) {
      setErrors(fieldErrors(e));
      setMsg({ ok: false, text: errorMessage(e) });
    } finally {
      setBusy(false);
    }
  }

  return (
    <Screen title={t('Professionals')} onRefresh={reload} refreshing={refreshing}>
      <View style={styles.search}>
        <Ionicons name="search" size={20} color={COLORS.gray700} />
        <TextInput value={q} onChangeText={setQ} placeholder={t('Name, expertise, industry…')} placeholderTextColor={COLORS.gray500} maxFontSizeMultiplier={MAX_FONT_SCALE} style={styles.input} autoCapitalize="none" />
      </View>
      {data?.industries?.length ? (
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 8, paddingVertical: 12 }}>
          <Chip label={t('All')} on={industry === ''} onPress={() => setIndustry('')} />
          {data.industries.map((i) => (
            <Chip key={i} label={i} on={industry === i} onPress={() => setIndustry(i)} />
          ))}
        </ScrollView>
      ) : (
        <View style={{ height: 12 }} />
      )}
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!rows.length} emptyIcon="briefcase-outline" emptyTitle="The directory is just starting" emptyMessage="Be among the first Lango professionals listed, and help mentor the next generation.">
        {rows.map((p) => (
          <Row key={p.id} avatar={{ name: p.name, photo: p.photo }} title={p.name} subtitle={[p.expertise, p.clan ? `${p.clan} ${t('clan')}` : '', p.location].filter(Boolean).join(' · ')} meta={p.industry} />
        ))}
      </StateView>
      <Button title={t('Add yourself')} icon="add" onPress={() => (user ? setJoin(true) : router.push({ pathname: '/(auth)/welcome', params: { next: '/professionals' } }))} style={{ marginTop: SPACING.md }} />

      <Sheet visible={join} title={t('Join the directory')} onClose={() => setJoin(false)} tall>
        <ScrollView keyboardShouldPersistTaps="handled">
          {msg.text ? (
            <Card style={{ marginBottom: 12, backgroundColor: msg.ok ? COLORS.skyMist : COLORS.primaryMist }}>
              <AppText variant="label" style={{ color: msg.ok ? COLORS.skyDark : COLORS.danger }}>
                {msg.text}
              </AppText>
            </Card>
          ) : null}
          <TextField label={t('Industry / profession')} value={f.industry} onChangeText={(v) => setF((x) => ({ ...x, industry: v }))} error={errors.industry} placeholder={t('e.g. Medicine, Law, Engineering, Agriculture')} />
          <TextField label={t('Expertise')} value={f.expertise} onChangeText={(v) => setF((x) => ({ ...x, expertise: v }))} />
          <TextField label={t('Where you work')} value={f.location} onChangeText={(v) => setF((x) => ({ ...x, location: v }))} />
          <TextField label={t('Short bio')} value={f.bio} onChangeText={(v) => setF((x) => ({ ...x, bio: v }))} multiline />
          <Button title={t('Submit for verification')} onPress={submit} loading={busy} />
        </ScrollView>
      </Sheet>
    </Screen>
  );
}

const styles = StyleSheet.create({
  search: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1.5, borderColor: COLORS.gray300, paddingHorizontal: 14 },
  input: { flex: 1, minHeight: 48, fontFamily: FONTS.regular, fontSize: 15.5, color: COLORS.black },
});
