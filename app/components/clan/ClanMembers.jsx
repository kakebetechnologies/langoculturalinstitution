// Who is in a clan group: the total, then the people who chose to be listed (some hide themselves but are still counted).
// Also the member's own choices: show me in the list or not, and leave the group.
import { useState } from 'react';
import { Alert, ScrollView, StyleSheet, Switch, View } from 'react-native';
import AppText from '../AppText';
import Button from '../Button';
import Card from '../Card';
import Pill from '../Pill';
import Row from '../Row';
import StateView from '../StateView';
import { COLORS, SPACING } from '../../constants/theme';
import api, { errorMessage } from '../../lib/api';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function ClanMembers({ clanId, clanName, hidden, onChanged, onLeft }) {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch(`/clans/${clanId}/members`, { params: { limit: 100 }, cache: false });
  const [busy, setBusy] = useState(false);
  const [problem, setProblem] = useState(null);
  const rows = data?.data ?? [];
  const meta = data?.meta;

  async function toggle(value) {
    setBusy(true);
    setProblem(null);
    try {
      await api.post(`/clans/${clanId}/visibility`, { hidden: !value });
      await onChanged?.();
      reload();
    } catch (e) {
      setProblem(errorMessage(e));
    } finally {
      setBusy(false);
    }
  }

  async function leave() {
    try {
      await api.post(`/clans/${clanId}/leave`, {});
      await onLeft?.();
    } catch (e) {
      setProblem(errorMessage(e));
    }
  }

  function confirmLeave() {
    const message = t('You will stop seeing the messages and the member list. You can join again later.');
    if (globalThis.confirm) {
      if (globalThis.confirm(`${t('Leave the {clan} group?', { clan: clanName })}\n\n${message}`)) leave();
      return;
    }
    Alert.alert(t('Leave the {clan} group?', { clan: clanName }), message, [
      { text: t('Cancel'), style: 'cancel' },
      { text: t('Leave'), style: 'destructive', onPress: leave },
    ]);
  }

  return (
    <StateView loading={loading && !data} error={error} onRetry={retry}>
      <ScrollView contentContainerStyle={{ padding: SPACING.md, paddingBottom: SPACING.xxl }}>
        <Card style={styles.total}>
          <AppText style={styles.count}>{meta?.group_size ?? rows.length}</AppText>
          <View style={{ flex: 1 }}>
            <AppText variant="subheading">{t('people in the {clan} group', { clan: clanName })}</AppText>
            {meta?.hidden ? <AppText variant="caption">{t('{n} of them keep their name private.', { n: meta.hidden })}</AppText> : null}
          </View>
        </Card>

        <Card style={styles.pref}>
          <View style={{ flex: 1 }}>
            <AppText variant="subheading">{t('Show my name in the list')}</AppText>
            <AppText variant="caption">{t('Other members of your clan can see it. You are always counted.')}</AppText>
          </View>
          <Switch value={!hidden} onValueChange={toggle} disabled={busy} trackColor={{ true: COLORS.primary, false: COLORS.gray300 }} thumbColor={COLORS.white} />
        </Card>
        {problem ? (
          <AppText variant="label" style={{ color: COLORS.danger, marginBottom: SPACING.sm }}>
            {problem}
          </AppText>
        ) : null}

        <View style={{ marginTop: SPACING.sm }}>
          {rows.map((m) => (
            <Row key={m.id} avatar={{ name: m.name }} title={m.you ? `${m.name} (${t('you')})` : m.name} subtitle={t(m.level)}>
              {m.leader ? <Pill label={t('Clan leader')} tone="gold" style={{ marginTop: 6 }} /> : null}
            </Row>
          ))}
        </View>
        {refreshing ? null : (
          <Button title={t('Leave the group')} variant="outline" onPress={confirmLeave} style={{ marginTop: SPACING.lg }} />
        )}
      </ScrollView>
    </StateView>
  );
}

const styles = StyleSheet.create({
  total: { flexDirection: 'row', alignItems: 'center', gap: 14, marginBottom: SPACING.sm },
  count: { fontSize: 34, lineHeight: 40, color: COLORS.primaryDark, fontFamily: 'ClarityCity_700Bold' },
  pref: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: SPACING.sm },
});
