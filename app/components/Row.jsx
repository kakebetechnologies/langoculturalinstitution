// A list row: optional picture or icon on the left, a title with a line or two under it, optional tags and a chevron.
// Used by the clan list, districts, people, heritage, events, programs and settings.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Image } from 'expo-image';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../constants/theme';
import { mediaUrl } from '../lib/api';
import AppText from './AppText';
import Avatar from './Avatar';

export default function Row({ title, subtitle, meta, image, avatar, icon, iconTone = 'sky', onPress, right, children, style, numberOfLines = 2 }) {
  const tone = { sky: [COLORS.skyMist, COLORS.skyDark], red: [COLORS.primaryMist, COLORS.primaryDark], gold: [COLORS.yellowMist, COLORS.yellowInk], dark: [COLORS.gray100, COLORS.navy] }[iconTone] ?? [COLORS.skyMist, COLORS.skyDark];
  const Wrap = onPress ? Pressable : View;

  return (
    <Wrap onPress={onPress} accessibilityRole={onPress ? 'button' : undefined} style={({ pressed }) => [styles.row, style, pressed && onPress && { opacity: 0.9 }]}>
      {image ? <Image source={{ uri: mediaUrl(image) }} style={styles.img} contentFit="cover" /> : null}
      {avatar ? <Avatar name={avatar.name} photo={avatar.photo} size={48} /> : null}
      {icon ? (
        <View style={[styles.icon, { backgroundColor: tone[0] }]}>
          <Ionicons name={icon} size={21} color={tone[1]} />
        </View>
      ) : null}
      <View style={{ flex: 1 }}>
        <AppText style={styles.title} numberOfLines={numberOfLines}>
          {title}
        </AppText>
        {subtitle ? (
          <AppText variant="caption" numberOfLines={2} style={{ marginTop: 2 }}>
            {subtitle}
          </AppText>
        ) : null}
        {meta ? (
          <AppText variant="caption" numberOfLines={1} style={{ marginTop: 3, color: COLORS.primaryDark, fontFamily: FONTS.semibold }}>
            {meta}
          </AppText>
        ) : null}
        {children}
      </View>
      {right}
      {onPress && !right ? <Ionicons name="chevron-forward" size={18} color={COLORS.gray500} /> : null}
    </Wrap>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1, borderColor: COLORS.line, padding: SPACING.md - 2, marginBottom: 10, ...SHADOW },
  img: { width: 64, height: 64, borderRadius: RADIUS.md, backgroundColor: COLORS.paper2 },
  icon: { width: 46, height: 46, borderRadius: RADIUS.lg, alignItems: 'center', justifyContent: 'center' },
  title: { fontFamily: FONTS.semibold, fontSize: 15.5, lineHeight: 21, color: COLORS.black },
});
