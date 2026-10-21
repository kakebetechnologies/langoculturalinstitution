// Events (GET /api/v1/events): upcoming festivals, ceremonies and meetings with a date tile; switch to past events.
import { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Pill from '../../components/Pill';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import Segmented from '../../components/Segmented';
import StateView from '../../components/StateView';
import { COLORS, FONTS } from '../../constants/theme';
import { dayParts, fmtTime } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Events() {
  const { t } = useT();
  const [past, setPast] = useState('0');
  const { data, loading, error, retry, reload, refreshing } = useFetch('/events', { params: { past: past === '1' ? 1 : undefined } });
  const rows = data?.data ?? [];

  return (
    <Screen title={t('Events')} onRefresh={reload} refreshing={refreshing}>
      <Segmented value={past} onChange={setPast} options={[{ value: '0', label: t('Upcoming') }, { value: '1', label: t('Past events') }]} style={{ marginBottom: 14 }} />
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!rows.length} emptyIcon="calendar-outline" emptyTitle={past === '1' ? 'No past events' : 'No upcoming events'} emptyMessage="Check back soon.">
        {rows.map((e) => {
          const d = dayParts(e.starts_at);
          return (
            <Row
              key={e.id}
              title={e.title}
              subtitle={[fmtTime(e.starts_at), e.location].filter(Boolean).join(' · ')}
              onPress={() => router.push(`/events/${e.slug}`)}
              right={
                <View style={styles.date}>
                  <AppText style={styles.day}>{d.day}</AppText>
                  <AppText style={styles.month}>{d.month}</AppText>
                </View>
              }
            >
              {e.registration_open ? <Pill label={t('Registration open')} tone="green" style={{ marginTop: 6 }} /> : null}
            </Row>
          );
        })}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  date: { alignItems: 'center', minWidth: 46 },
  day: { fontFamily: FONTS.serif, fontSize: 24, lineHeight: 28, color: COLORS.primaryDark },
  month: { fontFamily: FONTS.bold, fontSize: 10.5, letterSpacing: 1, color: COLORS.gray700 },
});
