// "Your clan group" shortcut for Home and Me: the clan's name, how many people are in the group and how many new messages are waiting.
// Shows nothing for signed-out visitors and for members who have not chosen a clan yet.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import AppText from '../AppText';
import { COLORS, FONTS, GRADIENTS, RADIUS, SHADOW } from '../../constants/theme';
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
    <Pressable onPress={() => router.push('/clan-group')} accessibilityRole="button" accessibilityLabel={t('Your clan group')} style={({ pressed }) => [styles.wrap, style, pressed && { opacity: 0.92, transform: [{ scale: 0.99 }] }]}>
      <LinearGradient colors={GRADIENTS.green} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.fill}>
        <View style={styles.icon}>
          <Ionicons name="people" size={24} color={COLORS.white} />
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
        <Ionicons name="chevron-forward" size={20} color="rgba(255,255,255,0.9)" />
      </LinearGradient>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  wrap: { borderRadius: RADIUS.xl, ...SHADOW },
  fill: { flexDirection: 'row', alignItems: 'center', gap: 14, borderRadius: RADIUS.xl, padding: 16 },
  icon: { width: 48, height: 48, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.22)', alignItems: 'center', justifyContent: 'center' },
  dot: { position: 'absolute', top: -5, right: -5, minWidth: 20, height: 20, borderRadius: 10, paddingHorizontal: 5, backgroundColor: COLORS.yellow, alignItems: 'center', justifyContent: 'center' },
  dotText: { fontFamily: FONTS.bold, fontSize: 11, color: COLORS.navy },
  title: { fontFamily: FONTS.bold, fontSize: 16.5, color: COLORS.white },
  sub: { fontFamily: FONTS.medium, fontSize: 13, color: 'rgba(255,255,255,0.9)', marginTop: 1 },
});
