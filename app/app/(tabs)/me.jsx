// Me: the profile (name, clan, level with progress, points), badges, and the menu — my contributions, notifications, settings,
// support, about, privacy and sign out. Signed-out visitors see a sign-in card instead, with Settings and About still reachable.
import { Alert, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Avatar from '../../components/Avatar';
import ClanGroupCard from '../../components/clan/ClanGroupCard';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import SectionHeader from '../../components/SectionHeader';
import SignInPrompt from '../../components/SignInPrompt';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../../constants/theme';
import { useAuth } from '../../lib/auth';
import { useT } from '../../lib/i18n';

export default function Me() {
  const { t } = useT();
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
    <Screen big title={t('Me')} onRefresh={user ? refresh : undefined}>
      {user ? (
        <>
          <View style={styles.card}>
            <View style={styles.head}>
              <Avatar name={user.name} size={64} />
              <View style={{ flex: 1 }}>
                <AppText variant="heading">{user.name}</AppText>
                <AppText variant="caption">{user.clan ? `${user.clan.name} ${t('clan')}` : user.clan_other ? `${user.clan_other} ${t('clan')}` : t('Clan not chosen yet')}</AppText>
                <AppText variant="caption">{user.email}</AppText>
              </View>
            </View>
            <View style={styles.levelRow}>
              <AppText style={styles.level}>{t(user.level.name)}</AppText>
              <AppText variant="caption">{user.points} {t('points')}</AppText>
            </View>
            <View style={styles.bar}>
              <View style={[styles.fill, { width: `${Math.round(user.level.progress * 100)}%` }]} />
            </View>
            <AppText variant="caption">{user.level.next_name ? t('{n} points to {next}', { n: Math.max(0, user.level.next_at - user.points), next: t(user.level.next_name) }) : t('Top level reached')}</AppText>
          </View>

          <ClanGroupCard style={{ marginTop: SPACING.md }} />

          <SectionHeader title={t('Badges')} style={{ marginTop: SPACING.lg }} />
          <View style={styles.badges}>
            {user.badges.map((b) => (
              <View key={b.key} style={[styles.badge, !b.earned && { opacity: 0.45 }]}>
                <Ionicons name={b.earned ? 'ribbon' : 'ribbon-outline'} size={26} color={b.earned ? COLORS.yellowInk : COLORS.gray700} />
                <AppText style={styles.badgeName}>{t(b.name)}</AppText>
                <AppText variant="caption" style={{ textAlign: 'center' }}>
                  {t(b.description)}
                </AppText>
              </View>
            ))}
          </View>

          <SectionHeader title={t('My account')} style={{ marginTop: SPACING.lg }} />
          <Row icon="document-text-outline" title={t('My contributions')} subtitle={t('{n} approved · {p} pending', { n: user.counts.approved, p: user.counts.pending })} onPress={() => router.push('/contributions')} />
          <Row icon="notifications-outline" title={t('Notifications')} onPress={() => router.push('/notifications')} />
          <Row icon="person-outline" iconTone="dark" title={t('Edit profile')} subtitle={t('Name, clan, phone and password')} onPress={() => router.push('/profile')} />
        </>
      ) : (
        <SignInPrompt title={t('Sign in to your account')} text={t('See your contributions, points and badges, and keep your clan on your profile.')} next="/(tabs)/me" />
      )}

      <SectionHeader title={t('More')} style={{ marginTop: SPACING.lg }} />
      <Row icon="settings-outline" iconTone="dark" title={t('Settings')} subtitle={t('Language, offline dictionary, notifications')} onPress={() => router.push('/settings')} />
      <Row icon="heart-outline" iconTone="red" title={t('Support LCI')} subtitle={t('Donate and sponsor programs')} onPress={() => router.push('/support')} />
      <Row icon="information-circle-outline" iconTone="sky" title={t('About LCI and this app')} onPress={() => router.push('/about')} />
      <Row icon="shield-checkmark-outline" iconTone="dark" title={t('Privacy & terms')} onPress={() => router.push('/legal/privacy')} />
      {user ? <Row icon="log-out-outline" iconTone="red" title={t('Sign out')} onPress={confirmSignOut} /> : null}
    </Screen>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: COLORS.white, borderRadius: RADIUS.xl, padding: SPACING.md + 2, ...SHADOW },
  head: { flexDirection: 'row', alignItems: 'center', gap: 14, marginBottom: SPACING.md },
  levelRow: { flexDirection: 'row', alignItems: 'baseline', justifyContent: 'space-between' },
  level: { fontFamily: FONTS.bold, fontSize: 22, color: COLORS.primaryDark },
  bar: { height: 8, backgroundColor: COLORS.track, borderRadius: 4, overflow: 'hidden', marginVertical: 8 },
  fill: { height: 8, backgroundColor: COLORS.primary, borderRadius: 4 },
  badges: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  badge: { width: '31%', flexGrow: 1, alignItems: 'center', gap: 4, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, padding: 10, ...SHADOW },
  badgeName: { fontFamily: FONTS.semibold, fontSize: 12.5, textAlign: 'center', color: COLORS.black },
});
