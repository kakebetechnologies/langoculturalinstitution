// Records a voice note (an elder's story, how a word is pronounced) with the microphone and hands back the file.
// Tap to start, tap to stop; the time counts up while recording. expo-audio does the recording (permission asked on first use).
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { AudioModule, RecordingPresets, setAudioModeAsync, useAudioRecorder, useAudioRecorderState } from 'expo-audio';
import { COLORS, RADIUS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { mediaFrom } from '../lib/media';
import AppText from './AppText';

const clock = (ms) => {
  const s = Math.floor((ms || 0) / 1000);
  return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
};

export default function AudioRecorder({ onDone, onError }) {
  const { t } = useT();
  const recorder = useAudioRecorder(RecordingPresets.HIGH_QUALITY);
  const state = useAudioRecorderState(recorder);
  const [busy, setBusy] = useState(false);

  async function toggle() {
    if (busy) return;
    setBusy(true);
    try {
      if (state.isRecording) {
        await recorder.stop();
        await setAudioModeAsync({ allowsRecording: false });
        if (recorder.uri) onDone(mediaFrom('audio', { uri: recorder.uri, name: `recording-${Date.now()}.m4a`, mimeType: 'audio/mp4' }));
      } else {
        const perm = await AudioModule.requestRecordingPermissionsAsync();
        if (!perm.granted) {
          onError?.(t('Allow the microphone for Lango App in your phone settings to record.'));
        } else {
          await setAudioModeAsync({ allowsRecording: true, playsInSilentMode: true });
          await recorder.prepareToRecordAsync();
          recorder.record();
        }
      }
    } catch {
      onError?.(t('We could not use the microphone. Please try again.'));
    } finally {
      setBusy(false);
    }
  }

  return (
    <Pressable onPress={toggle} accessibilityRole="button" style={[styles.box, state.isRecording && styles.live]}>
      <View style={[styles.dot, state.isRecording && { backgroundColor: COLORS.primary }]}>
        <Ionicons name={state.isRecording ? 'stop' : 'mic'} size={20} color={state.isRecording ? COLORS.white : COLORS.primaryDark} />
      </View>
      <View style={{ flex: 1 }}>
        <AppText variant="subheading">{state.isRecording ? t('Recording… tap to stop') : t('Record with the microphone')}</AppText>
        <AppText variant="caption">{state.isRecording ? clock(state.durationMillis) : t('Speak clearly and close to the phone.')}</AppText>
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  box: { flexDirection: 'row', alignItems: 'center', gap: 12, padding: SPACING.md, borderRadius: RADIUS.lg, borderWidth: 1.5, borderColor: COLORS.gray300, backgroundColor: COLORS.white },
  live: { borderColor: COLORS.primary, backgroundColor: COLORS.primaryMist },
  dot: { width: 44, height: 44, borderRadius: 22, backgroundColor: COLORS.primaryMist, alignItems: 'center', justifyContent: 'center' },
});
