// Getting a photo, video, recording or document into a contribution. Each function resolves a media object
// { kind: 'image' | 'video' | 'audio' | 'document', uri, name, type } (or null when the person cancels), which the form shows
// and lib/outbox.js uploads. Photos are compressed (quality 0.7) so they upload quickly on a weak connection.
import { Platform } from 'react-native';
import * as ImagePicker from 'expo-image-picker';

const extOf = (uri, fallback) => {
  const m = String(uri).split('?')[0].match(/\.([a-z0-9]{2,4})$/i);
  return (m ? m[1] : fallback).toLowerCase();
};

const MIME = { jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png', webp: 'image/webp', mp4: 'video/mp4', mov: 'video/quicktime', webm: 'video/webm', m4a: 'audio/mp4', mp3: 'audio/mpeg', wav: 'audio/wav', pdf: 'application/pdf', txt: 'text/plain', doc: 'application/msword', docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' };

export function mediaFrom(kind, asset) {
  const ext = extOf(asset.uri, kind === 'image' ? 'jpg' : kind === 'video' ? 'mp4' : kind === 'audio' ? 'm4a' : 'pdf');
  const name = asset.fileName || asset.name || `${kind}-${Date.now()}.${ext}`;
  return { kind, uri: asset.uri, name: /\.[a-z0-9]{2,4}$/i.test(name) ? name : `${name}.${ext}`, type: asset.mimeType || MIME[ext] || 'application/octet-stream', size: asset.fileSize ?? asset.size ?? null };
}

/** Takes a photo with the camera. Resolves { media } or { error } (permission refused) or null (cancelled). */
export async function takePhoto() {
  if (Platform.OS !== 'web') {
    const perm = await ImagePicker.requestCameraPermissionsAsync();
    if (!perm.granted) return { error: 'Allow the camera for Lango App in your phone settings to take a photo.' };
  }
  const res = await ImagePicker.launchCameraAsync({ mediaTypes: ['images'], quality: 0.7, exif: false });
  return res.canceled ? null : { media: mediaFrom('image', res.assets[0]) };
}

/** Chooses a photo or a video from the library. */
export async function pickFromLibrary({ video = false } = {}) {
  const res = await ImagePicker.launchImageLibraryAsync({ mediaTypes: video ? ['images', 'videos'] : ['images'], quality: 0.7, exif: false });
  if (res.canceled) return null;
  const a = res.assets[0];
  return { media: mediaFrom(a.type === 'video' ? 'video' : 'image', a) };
}

/** Chooses a PDF / Word / text file. */
export async function pickDocument() {
  try {
    const DocumentPicker = require('expo-document-picker');
    const res = await DocumentPicker.getDocumentAsync({ type: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'], copyToCacheDirectory: true });
    if (res.canceled) return null;
    return { media: mediaFrom('document', res.assets[0]) };
  } catch {
    return { error: 'Choosing documents is not available on this device.' };
  }
}
