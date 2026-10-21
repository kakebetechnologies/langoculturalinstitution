// Track a contribution by its code (GET /api/v1/contributions/track): where it is in review — Pending, Under review, Approved,
// Published — plus the reviewers' note, points awarded and any attached file. Opens with the code when you tap one of your own.
import { useEffect, useState } from 'react';
import { Linking, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import Card from '../../components/Card';
import Pill from '../../components/Pill';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import TextField from '../../components/TextField';
import { COLORS, FONTS, SPACING } from '../../constants/theme';
import { STATUS_TONE } from '../../constants/contribute';
import api, { errorMessage, mediaUrl } from '../../lib/api';
import { fmtDate } from '../../lib/format';
import { useT } from '../../lib/i18n';

const ORDER = ['pending', 'under_review', 'approved', 'published'];

export default function Track() {
  const { t } = useT();
  const params = useLocalSearchParams();
  const [code, setCode] = useState(typeof params.code === 'string' ? params.code : '');
  const [state, setState] = useState({ loading: false, error: null, data: null });

  async function load(c = code) {
    const value = c.trim().toUpperCase();
    if (!value) return;
    setState({ loading: true, error: null, data: null });
    try {
      const { data } = await api.get('/contributions/track', { params: { code: value } });
      setState({ loading: false, error: null, data: data.data });
    } catch (e) {
      setState({ loading: false, error: errorMessage(e), data: null });
    }
  }

  useEffect(() => {
    if (typeof params.code === 'string' && params.code) load(params.code);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [params.code]);

  const c = state.data;
  const at = c ? ORDER.indexOf(c.status) : -1;

  return (
    <Screen title={t('Track a contribution')}>
      <TextField label={t('Tracking code')} value={code} onChangeText={setCode} autoCapitalize="characters" maxLength={16} placeholder="ABCD1234" returnKeyType="search" onSubmitEditing={() => load()} />
      <Button title={t('Track')} onPress={() => load()} loading={state.loading} />

      {state.error ? (
        <View style={{ marginTop: SPACING.lg }}>
          <StateView error={state.error} onRetry={() => load()} />
        </View>
      ) : null}

      {c ? (
        <Card style={{ marginTop: SPACING.lg }}>
          <AppText variant="eyebrow">{t(c.type_label)}</AppText>
          <AppText variant="heading" style={{ marginTop: 2 }}>
            {c.title}
          </AppText>
          <AppText variant="caption">
            {fmtDate(c.created_at)} · {c.code}
          </AppText>

          <View style={styles.steps}>
            {ORDER.map((s, i) => (
              <View key={s} style={[styles.step, at >= i && styles.stepOn]} />
            ))}
          </View>
          <View style={styles.labels}>
            {['Pending', 'Under review', 'Approved', 'Published'].map((l) => (
              <AppText key={l} variant="caption" style={{ flex: 1, textAlign: 'center', fontSize: 11 }}>
                {t(l)}
              </AppText>
            ))}
          </View>

          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: SPACING.md }}>
            <AppText variant="label">{t('Status')}:</AppText>
            <Pill label={t(c.status_label)} tone={STATUS_TONE[c.status] ?? 'grey'} />
          </View>
          {c.review_note ? (
            <View style={styles.note}>
              <AppText variant="label">{t('Note from our reviewers')}</AppText>
              <AppText style={{ marginTop: 4 }}>{c.review_note}</AppText>
            </View>
          ) : null}
          {c.points_awarded ? (
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: SPACING.md }}>
              <Ionicons name="ribbon" size={18} color={COLORS.yellowInk} />
              <AppText style={{ fontFamily: FONTS.semibold }}>{t('{n} points awarded', { n: c.points_awarded })}</AppText>
            </View>
          ) : null}
          {c.media_url ? <Button small variant="outline" title={t('Open the attached file')} icon="open-outline" onPress={() => Linking.openURL(mediaUrl(c.media_url))} style={{ marginTop: SPACING.md, alignSelf: 'flex-start' }} /> : null}
        </Card>
      ) : null}
    </Screen>
  );
}

const styles = StyleSheet.create({
  steps: { flexDirection: 'row', gap: 6, marginTop: SPACING.md },
  step: { flex: 1, height: 6, borderRadius: 3, backgroundColor: COLORS.gray300 },
  stepOn: { backgroundColor: COLORS.primary },
  labels: { flexDirection: 'row', gap: 6, marginTop: 6 },
  note: { backgroundColor: COLORS.yellowMist, borderRadius: 8, padding: SPACING.md, marginTop: SPACING.md },
});
