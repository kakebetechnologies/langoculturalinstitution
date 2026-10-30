// One podcast episode (GET /api/v1/podcasts/{id}): who tells the story, where and when it was recorded, the player, the written
// version when LCI has added one, and more stories from the same storyteller or series.
import { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import Card from '../../components/Card';
import Pill from '../../components/Pill';
import PlayerControls from '../../components/PlayerControls';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import SectionHeader from '../../components/SectionHeader';
import StateView from '../../components/StateView';
import { COLORS, RADIUS, SPACING } from '../../constants/theme';
import { mediaUrl } from '../../lib/api';
import { useT } from '../../lib/i18n';
import { currentTrack, usePlayer } from '../../lib/player';
import { playEpisodes } from '../../lib/podcast';
import { useFetch } from '../../lib/useFetch';

export default function Episode() {
  const { t } = useT();
  const { id } = useLocalSearchParams();
  const { data, loading, error, retry, reload, refreshing } = useFetch(`/podcasts/${id}`);
  const [read, setRead] = useState(false);
  const p = usePlayer();
  const e = data?.data;
  const more = data?.more ?? [];
  const mine = !!e && currentTrack(p)?.id === `e${e.id}`;

  return (
    <Screen title={t('Lango Podcast')} onRefresh={reload} refreshing={refreshing}>
      <StateView loading={loading} error={error} onRetry={retry}>
        {e ? (
          <>
            {e.cover ? <Image source={{ uri: mediaUrl(e.cover) }} style={styles.cover} contentFit="cover" /> : null}
            <AppText variant="title">{e.title}</AppText>
            {e.storyteller ? (
              <AppText variant="subheading" style={{ marginTop: 4 }}>
                {t('Told by {name}', { name: e.storyteller })}
              </AppText>
            ) : null}
            {e.storyteller_note ? <AppText variant="caption">{e.storyteller_note}</AppText> : null}
            <View style={styles.tags}>
              {e.series ? <Pill label={e.series} tone="red" /> : null}
              {e.clan ? <Pill label={e.clan} tone="gold" /> : null}
              {e.district ? <Pill label={e.district} tone="sky" /> : null}
              {e.recorded_year ? <Pill label={t('Recorded {y}', { y: e.recorded_year })} tone="grey" /> : null}
              {e.minutes ? <Pill label={t('{n} min', { n: e.minutes })} tone="grey" /> : null}
            </View>

            <Card style={{ marginTop: SPACING.md }}>
              {mine ? <PlayerControls /> : <Button title={t('Play this story')} icon="play" onPress={() => playEpisodes([e, ...more], 0, t('Lango Podcast'))} />}
            </Card>

            {e.summary ? <AppText style={{ marginTop: SPACING.md }}>{e.summary}</AppText> : null}

            {e.transcript ? (
              <View style={{ marginTop: SPACING.md }}>
                <Button title={read ? t('Hide the written story') : t('Read the story')} icon={read ? 'chevron-up' : 'document-text-outline'} variant="outline" small onPress={() => setRead((v) => !v)} style={{ alignSelf: 'flex-start' }} />
                {read ? (
                  <Card style={{ marginTop: SPACING.sm }}>
                    <AppText selectable style={styles.transcript}>
                      {e.transcript}
                    </AppText>
                  </Card>
                ) : null}
              </View>
            ) : null}

            {more.length ? (
              <View style={{ marginTop: SPACING.lg }}>
                <SectionHeader title={t('More stories')} actionLabel={t('All stories')} onAction={() => router.push('/podcast')} />
                {more.map((m) => (
                  <Row key={m.id} image={m.cover} icon={m.cover ? undefined : 'mic-outline'} title={m.title} subtitle={m.storyteller} onPress={() => router.replace(`/podcast/${m.id}`)} />
                ))}
              </View>
            ) : null}
          </>
        ) : null}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  cover: { width: '100%', aspectRatio: 16 / 9, borderRadius: RADIUS.lg, marginBottom: SPACING.md, backgroundColor: COLORS.gray100 },
  tags: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginTop: SPACING.sm },
  transcript: { lineHeight: 26 },
});
