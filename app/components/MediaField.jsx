// The "add a photo, recording, video or document" part of a contribution. One file per contribution (the website keeps one):
// choosing another replaces it. `kinds` says which choices to offer.
import { useState } from 'react';
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Image } from 'expo-image';
import { COLORS, RADIUS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { pickDocument, pickFromLibrary, takePhoto } from '../lib/media';
import AppText from './AppText';
import AudioRecorder from './AudioRecorder';
import Button from './Button';

const ICON = { image: 'image-outline', video: 'videocam-outline', audio: 'mic-outline', document: 'document-text-outline' };

export default function MediaField({ value, onChange, kinds = ['photo', 'library', 'audio', 'video', 'document'] }) {
  const { t } = useT();
  const [note, setNote] = useState('');
  const [recording, setRecording] = useState(false);

  const take = async (fn) => {
    setNote('');
    const res = await fn();
    if (res?.error) setNote(res.error);
    else if (res?.media) onChange(res.media);
  };

  if (value) {
    const mb = value.size ? `${(value.size / 1048576).toFixed(1)} MB` : '';
    return (
      <View style={styles.chosen}>
        {value.kind === 'image' ? (
          <Image source={{ uri: value.uri }} style={styles.thumb} contentFit="cover" />
        ) : (
          <View style={[styles.thumb, styles.fileIcon]}>
            <Ionicons name={ICON[value.kind]} size={26} color={COLORS.primaryDark} />
          </View>
        )}
        <View style={{ flex: 1 }}>
          <AppText variant="subheading" numberOfLines={1}>
            {value.name}
          </AppText>
          <AppText variant="caption">{[t(value.kind === 'image' ? 'Photo' : value.kind === 'video' ? 'Video' : value.kind === 'audio' ? 'Recording' : 'Document'), mb].filter(Boolean).join(' · ')}</AppText>
        </View>
        <Pressable onPress={() => onChange(null)} hitSlop={10} accessibilityRole="button" accessibilityLabel={t('Remove file')}>
          <Ionicons name="close-circle" size={26} color={COLORS.gray500} />
        </Pressable>
      </View>
    );
  }

  return (
    <View>
      <View style={styles.grid}>
        {kinds.includes('photo') ? <Button small variant="outline" icon="camera-outline" title={t('Take a photo')} onPress={() => take(takePhoto)} style={styles.btn} /> : null}
        {kinds.includes('library') ? <Button small variant="outline" icon="image-outline" title={t('Choose a photo')} onPress={() => take(() => pickFromLibrary())} style={styles.btn} /> : null}
        {kinds.includes('video') ? <Button small variant="outline" icon="videocam-outline" title={t('Choose a video')} onPress={() => take(() => pickFromLibrary({ video: true }))} style={styles.btn} /> : null}
        {kinds.includes('audio') ? <Button small variant="outline" icon="mic-outline" title={t('Record audio')} onPress={() => setRecording((r) => !r)} style={styles.btn} /> : null}
        {kinds.includes('document') ? <Button small variant="outline" icon="document-text-outline" title={t('Add a document')} onPress={() => take(pickDocument)} style={styles.btn} /> : null}
      </View>
      {recording ? (
        <View style={{ marginTop: SPACING.sm }}>
          <AudioRecorder
            onDone={(m) => {
              setRecording(false);
              onChange(m);
            }}
            onError={setNote}
          />
        </View>
      ) : null}
      {note ? (
        <AppText variant="caption" style={{ color: COLORS.danger, marginTop: 8 }}>
          {note}
        </AppText>
      ) : null}
      <AppText variant="caption" style={{ marginTop: 8 }}>
        {t('One file per contribution. Photos up to 12 MB, audio 40 MB, video 120 MB, documents 25 MB.')}
      </AppText>
    </View>
  );
}

const styles = StyleSheet.create({
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  btn: { flexGrow: 1 },
  chosen: { flexDirection: 'row', alignItems: 'center', gap: 12, padding: SPACING.sm + 2, borderRadius: RADIUS.lg, borderWidth: 1.5, borderColor: COLORS.sky, backgroundColor: COLORS.skyMist },
  thumb: { width: 56, height: 56, borderRadius: RADIUS.md, backgroundColor: COLORS.white },
  fileIcon: { alignItems: 'center', justifyContent: 'center' },
});
