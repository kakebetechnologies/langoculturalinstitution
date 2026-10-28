// Leaderboards, three in one screen: this week's Language Challenge, the month's learners (days on which a word of the day was learned),
// and the clans (by registered members, approved contributions or learners). Names are shown as first name and initial.
// Top contributors, by points, has its own screen.
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../components/AppText';
import Card from '../components/Card';
import Chip from '../components/Chip';
import Pill from '../components/Pill';
import Row from '../components/Row';
import Screen from '../components/Screen';
import Segmented from '../components/Segmented';
import StateView from '../components/StateView';
import { COLORS, FONTS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

const SORTS = [['members', 'Members'], ['contributions', 'Contributions'], ['learners', 'Learners']];

export default function Leaders() {
  const { t } = useT();
  const { tab: start } = useLocalSearchParams();
  const [tab, setTab] = useState(['challenge', 'learners', 'clans'].includes(String(start)) ? String(start) : 'challenge');
  const [sort, setSort] = useState('members');
  const people = useFetch(`/leaderboards/${tab === 'learners' ? 'learners' : 'challenge'}`, { enabled: tab !== 'clans', cache: false });
  const clans = useFetch('/clans/leaderboard', { params: { sort }, enabled: tab === 'clans', cache: false });
  const active = tab === 'clans' ? clans : people;
  const rows = active.data?.data ?? [];
  const pl = (n, one, many) => (n === 1 ? t(one) : t(many, { n }));
  const metric = (r) => ({ members: r.members, contributions: r.contributions, learners: r.learners }[sort]);

  return (
    <Screen title={t('Leaderboards')} onRefresh={active.reload} refreshing={active.refreshing}>
      <Segmented value={tab} onChange={setTab} options={[{ value: 'challenge', label: t('Challenge') }, { value: 'learners', label: t('Learners') }, { value: 'clans', label: t('Clans') }]} />

      {tab === 'clans' ? (
        <View style={styles.chips}>
          {SORTS.map(([k, label]) => (
            <Chip key={k} label={t(label)} on={sort === k} onPress={() => setSort(k)} />
          ))}
        </View>
      ) : (
        <AppText variant="caption" style={{ marginTop: SPACING.sm }}>
          {tab === 'challenge' ? t('Points from the weekly Language Challenge, {week}.', { week: active.data?.period ?? '' }) : t('Days this month on which a member learned the word of the day, {month}.', { month: active.data?.period ?? '' })}
        </AppText>
      )}

      {tab === 'clans' && clans.data?.mine ? (
        <Card style={styles.mine}>
          <Ionicons name="people" size={22} color={COLORS.primaryDark} />
          <AppText style={{ flex: 1, fontFamily: FONTS.semibold }}>{t('Your clan, {clan}, is number {n} of {total}', { clan: clans.data.mine.name, n: clans.data.data.findIndex((r) => r.id === clans.data.mine.id) + 1, total: rows.length })}</AppText>
        </Card>
      ) : null}

      <View style={{ marginTop: SPACING.md }}>
        <StateView loading={active.loading && !active.data} error={active.error} onRetry={active.retry} empty={!rows.length} emptyIcon="trophy-outline" emptyTitle={tab === 'clans' ? 'No clans yet' : 'Nobody on the board yet'} emptyMessage={tab === 'challenge' ? 'Take this week’s challenge to be the first.' : tab === 'learners' ? 'Learn today’s word to be the first.' : ''}>
          {tab === 'clans'
            ? rows.map((r) => (
                <Row
                  key={r.id}
                  icon={r.rank <= 3 ? 'trophy' : 'people-outline'}
                  iconTone={r.rank === 1 ? 'gold' : r.rank <= 3 ? 'sky' : 'dark'}
                  title={`${r.rank}. ${r.name}`}
                  subtitle={`${pl(r.members, '1 registered', '{n} registered')} · ${pl(r.contributions, '1 contribution', '{n} contributions')} · ${pl(r.learners, '1 learner', '{n} learners')}`}
                  onPress={() => router.push(`/clans/${r.slug}`)}
                  right={<Pill label={String(metric(r))} tone={clans.data?.mine?.id === r.id ? 'red' : 'grey'} />}
                />
              ))
            : rows.map((r) => (
                <Row
                  key={r.rank}
                  icon={r.rank <= 3 ? 'trophy' : 'person-outline'}
                  iconTone={r.rank === 1 ? 'gold' : r.rank <= 3 ? 'sky' : 'dark'}
                  title={`${r.rank}. ${r.name}${r.you ? ` (${t('you')})` : ''}`}
                  subtitle={tab === 'challenge' ? pl(r.score, '1 point', '{n} points') : pl(r.score, '1 day', '{n} days')}
                  style={r.you ? styles.you : undefined}
                />
              ))}
        </StateView>
      </View>

      <Pressable onPress={() => router.push('/leaderboard')} accessibilityRole="button" style={styles.link}>
        <Ionicons name="ribbon-outline" size={20} color={COLORS.yellowInk} />
        <AppText style={{ flex: 1, fontFamily: FONTS.semibold }}>{t('Top contributors')}</AppText>
        <Ionicons name="chevron-forward" size={18} color={COLORS.gray500} />
      </Pressable>
    </Screen>
  );
}

const styles = StyleSheet.create({
  chips: { flexDirection: 'row', gap: 8, marginTop: SPACING.md, flexWrap: 'wrap' },
  mine: { flexDirection: 'row', alignItems: 'center', gap: 10, marginTop: SPACING.md },
  you: { borderWidth: 2, borderColor: COLORS.primary },
  link: { flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: COLORS.yellowMist, borderRadius: 20, padding: SPACING.md, marginTop: SPACING.lg },
});
