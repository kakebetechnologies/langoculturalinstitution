// The controls of the shared player (lib/player.js): a bar to drag or tap to a place in the recording, the time, and previous /
// back 15 seconds / play-pause / forward 15 seconds / next. A live stream shows only play-pause. Used under the title of an episode
// and on the Radio screen.
import { useState } from 'react';
import { ActivityIndicator, Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { fmtClock, next, previous, seekBy, seekTo, toggle, usePlayer, usePlayerClock } from '../lib/player';
import AppText from './AppText';

function SeekBar() {
  const { t } = useT();
  const { position, duration } = usePlayerClock();
  const [width, setWidth] = useState(0);
  const pct = duration > 0 ? Math.min(1, position / duration) : 0;

  return (
    <View>
      <Pressable
        onLayout={(e) => setWidth(e.nativeEvent.layout.width)}
        onPress={(e) => width && duration > 0 && seekTo((e.nativeEvent.locationX / width) * duration)}
        accessibilityRole="adjustable"
        accessibilityLabel={t('Position in the recording')}
        style={styles.seek}
      >
        <View style={styles.bar} pointerEvents="none">
          <View style={[styles.barFill, { width: `${pct * 100}%` }]} />
        </View>
        <View style={[styles.knob, { left: `${pct * 100}%` }]} pointerEvents="none" />
      </Pressable>
      <View style={styles.times}>
        <AppText variant="caption">{fmtClock(position)}</AppText>
        <AppText variant="caption">{duration > 0 ? fmtClock(duration) : '--:--'}</AppText>
      </View>
    </View>
  );
}

function Skip({ label, onPress, hint }) {
  return (
    <Pressable onPress={onPress} hitSlop={6} accessibilityRole="button" accessibilityLabel={hint} style={styles.skip}>
      <AppText style={styles.skipText}>{label}</AppText>
    </Pressable>
  );
}

export default function PlayerControls({ style }) {
  const { t } = useT();
  const p = usePlayer();
  const busy = p.loading && !p.error;

  return (
    <View style={style}>
      {p.live ? null : <SeekBar />}
      {p.error ? (
        <AppText variant="caption" style={{ color: COLORS.danger, textAlign: 'center', marginTop: 6 }}>
          {t('This cannot be played right now.')}
        </AppText>
      ) : null}
      <View style={styles.row}>
        {p.live ? null : (
          <>
            <Pressable onPress={previous} hitSlop={8} accessibilityRole="button" accessibilityLabel={t('Previous')} style={styles.side}>
              <Ionicons name="play-skip-back" size={24} color={COLORS.black} />
            </Pressable>
            <Skip label="-15" hint={t('Back 15 seconds')} onPress={() => seekBy(-15)} />
          </>
        )}
        <Pressable onPress={toggle} accessibilityRole="button" accessibilityLabel={p.playing ? t('Pause') : t('Play')} style={styles.big}>
          {busy ? <ActivityIndicator color={COLORS.white} /> : <Ionicons name={p.playing ? 'pause' : 'play'} size={32} color={COLORS.white} style={p.playing ? null : { marginLeft: 3 }} />}
        </Pressable>
        {p.live ? null : (
          <>
            <Skip label="+15" hint={t('Forward 15 seconds')} onPress={() => seekBy(15)} />
            <Pressable onPress={next} hitSlop={8} accessibilityRole="button" accessibilityLabel={t('Next')} style={styles.side}>
              <Ionicons name="play-skip-forward" size={24} color={COLORS.black} />
            </Pressable>
          </>
        )}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  seek: { height: 28, justifyContent: 'center' },
  bar: { height: 6, borderRadius: 3, backgroundColor: COLORS.track, overflow: 'hidden' },
  barFill: { height: 6, backgroundColor: COLORS.primary },
  knob: { position: 'absolute', width: 16, height: 16, borderRadius: 8, marginLeft: -8, backgroundColor: COLORS.primary, borderWidth: 3, borderColor: COLORS.white },
  times: { flexDirection: 'row', justifyContent: 'space-between' },
  row: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: SPACING.md, marginTop: SPACING.sm },
  big: { width: 68, height: 68, borderRadius: 34, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  side: { padding: SPACING.sm },
  skip: { width: 44, height: 44, borderRadius: 22, borderWidth: 2, borderColor: COLORS.gray300, alignItems: 'center', justifyContent: 'center' },
  skipText: { fontFamily: FONTS.bold, fontSize: 13, color: COLORS.black },
});
