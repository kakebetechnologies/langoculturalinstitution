// Top contributors (GET /api/v1/leaderboard): first name and initial, points and level — the people adding most to the archive.
import { StyleSheet, View } from 'react-native';
import AppText from '../components/AppText';
import Row from '../components/Row';
import Screen from '../components/Screen';
import StateView from '../components/StateView';
import Pill from '../components/Pill';
import { COLORS, FONTS } from '../constants/theme';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function Leaderboard() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/leaderboard');
  const rows = data?.data ?? [];

  return (
    <Screen title={t('Top contributors')} onRefresh={reload} refreshing={refreshing}>
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!rows.length} emptyIcon="trophy-outline" emptyTitle="The leaderboard starts with the first approved contribution" emptyMessage="Contribute something and be the first.">
        {rows.map((r) => (
          <Row
            key={r.rank}
            title={r.name}
            subtitle={`${r.points} ${t('points')}`}
            right={<Pill label={t(r.level)} tone={r.rank === 1 ? 'gold' : 'grey'} />}
            icon={r.rank <= 3 ? 'trophy' : 'person-outline'}
            iconTone={r.rank === 1 ? 'gold' : r.rank <= 3 ? 'sky' : 'dark'}
          />
        ))}
        <View style={styles.note}>
          <AppText variant="caption">{t('Points: a photo 10 · a word 5 · an oral history 20 · a nature record 15, when approved.')}</AppText>
        </View>
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  note: { marginTop: 8 },
});
