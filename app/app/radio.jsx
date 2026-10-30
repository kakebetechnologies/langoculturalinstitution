// Lango Radio (GET /api/v1/radio, /radio/{id}): the channels LCI has set up. A channel is a live stream from a station, a playlist of
// recordings, or every podcast episode; the ones with something to play appear here. Pick one and it plays and keeps going, also
// when the app is in the background; the bar above the tabs shows what is on. Until LCI adds a channel the screen says so.
import { useState } from 'react';
import { Pressable, StyleSheet, Switch, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import AppText from '../components/AppText';
import Card from '../components/Card';
import PlayerControls from '../components/PlayerControls';
import Row from '../components/Row';
import Screen from '../components/Screen';
import SectionHeader from '../components/SectionHeader';
import StateView from '../components/StateView';
import { COLORS, SPACING } from '../constants/theme';
import api, { errorMessage } from '../lib/api';
import { useT } from '../lib/i18n';
import { currentTrack, playLive, playQueue, toggle, usePlayer } from '../lib/player';
import { useFetch } from '../lib/useFetch';

const shuffled = (list) => {
  const a = [...list];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
};

export default function Radio() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/radio');
  const channels = data?.data ?? [];
  const p = usePlayer();
  const track = currentTrack(p);
  const onAir = p.key?.startsWith('radio:') ? channels.find((c) => p.key === `radio:${c.id}`) : null;
  const [busy, setBusy] = useState(null);
  const [note, setNote] = useState('');
  const [shuffle, setShuffle] = useState(true);

  async function tune(c) {
    setBusy(c.id);
    setNote('');
    try {
      if (c.source === 'stream') await playLive(c);
      else {
        const { data: r } = await api.get(`/radio/${c.id}`);
        let tracks = r.data.tracks ?? [];
        if (!tracks.length) return setNote(t('This channel has nothing to play yet.'));
        if (shuffle && tracks.length > 1) tracks = shuffled(tracks);
        await playQueue(tracks, 0, { loop: true, label: c.name, route: '/radio', key: `radio:${c.id}` });
      }
    } catch (e) {
      setNote(errorMessage(e));
    } finally {
      setBusy(null);
    }
  }

  return (
    <Screen title={t('Lango Radio')} subtitle={t('Listen to Lango, wherever you are')} onRefresh={reload} refreshing={refreshing}>
      <StateView loading={loading} error={error} onRetry={retry} empty={!channels.length} emptyIcon="radio-outline" emptyTitle="Lango Radio is getting ready" emptyMessage="Channels appear here as soon as LCI sets them up.">
        {onAir && track ? (
          <Card style={{ marginBottom: SPACING.md }}>
            <View style={styles.live}>
              <View style={styles.dot} />
              <AppText variant="eyebrow" style={{ color: COLORS.primaryDark }}>
                {p.live ? t('Live now') : t('On air')} · {onAir.name}
              </AppText>
            </View>
            <AppText variant="heading" style={{ marginTop: 6 }}>
              {track.title}
            </AppText>
            {track.credit ? <AppText variant="caption">{track.credit}</AppText> : null}
            <PlayerControls style={{ marginTop: SPACING.sm }} />
          </Card>
        ) : null}

        {channels.some((c) => c.source !== 'stream') ? (
          <View style={styles.shuffle}>
            <View style={{ flex: 1 }}>
              <AppText variant="subheading">{t('Shuffle')}</AppText>
              <AppText variant="caption">{t('Play the recordings of a channel in a mixed order')}</AppText>
            </View>
            <Switch value={shuffle} onValueChange={setShuffle} trackColor={{ true: COLORS.primary, false: COLORS.gray300 }} />
          </View>
        ) : null}

        <SectionHeader title={t('Channels')} />
        {channels.map((c) => {
          const active = onAir?.id === c.id;

          return (
            <Row
              key={c.id}
              image={c.cover}
              icon={c.cover ? undefined : 'radio-outline'}
              iconTone={active ? 'red' : 'sky'}
              title={c.name}
              subtitle={c.description || (c.source === 'stream' ? t('Live radio') : t('{n} recordings', { n: c.count }))}
              onPress={() => (active ? toggle() : tune(c))}
              right={
                <Pressable onPress={() => (active ? toggle() : tune(c))} hitSlop={8} accessibilityLabel={active && p.playing ? t('Pause') : t('Play')} style={styles.play}>
                  <Ionicons name={busy === c.id ? 'hourglass-outline' : active && p.playing ? 'pause' : 'play'} size={18} color={COLORS.white} />
                </Pressable>
              }
            />
          );
        })}
        {note ? (
          <AppText variant="label" style={{ color: COLORS.danger, marginTop: SPACING.sm }}>
            {note}
          </AppText>
        ) : null}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  live: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  dot: { width: 9, height: 9, borderRadius: 5, backgroundColor: COLORS.primary },
  shuffle: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: SPACING.sm },
  play: { width: 36, height: 36, borderRadius: 18, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
});
