// Me: a yellow profile header (name, clan), three stat cards that overlap it (approved, pending, points), the level, clan group, streak,
// badges, and the menus: my contributions, notifications, settings, support, about, privacy and sign out. Signed-out visitors see a
// sign-in card instead, with Settings and About still reachable.
import { Alert, RefreshControl, ScrollView, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import AppText from '../../components/AppText';
import Avatar from '../../components/Avatar';
import ClanGroupCard from '../../components/clan/ClanGroupCard';
import { StreakCard } from '../../components/daily/Streaks';
import Logo from '../../components/Logo';
import MenuGroup, { MenuRow } from '../../components/MenuGroup';
import SectionHeader from '../../components/SectionHeader';
import SignInPrompt from '../../components/SignInPrompt';
import SyncBanner from '../../components/SyncBanner';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING, TONES } from '../../constants/theme';
import { useAuth } from '../../lib/auth';
import { useT } from '../../lib/i18n';

function Stat({ icon, tone, value, label }) {
  const c = TONES[tone];

  return (
    <View style={styles.stat}>
      <View style={[styles.statIcon, { backgroundColor: c.bg }]}>
        <Ionicons name={icon} size={18} color={c.fg} />
      </View>
      <AppText style={styles.statNum}>{value}</AppText>
      <AppText variant="caption">{label}</AppText>
    </View>
  );
}

export default function Me() {
  const { t } = useT();
  const insets = useSafeAreaInsets();
  const { user, signOut, refresh } = useAuth();

  function confirmSignOut() {
    if (globalThis.confirm) {
      if (globalThis.confirm(t('Sign out of Lango App?'))) signOut();
      return;
    }
    Alert.alert(t('Sign out'), t('Sign out of Lango App?'), [
      { text: t('Cancel'), style: 'cancel' },
      { text: t('Sign out'), style: 'destructive', onPress: signOut },
    ]);
  }

  return (
    <View style={styles.screen}>
      <ScrollView contentContainerStyle={{ paddingBottom: SPACING.xxl }} refreshControl={user ? <RefreshControl refreshing={false} onRefresh={refresh} tintColor={COLORS.primary} colors={[COLORS.primary]} /> : undefined}>
        <LinearGradient colors={['#FFE75A', COLORS.yellow, '#F5C400']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={[styles.head, { paddingTop: insets.top + 12 }]}>
          <AppText style={styles.pageTitle}>{t('Me')}</AppText>
          {user ? (
            <>
              <View style={styles.ring}>
                <Avatar name={user.name} size={84} />
              </View>
              <AppText style={styles.name} numberOfLines={1}>
                {user.name}
              </AppText>
              <AppText style={styles.clan} numberOfLines={1}>
                {user.clan ? `${user.clan.name} ${t('clan')}` : user.clan_other ? `${user.clan_other} ${t('clan')}` : t('Clan not chosen yet')}
              </AppText>
            </>
          ) : (
            <>
              <Logo tile size={64} />
              <AppText style={styles.name}>{t('Welcome to Lango')}</AppText>
              <AppText style={styles.clan}>{t('Our Culture. Our People. Our Future.')}</AppText>
            </>
          )}
        </LinearGradient>

        <SyncBanner />

        <View style={styles.body}>
          {user ? (
            <>
              <View style={styles.statRow}>
                <Stat icon="checkmark-circle" tone="green" value={user.counts.approved} label={t('approved')} />
                <Stat icon="time" tone="amber" value={user.counts.pending} label={t('pending')} />
                <Stat icon="star" tone="red" value={user.points} label={t('points')} />
              </View>

              <View style={styles.level}>
                <View style={styles.levelRow}>
                  <AppText style={styles.levelName}>{t(user.level.name)}</AppText>
                  <AppText variant="caption">{user.email}</AppText>
                </View>
                <View style={styles.bar}>
                  <View style={[styles.fill, { width: `${Math.round(user.level.progress * 100)}%` }]} />
                </View>
                <AppText variant="caption">{user.level.next_name ? t('{n} points to {next}', { n: Math.max(0, user.level.next_at - user.points), next: t(user.level.next_name) }) : t('Top level reached')}</AppText>
              </View>

              <ClanGroupCard style={{ marginTop: SPACING.md }} />
              <StreakCard streaks={user.streaks} style={{ marginTop: SPACING.md }} />

              <SectionHeader title={t('Badges')} style={{ marginTop: SPACING.lg }} />
              <View style={styles.badges}>
                {user.badges.map((b) => (
                  <View key={b.key} style={[styles.badge, !b.earned && { opacity: 0.45 }]}>
                    <View style={[styles.badgeIcon, { backgroundColor: b.earned ? TONES.amber.bg : COLORS.gray100 }]}>
                      <Ionicons name={b.earned ? 'ribbon' : 'ribbon-outline'} size={22} color={b.earned ? TONES.amber.fg : COLORS.gray700} />
                    </View>
                    <AppText style={styles.badgeName}>{t(b.name)}</AppText>
                    <AppText variant="caption" style={{ textAlign: 'center' }}>
                      {t(b.description)}
                    </AppText>
                  </View>
                ))}
              </View>

              <MenuGroup title={t('My account')} style={{ marginTop: SPACING.lg }}>
                <MenuRow icon="document-text-outline" tone="amber" title={t('My contributions')} subtitle={t('{n} approved · {p} pending', { n: user.counts.approved, p: user.counts.pending })} onPress={() => router.push('/contributions')} />
                <MenuRow icon="notifications-outline" tone="sky" title={t('Notifications')} onPress={() => router.push('/notifications')} />
                <MenuRow icon="person-outline" tone="slate" title={t('Edit profile')} subtitle={t('Name, clan, phone and password')} onPress={() => router.push('/profile')} />
              </MenuGroup>
            </>
          ) : (
            <SignInPrompt title={t('Sign in to your account')} text={t('See your contributions, points and badges, and keep your clan on your profile.')} next="/(tabs)/me" />
          )}

          <MenuGroup title={t('More')} style={{ marginTop: SPACING.lg }}>
            <MenuRow icon="settings-outline" tone="slate" title={t('Settings')} subtitle={t('Language, offline dictionary, notifications')} onPress={() => router.push('/settings')} />
            <MenuRow icon="heart-outline" tone="red" title={t('Support LCI')} subtitle={t('Donate and sponsor programs')} onPress={() => router.push('/support')} />
            <MenuRow icon="information-circle-outline" tone="sky" title={t('About LCI and this app')} onPress={() => router.push('/about')} />
            <MenuRow icon="shield-checkmark-outline" tone="green" title={t('Privacy & terms')} onPress={() => router.push('/legal/privacy')} />
            {user ? <MenuRow icon="log-out-outline" tone="red" title={t('Sign out')} onPress={confirmSignOut} /> : null}
          </MenuGroup>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  head: { alignItems: 'center', paddingHorizontal: SPACING.md, paddingBottom: 56, borderBottomLeftRadius: RADIUS.xl + 8, borderBottomRightRadius: RADIUS.xl + 8 },
  pageTitle: { fontFamily: FONTS.bold, fontSize: 21, lineHeight: 27, color: COLORS.navy, marginBottom: SPACING.md },
  ring: { padding: 4, borderRadius: 50, backgroundColor: COLORS.white, ...SHADOW },
  name: { fontFamily: FONTS.bold, fontSize: 21, lineHeight: 27, color: COLORS.navy, marginTop: 10, textAlign: 'center' },
  clan: { fontFamily: FONTS.medium, fontSize: 14, color: 'rgba(27,30,37,0.72)', marginTop: 1, textAlign: 'center' },
  body: { paddingHorizontal: SPACING.md },
  statRow: { flexDirection: 'row', gap: 10, marginTop: -38 },
  stat: { flex: 1, alignItems: 'center', gap: 2, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, paddingVertical: 14, ...SHADOW },
  statIcon: { width: 34, height: 34, borderRadius: 12, alignItems: 'center', justifyContent: 'center', marginBottom: 2 },
  statNum: { fontFamily: FONTS.bold, fontSize: 22, lineHeight: 27, color: COLORS.black },
  level: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg + 2, padding: SPACING.md, marginTop: SPACING.md, ...SHADOW },
  levelRow: { flexDirection: 'row', alignItems: 'baseline', justifyContent: 'space-between' },
  levelName: { fontFamily: FONTS.bold, fontSize: 20, color: COLORS.primaryDark },
  bar: { height: 8, backgroundColor: COLORS.track, borderRadius: 4, overflow: 'hidden', marginVertical: 8 },
  fill: { height: 8, backgroundColor: COLORS.yellow, borderRadius: 4 },
  badges: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  badge: { width: '31%', flexGrow: 1, alignItems: 'center', gap: 4, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, padding: 10, ...SHADOW },
  badgeIcon: { width: 40, height: 40, borderRadius: 13, alignItems: 'center', justifyContent: 'center' },
  badgeName: { fontFamily: FONTS.semibold, fontSize: 12.5, textAlign: 'center', color: COLORS.black },
});
