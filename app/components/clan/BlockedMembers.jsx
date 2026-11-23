// The members of the clan group that the person has blocked, with a button to unblock each (Settings, Blocked members). A blocked member's
// messages and name are hidden from the one who blocked them; nothing is told to the blocked member.
import { StyleSheet, View } from 'react-native';
import AppText from '../AppText';
import Button from '../Button';
import Card from '../Card';
import StateView from '../StateView';
import { SPACING } from '../../constants/theme';
import api, { errorMessage } from '../../lib/api';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';
import { useState } from 'react';

export default function BlockedMembers({ clanId }) {
  const { t } = useT();
  const { data, loading, error, retry, reload } = useFetch(`/clans/${clanId}/blocks`, { cache: false });
  const [problem, setProblem] = useState(null);
  const rows = data?.data ?? [];

  async function unblock(id) {
    setProblem(null);
    try {
      await api.delete(`/clans/${clanId}/blocks/${id}`);
      reload();
    } catch (e) {
      setProblem(errorMessage(e));
    }
  }

  return (
    <StateView loading={loading && !data} error={error} onRetry={retry}>
      <Card>
        {rows.length ? (
          rows.map((r) => (
            <View key={r.id} style={styles.row}>
              <AppText style={{ flex: 1 }}>{r.name}</AppText>
              <Button small variant="outline" title={t('Unblock')} onPress={() => unblock(r.id)} />
            </View>
          ))
        ) : (
          <AppText variant="caption">{t('You have not blocked anyone. To block a member, press and hold one of their messages in your clan group.')}</AppText>
        )}
        {problem ? (
          <AppText variant="label" style={{ marginTop: SPACING.sm }}>
            {problem}
          </AppText>
        ) : null}
      </Card>
    </StateView>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 6 },
});
