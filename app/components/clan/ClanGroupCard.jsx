// "Your clan group" shortcut for Home and Me: the clan's name, how many people are in the group and how many new messages are waiting.
// A white card with a green icon. Shows nothing for signed-out visitors and for members who have not chosen a clan yet.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../AppText';
import { COLORS, FONTS, RADIUS, SHADOW, TONES } from '../../constants/theme';
import { useAuth } from '../../lib/auth';
import { useT } from '../../lib/i18n';

export default function ClanGroupCard({ style }) {
  const { t } = useT();
  const { user } = useAuth();
  const group = user?.clan_group;
  if (!user?.clan || !group?.has_clan) return null;

  const line = group.joined
    ? [group.members === 1 ? t('1 member') : t('{n} members', { n: group.members }), group.unread ? t('{n} new', { n: group.unread }) : null].filter(Boolean).join(' · ')
    : group.members
      ? t('{n} members · Tap to join', { n: group.members })
      : t('Be the first to join');

  return (
    <Pressable onPress={() => router.push('/clan-group')} accessibilityRole="button" accessibilityLabel={t('Your clan group')} style={({ pressed }) => [styles.card, style, pressed && { opacity: 0.92, transform: [{ scale: 0.99 }] }]}>
      <View style={styles.icon}>
        <Ionicons name="people" size={24} color={TONES.green.fg} />
        {group.unread > 0 ? (
          <View style={styles.dot}>
            <AppText allowFontScaling={false} style={styles.dotText}>
              {group.unread > 9 ? '9+' : group.unread}
            </AppText>
          </View>
        ) : null}
      </View>
      <View style={{ flex: 1 }}>
        <AppText style={styles.title} numberOfLines={1}>
          {t('{clan} clan group', { clan: user.clan.name })}
        </AppText>
        <AppText style={styles.sub} numberOfLines={1}>
          {line}
        </AppText>
      </View>
      <Ionicons name="chevron-forward" size={20} color={COLORS.gray500} />
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: { flexDirection: 'row', alignItems: 'center', gap: 14, borderRadius: RADIUS.lg + 2, padding: 14, backgroundColor: COLORS.white, ...SHADOW },
  icon: { width: 50, height: 50, borderRadius: 16, backgroundColor: TONES.green.bg, alignItems: 'center', justifyContent: 'center' },
  dot: { position: 'absolute', top: -5, right: -5, minWidth: 20, height: 20, borderRadius: 10, paddingHorizontal: 5, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center', borderWidth: 2, borderColor: COLORS.white },
  dotText: { fontFamily: FONTS.bold, fontSize: 11, color: COLORS.white },
  title: { fontFamily: FONTS.bold, fontSize: 16.5, color: COLORS.black },
  sub: { fontFamily: FONTS.medium, fontSize: 13, color: COLORS.gray700, marginTop: 1 },
});
