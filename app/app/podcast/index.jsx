// Lango Podcast (GET /api/v1/podcasts): the stories of the old days told by the elders, newest first. Search by title or storyteller,
// narrow to one series, tap an episode to open it or tap its play button to listen straight away (the next episode follows).
// Only what LCI has published appears; until then the screen says the first stories are on their way.
import { useEffect, useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Pill from '../../components/Pill';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import Select from '../../components/Select';
import StateView from '../../components/StateView';
import TextField from '../../components/TextField';
import { COLORS, SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { currentTrack, toggle, usePlayer } from '../../lib/player';
import { playEpisodes } from '../../lib/podcast';
import { useFetch } from '../../lib/useFetch';

export default function Podcast() {
  const { t } = useT();
  const [q, setQ] = useState('');
  const [term, setTerm] = useState(''); // what was typed, once the person pauses
  const [series, setSeries] = useState('');
  useEffect(() => {
    const id = setTimeout(() => setTerm(q.trim()), 350);
    return () => clearTimeout(id);
  }, [q]);
  const { data, loading, error, retry, reload, refreshing } = useFetch('/podcasts', { params: { q: term || undefined, series: series || undefined } });
  const p = usePlayer();
  const now = currentTrack(p);
  const rows = data?.data ?? [];
  const seriesOptions = [{ value: '', label: t('All series') }, ...(data?.series ?? []).map((s) => ({ value: s, label: s }))];
  const filtered = !!term || !!series;

  return (
    <Screen title={t('Lango Podcast')} subtitle={t('Stories from our elders')} onRefresh={reload} refreshing={refreshing}>
      <AppText style={{ marginBottom: SPACING.md }}>{t('Listen to the elders of Lango tell the stories of the old days, in their own voices.')}</AppText>
      {rows.length || filtered ? (
        <>
          <TextField value={q} onChangeText={setQ} placeholder={t('Search stories or storytellers')} autoCapitalize="none" returnKeyType="search" />
          {seriesOptions.length > 2 ? <Select value={series} options={seriesOptions} onChange={setSeries} /> : null}
        </>
      ) : null}
      <StateView
        loading={loading}
        error={error}
        onRetry={retry}
        empty={!rows.length}
        emptyIcon={filtered ? 'search-outline' : 'mic-outline'}
        emptyTitle={filtered ? 'No story matches that' : 'The first stories are on their way'}
        emptyMessage={filtered ? 'Try another word, or choose all series.' : 'They will appear here as soon as LCI publishes them.'}
      >
        {rows.map((e, i) => {
          const playing = now?.id === `e${e.id}`;

          return (
            <Row
              key={e.id}
              image={e.cover}
              icon={e.cover ? undefined : 'mic-outline'}
              iconTone={playing ? 'red' : 'sky'}
              title={e.title}
              subtitle={[e.storyteller, e.minutes ? t('{n} min', { n: e.minutes }) : null].filter(Boolean).join(' · ')}
              onPress={() => router.push(`/podcast/${e.id}`)}
              right={
                <Pressable onPress={() => (playing ? toggle() : playEpisodes(rows, i, t('Lango Podcast')))} hitSlop={8} accessibilityLabel={playing && p.playing ? t('Pause') : t('Play')} style={styles.play}>
                  <Ionicons name={playing && p.playing ? 'pause' : 'play'} size={18} color={COLORS.white} />
                </Pressable>
              }
            >
              {e.series ? <Pill label={e.series} tone="grey" style={{ marginTop: 6 }} /> : null}
            </Row>
          );
        })}
      </StateView>
      <View style={{ height: SPACING.md }} />
    </Screen>
  );
}

const styles = StyleSheet.create({
  play: { width: 36, height: 36, borderRadius: 18, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
});
