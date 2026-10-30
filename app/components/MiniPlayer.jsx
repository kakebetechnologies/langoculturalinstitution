// The "now playing" bar that sits above the tab bar while something plays (Lango Radio or a podcast episode): picture or icon,
// title, play / pause and close. Tapping it opens the screen the sound came from.
import { ActivityIndicator, Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Image } from 'expo-image';
import { router } from 'expo-router';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../constants/theme';
import { mediaUrl } from '../lib/api';
import { useT } from '../lib/i18n';
import { currentTrack, stop, toggle, usePlayer, usePlayerClock } from '../lib/player';
import AppText from './AppText';

function Progress() {
  const { position, duration } = usePlayerClock();
  const pct = duration > 0 ? Math.min(1, position / duration) : 0;

  return (
    <View style={styles.track}>
      <View style={[styles.fill, { width: `${pct * 100}%` }]} />
    </View>
  );
}

export default function MiniPlayer() {
  const { t } = useT();
  const p = usePlayer();
  const track = currentTrack(p);
  if (!track) return null;

  return (
    <View style={styles.wrap}>
      <View style={styles.card}>
        <Pressable onPress={() => (track.route || p.route) && router.push(track.route || p.route)} accessibilityRole="button" accessibilityLabel={t('Open the player')} style={styles.info}>
          {track.cover ? (
            <Image source={{ uri: mediaUrl(track.cover) }} style={styles.cover} contentFit="cover" />
          ) : (
            <View style={[styles.cover, styles.coverIcon]}>
              <Ionicons name={p.live ? 'radio-outline' : 'musical-notes'} size={22} color={COLORS.white} />
            </View>
          )}
          <View style={{ flex: 1 }}>
            <AppText numberOfLines={1} style={styles.title}>
              {track.title}
            </AppText>
            <AppText variant="caption" numberOfLines={1}>
              {p.error ? t('This cannot be played right now.') : p.live ? t('Live') : track.credit || p.label}
            </AppText>
          </View>
        </Pressable>
        <Pressable onPress={toggle} hitSlop={8} accessibilityRole="button" accessibilityLabel={p.playing ? t('Pause') : t('Play')} style={styles.play}>
          {p.loading && !p.error ? <ActivityIndicator size="small" color={COLORS.white} /> : <Ionicons name={p.playing ? 'pause' : 'play'} size={20} color={COLORS.white} />}
        </Pressable>
        <Pressable onPress={stop} hitSlop={10} accessibilityRole="button" accessibilityLabel={t('Stop and close')} style={styles.close}>
          <Ionicons name="close" size={20} color={COLORS.gray700} />
        </Pressable>
        {p.live ? null : <Progress />}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { backgroundColor: COLORS.canvas, paddingHorizontal: 14, paddingTop: 6 },
  card: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, padding: 8, paddingRight: 10, overflow: 'hidden', ...SHADOW },
  info: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 10 },
  cover: { width: 44, height: 44, borderRadius: 12 },
  coverIcon: { backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  title: { fontFamily: FONTS.semibold, fontSize: 14.5, color: COLORS.black },
  play: { width: 38, height: 38, borderRadius: 19, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  close: { padding: SPACING.xs },
  track: { position: 'absolute', left: 0, right: 0, bottom: 0, height: 3, backgroundColor: COLORS.track },
  fill: { height: 3, backgroundColor: COLORS.primary },
});
