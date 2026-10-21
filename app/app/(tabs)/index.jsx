// Home dashboard (GET /api/v1/home): greeting with the notification bell, four quick actions, the word of the day with its
// dictionary shortcut, your contribution stats (signed in), a featured notable Lango, latest news, upcoming events and the
// donation call. Pull down to refresh; the last copy shows with no internet.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Image } from 'expo-image';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Avatar from '../../components/Avatar';
import Button from '../../components/Button';
import Card from '../../components/Card';
import Logo from '../../components/Logo';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import { HeaderAction } from '../../components/ScreenHeader';
import SectionHeader from '../../components/SectionHeader';
import Skeleton from '../../components/Skeleton';
import StateView from '../../components/StateView';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { mediaUrl } from '../../lib/api';
import { useAuth } from '../../lib/auth';
import { dayParts, firstName, fmtTime } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

const ACTIONS = [
  { key: 'contribute', icon: 'add-circle-outline', label: 'Contribute', href: '/(tabs)/contribute', bg: COLORS.primary, fg: COLORS.white },
  { key: 'explore', icon: 'compass-outline', label: 'Explore', href: '/(tabs)/explore', bg: COLORS.skyMist, fg: COLORS.skyDark },
  { key: 'learn', icon: 'book-outline', label: 'Learn', href: '/(tabs)/learn', bg: COLORS.yellowMist, fg: COLORS.yellowInk },
  { key: 'connect', icon: 'people-outline', label: 'Connect', href: '/connect', bg: COLORS.gray100, fg: COLORS.navy },
];

export default function Home() {
  const { t } = useT();
  const { user } = useAuth();
  const { data, loading, error, reload, retry, refreshing } = useFetch('/home');
  const home = data?.data;
  const unread = home?.unread ?? 0;

  return (
    <Screen
      back={false}
      scroll
      onRefresh={reload}
      refreshing={refreshing}
      contentStyle={{ paddingTop: 0 }}
    >
      <View style={styles.top}>
        <Logo size={44} />
        <View style={{ flex: 1 }}>
          <AppText variant="caption">{t('Apwoyo!')}</AppText>
          <AppText style={styles.hello} numberOfLines={2}>
            {user ? t('Welcome back, {name}', { name: firstName(user.name) }) : t('Welcome to Lango')}
          </AppText>
        </View>
        {user ? <HeaderAction icon="notifications-outline" label={t('Notifications')} badge={unread} onPress={() => router.push('/notifications')} /> : <Button small title={t('Sign in')} variant="dark" onPress={() => router.push('/(auth)/welcome')} />}
      </View>

      <View style={styles.actions}>
        {ACTIONS.map((a) => (
          <Pressable key={a.key} onPress={() => router.push(a.href)} accessibilityRole="button" style={({ pressed }) => [styles.action, { backgroundColor: a.bg }, pressed && { opacity: 0.88 }]}>
            <Ionicons name={a.icon} size={26} color={a.fg} />
            <AppText style={[styles.actionText, { color: a.fg }]}>{t(a.label)}</AppText>
          </Pressable>
        ))}
      </View>

      <StateView loading={loading} error={error} onRetry={retry}>
        {home ? (
          <>
            {home.word_of_the_day ? (
              <Pressable onPress={() => router.push({ pathname: '/dictionary', params: { q: home.word_of_the_day.lango } })} accessibilityRole="button" style={styles.wotd}>
                <AppText variant="eyebrow" style={{ color: '#9AA1AC' }}>
                  {t('Word of the day')}
                </AppText>
                <AppText style={styles.wotdWord}>{home.word_of_the_day.lango}</AppText>
                <AppText style={styles.wotdMeaning}>{String(home.word_of_the_day.english).split(';').map((s) => s.trim()).join(' · ')}</AppText>
                <AppText variant="label" style={{ color: COLORS.yellow, marginTop: 10 }}>
                  {t('Open the dictionary')} →
                </AppText>
              </Pressable>
            ) : null}

            {user ? (
              <Card style={{ marginTop: SPACING.md }} onPress={() => router.push('/(tabs)/me')}>
                <View style={styles.statsRow}>
                  <View style={styles.stat}>
                    <AppText style={styles.statNum}>{user.counts.approved}</AppText>
                    <AppText variant="caption">{t('approved')}</AppText>
                  </View>
                  <View style={styles.stat}>
                    <AppText style={[styles.statNum, { color: COLORS.yellowInk }]}>{user.counts.pending}</AppText>
                    <AppText variant="caption">{t('pending')}</AppText>
                  </View>
                  <View style={styles.stat}>
                    <AppText style={[styles.statNum, { color: COLORS.skyDark }]}>{user.points}</AppText>
                    <AppText variant="caption">{t('points')}</AppText>
                  </View>
                  <View style={{ flex: 1.2 }}>
                    <AppText variant="label">{t(user.level.name)}</AppText>
                    <View style={styles.bar}>
                      <View style={[styles.barFill, { width: `${Math.round(user.level.progress * 100)}%` }]} />
                    </View>
                    <AppText variant="caption">{user.level.next_name ? t('{n} points to {next}', { n: Math.max(0, user.level.next_at - user.points), next: t(user.level.next_name) }) : t('Top level reached')}</AppText>
                  </View>
                </View>
              </Card>
            ) : null}

            {home.featured_person ? (
              <View style={{ marginTop: SPACING.lg }}>
                <SectionHeader eyebrow={t('Notable Langos')} title={t('Know where you come from')} actionLabel={t('All')} onAction={() => router.push('/people')} />
                <Card onPress={() => router.push(`/people/${home.featured_person.slug}`)} style={{ flexDirection: 'row', gap: 14, alignItems: 'center' }}>
                  <Avatar name={home.featured_person.short_name} photo={home.featured_person.photo} size={64} square />
                  <View style={{ flex: 1 }}>
                    <AppText variant="subheading">{home.featured_person.short_name}</AppText>
                    <AppText variant="caption" style={{ color: COLORS.primaryDark, fontFamily: FONTS.semibold }}>
                      {home.featured_person.title}
                    </AppText>
                    <AppText variant="caption" numberOfLines={2} style={{ marginTop: 2 }}>
                      {home.featured_person.summary}
                    </AppText>
                  </View>
                </Card>
              </View>
            ) : null}

            {home.news?.length ? (
              <View style={{ marginTop: SPACING.lg }}>
                <SectionHeader title={t('Latest news')} actionLabel={t('See all')} onAction={() => router.push('/news')} />
                {home.news.map((n) => (
                  <Row key={n.id} image={n.image} title={n.title} subtitle={n.excerpt} onPress={() => router.push(`/news/${n.slug}`)} />
                ))}
              </View>
            ) : null}

            {home.events?.length ? (
              <View style={{ marginTop: SPACING.md }}>
                <SectionHeader title={t('Upcoming events')} actionLabel={t('See all')} onAction={() => router.push('/events')} />
                {home.events.map((e) => {
                  const d = dayParts(e.starts_at);
                  return (
                    <Row
                      key={e.id}
                      title={e.title}
                      subtitle={[fmtTime(e.starts_at), e.location].filter(Boolean).join(' · ')}
                      onPress={() => router.push(`/events/${e.slug}`)}
                      right={
                        <View style={styles.date}>
                          <AppText style={styles.dateDay}>{d.day}</AppText>
                          <AppText style={styles.dateMonth}>{d.month}</AppText>
                        </View>
                      }
                    />
                  );
                })}
              </View>
            ) : null}

            <Pressable onPress={() => router.push('/support')} accessibilityRole="button" style={styles.donate}>
              <Image source={require('../../assets/welcome/field.jpg')} style={StyleSheet.absoluteFill} contentFit="cover" />
              <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(15,60,90,0.82)' }]} />
              <AppText variant="eyebrow" style={{ color: COLORS.yellow }}>
                {t('Be part of the story')}
              </AppText>
              <AppText style={styles.donateTitle}>{t('Support LCI — your gift keeps Lango heritage alive')}</AppText>
              <View style={styles.donateBtn}>
                <Ionicons name="heart" size={16} color={COLORS.navy} />
                <AppText style={{ fontFamily: FONTS.semibold, color: COLORS.navy }}>{t('Donate now')}</AppText>
              </View>
            </Pressable>

            <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.lg }}>
              {t('{words} words documented · {clans} registered clans · {districts} districts', { words: Number(home.stats.words).toLocaleString('en-US'), clans: home.stats.clans, districts: home.stats.districts })}
            </AppText>
          </>
        ) : loading ? (
          <View style={{ gap: 12, marginTop: 8 }}>
            <Skeleton height={130} radius={RADIUS.lg} />
            <Skeleton height={80} radius={RADIUS.lg} />
            <Skeleton height={80} radius={RADIUS.lg} />
          </View>
        ) : null}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  top: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: SPACING.sm, marginBottom: SPACING.sm },
  hello: { fontFamily: FONTS.serif, fontSize: 19, lineHeight: 24, color: COLORS.black },
  actions: { flexDirection: 'row', gap: 10 },
  action: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: 8, paddingVertical: 16, borderRadius: RADIUS.lg },
  actionText: { fontFamily: FONTS.semibold, fontSize: 12.5 },
  wotd: { backgroundColor: COLORS.navy, borderRadius: RADIUS.lg, borderTopWidth: 4, borderTopColor: COLORS.yellow, padding: SPACING.md + 4, marginTop: SPACING.md },
  wotdWord: { fontFamily: FONTS.serif, fontSize: 34, lineHeight: 42, color: COLORS.yellow, marginTop: 2 },
  wotdMeaning: { fontFamily: FONTS.regular, fontSize: 16, lineHeight: 23, color: COLORS.white, marginTop: 2 },
  statsRow: { flexDirection: 'row', alignItems: 'center', gap: 14 },
  stat: { alignItems: 'center', minWidth: 46 },
  statNum: { fontFamily: FONTS.serif, fontSize: 24, lineHeight: 30, color: COLORS.primaryDark },
  bar: { height: 6, backgroundColor: COLORS.gray100, borderRadius: 3, overflow: 'hidden', marginVertical: 5 },
  barFill: { height: 6, backgroundColor: COLORS.yellow },
  date: { alignItems: 'center', paddingHorizontal: 10 },
  dateDay: { fontFamily: FONTS.serif, fontSize: 22, color: COLORS.primaryDark, lineHeight: 26 },
  dateMonth: { fontFamily: FONTS.bold, fontSize: 10.5, letterSpacing: 1, color: COLORS.gray700 },
  donate: { overflow: 'hidden', borderRadius: RADIUS.lg, padding: SPACING.lg, marginTop: SPACING.md, gap: 6, backgroundColor: COLORS.skyDark },
  donateTitle: { fontFamily: FONTS.serif, fontSize: 20, lineHeight: 26, color: COLORS.white },
  donateBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-start', backgroundColor: COLORS.yellow, paddingHorizontal: 16, paddingVertical: 10, borderRadius: RADIUS.md, marginTop: 8 },
});
