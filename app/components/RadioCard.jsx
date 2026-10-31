// Lango Radio on Home: a dark card with the channel on air (or the first one) and a play button that starts it from right here.
// The card itself opens the Radio screen. With no channel yet it says Lango Radio is getting ready, and nothing is invented.
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import AppText from './AppText';
import { COLORS, FONTS, RADIUS, SHADOW } from '../constants/theme';
import { errorMessage } from '../lib/api';
import { useT } from '../lib/i18n';
import { currentTrack, toggle, usePlayer } from '../lib/player';
import { tuneChannel } from '../lib/radio';
import { useFetch } from '../lib/useFetch';

export default function RadioCard({ style }) {
  const { t } = useT();
  const { data } = useFetch('/radio');
  const channels = data?.data ?? [];
  const p = usePlayer();
  const track = currentTrack(p);
  const onAir = p.key?.startsWith('radio:') ? channels.find((c) => p.key === `radio:${c.id}`) : null;
  const channel = onAir ?? channels[0];
  const [busy, setBusy] = useState(false);
  const [note, setNote] = useState('');

  async function play() {
    if (!channel) return;
    if (onAir) return toggle();
    setBusy(true);
    setNote('');
    try {
      if ((await tuneChannel(channel)) === 'empty') setNote(t('This channel has nothing to play yet.'));
    } catch (e) {
      setNote(errorMessage(e));
    } finally {
      setBusy(false);
    }
  }

  const playing = !!onAir && p.playing;

  return (
    <View style={[styles.card, style]}>
      <LinearGradient colors={['#14161B', '#4A0D14']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={StyleSheet.absoluteFill} />
      <Pressable onPress={() => router.push('/radio')} accessibilityRole="button" accessibilityLabel={t('Lango Radio')} style={styles.main}>
        <View style={styles.badge}>
          <Ionicons name="radio" size={22} color={COLORS.black} />
        </View>
        <View style={{ flex: 1 }}>
          <View style={styles.eyebrowRow}>
            {playing ? <View style={styles.dot} /> : null}
            <AppText style={styles.eyebrow}>{playing ? t('On air') : t('Lango Radio')}</AppText>
          </View>
          <AppText style={styles.title} numberOfLines={1}>
            {channel ? (onAir && track ? track.title : channel.name) : t('Lango Radio is getting ready')}
          </AppText>
          <AppText style={styles.sub} numberOfLines={1}>
            {note || (channel ? (onAir && track?.credit ? track.credit : channel.description || (channel.source === 'stream' ? t('Live radio') : channel.count === 1 ? t('1 recording') : t('{n} recordings', { n: channel.count }))) : t('Channels appear here as soon as they are set up.'))}
          </AppText>
        </View>
      </Pressable>
      {channel ? (
        <Pressable onPress={play} hitSlop={8} accessibilityRole="button" accessibilityLabel={playing ? t('Pause') : t('Play')} style={styles.play}>
          <Ionicons name={busy ? 'hourglass-outline' : playing ? 'pause' : 'play'} size={22} color={COLORS.black} style={!playing && !busy ? { marginLeft: 2 } : undefined} />
        </Pressable>
      ) : (
        <Ionicons name="chevron-forward" size={20} color="rgba(255,255,255,0.6)" />
      )}
      <View style={styles.flag} pointerEvents="none">
        <View style={{ flex: 1, backgroundColor: COLORS.primary }} />
        <View style={{ flex: 1, backgroundColor: COLORS.yellow }} />
        <View style={{ flex: 1, backgroundColor: COLORS.sky }} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  card: { flexDirection: 'row', alignItems: 'center', gap: 12, overflow: 'hidden', borderRadius: RADIUS.lg + 2, padding: 14, paddingBottom: 18, backgroundColor: COLORS.navy, ...SHADOW },
  main: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 12 },
  badge: { width: 48, height: 48, borderRadius: 16, backgroundColor: COLORS.yellow, alignItems: 'center', justifyContent: 'center' },
  eyebrowRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
  dot: { width: 8, height: 8, borderRadius: 4, backgroundColor: COLORS.primary },
  eyebrow: { fontFamily: FONTS.bold, fontSize: 10.5, letterSpacing: 1.2, textTransform: 'uppercase', color: COLORS.yellow },
  title: { fontFamily: FONTS.bold, fontSize: 18, lineHeight: 24, color: COLORS.white, marginTop: 1 },
  sub: { fontFamily: 'Inter_400Regular', fontSize: 13, lineHeight: 18, color: 'rgba(255,255,255,0.72)' },
  play: { width: 48, height: 48, borderRadius: 24, backgroundColor: COLORS.yellow, alignItems: 'center', justifyContent: 'center' },
  flag: { position: 'absolute', left: 0, right: 0, bottom: 0, height: 4, flexDirection: 'row' },
});
