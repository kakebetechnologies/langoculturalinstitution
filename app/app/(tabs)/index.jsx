// Home (GET /api/v1/home), in the FaithFellows layout: a deep gradient header with the greeting, the notification bell and the word of
// the day on a glass card; shortcut tiles into Explore; a "Coming up" carousel of events; a notable Lango; the latest news and the
// donation call. Signed-in members also see their points and level. Pull down to refresh; the last copy shows with no internet.
import { useCallback } from 'react';
import { Pressable, RefreshControl, ScrollView, StyleSheet, View, useWindowDimensions } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { router, useFocusEffect } from 'expo-router';
import { setStatusBarStyle } from 'expo-status-bar';
import AppText from '../../components/AppText';
import Avatar from '../../components/Avatar';
import Button from '../../components/Button';
import Card from '../../components/Card';
import ClanGroupCard from '../../components/clan/ClanGroupCard';
import HeroHeader from '../../components/HeroHeader';
import IconTile from '../../components/IconTile';
import Logo from '../../components/Logo';
import Row from '../../components/Row';
import { HeaderAction } from '../../components/ScreenHeader';
import SectionHeader from '../../components/SectionHeader';
import Skeleton from '../../components/Skeleton';
import StateView from '../../components/StateView';
import SyncBanner from '../../components/SyncBanner';
import { COLORS, FONTS, GRADIENTS, RADIUS, SHADOW, SPACING } from '../../constants/theme';
import { useAuth } from '../../lib/auth';
import { dayParts, firstName, fmtTime } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

const EXPLORE = [
  { href: '/dictionary', icon: 'book', label: 'Dictionary', tone: 'amber' },
  { href: '/translate', icon: 'language', label: 'Translator', tone: 'sky' },
  { href: '/proverbs', icon: 'ribbon', label: 'Proverbs', tone: 'red' },
  { href: '/clans', icon: 'people', label: 'Clans', tone: 'green' },
  { href: '/districts', icon: 'map', label: 'Districts', tone: 'slate' },
  { href: '/heritage', icon: 'color-palette', label: 'Heritage', tone: 'red' },
  { href: '/events', icon: 'calendar', label: 'Events', tone: 'sky' },
  { href: '/people', icon: 'star', label: 'Notable', tone: 'amber' },
];
const CARD_TONES = ['red', 'sky', 'amber', 'dark'];

export default function Home() {
  const { t } = useT();
  const { user } = useAuth();
  const { width } = useWindowDimensions();
  const { data, loading, error, reload, retry, refreshing } = useFetch('/home');
  const home = data?.data;
  const unread = home?.unread ?? 0;
  const wotd = home?.word_of_the_day;
  const cardWidth = Math.min(width - 56, 420);

  // The header is dark, so the status bar text goes light while Home is showing.
  useFocusEffect(
    useCallback(() => {
      setStatusBarStyle('light');
      return () => setStatusBarStyle('dark');
    }, []),
  );

  return (
    <View style={styles.screen}>
      <ScrollView
        style={{ flex: 1 }}
        contentContainerStyle={{ paddingBottom: SPACING.xxl }}
        keyboardShouldPersistTaps="handled"
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={reload} tintColor={COLORS.white} colors={[COLORS.primary]} progressBackgroundColor={COLORS.white} />}
      >
        <HeroHeader>
          <View style={styles.top}>
            <Logo tile size={46} />
            <View style={{ flex: 1 }}>
              <AppText style={styles.hi}>{t('Apwoyo!')}</AppText>
              <AppText style={styles.hello} numberOfLines={2}>
                {user ? t('Welcome back, {name}', { name: firstName(user.name) }) : t('Welcome to Lango')}
              </AppText>
            </View>
            {user ? <HeaderAction icon="notifications-outline" label={t('Notifications')} badge={unread} onDark onPress={() => router.push('/notifications')} /> : <Button small title={t('Sign in')} variant="glass" onPress={() => router.push('/(auth)/welcome')} />}
          </View>

          <View style={styles.glass}>
            {wotd ? (
              <Pressable onPress={() => router.push({ pathname: '/dictionary', params: { q: wotd.lango } })} accessibilityRole="button" accessibilityLabel={t('Open the dictionary')}>
                <View style={styles.liveRow}>
                  <View style={styles.tag}>
                    <Ionicons name="sunny" size={13} color={COLORS.yellow} />
                    <AppText style={styles.tagText}>{t('Word of the day')}</AppText>
                  </View>
                </View>
                <AppText lango style={styles.word}>
                  {wotd.lango}
                </AppText>
                <AppText lango style={styles.meaning} numberOfLines={2}>
                  {String(wotd.english).split(';').map((s) => s.trim()).join(' · ')}
                </AppText>
                <View style={styles.go}>
                  <Ionicons name="arrow-forward" size={22} color={COLORS.primary} />
                </View>
              </Pressable>
            ) : (
              <View>
                <AppText style={styles.tagText}>{t('Our Culture. Our People. Our Future.')}</AppText>
                <AppText style={[styles.meaning, { marginTop: 8 }]}>{t('Keep the language, clans and stories of the Lango people for the next generation.')}</AppText>
              </View>
            )}
          </View>

          <View style={styles.pills}>
            <Pill icon="add-circle" label={t('Contribute')} onPress={() => router.push('/(tabs)/contribute')} />
            <Pill icon="language" label={t('Translate')} onPress={() => router.push('/translate')} />
            <Pill icon="chatbubbles" label={t('Ask')} onPress={() => router.push('/assistant')} />
          </View>
        </HeroHeader>

        <SyncBanner />

        <View style={styles.body}>
          {user ? (
            <Card onPress={() => router.push('/(tabs)/me')} style={styles.stats}>
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
          ) : (
            <Card style={styles.join}>
              <AppText variant="heading">{t('Join the Lango community')}</AppText>
              <AppText variant="caption" style={{ marginTop: 4 }}>
                {t('Create a free account to suggest words, record stories and register your clan.')}
              </AppText>
              <View style={{ flexDirection: 'row', gap: 10, marginTop: 14 }}>
                <Button small title={t('Create account')} onPress={() => router.push('/(auth)/register')} style={{ flex: 1 }} />
                <Button small title={t('Sign in')} variant="outline" onPress={() => router.push('/(auth)/login')} style={{ flex: 1 }} />
              </View>
            </Card>
          )}

          {user ? <ClanGroupCard style={{ marginTop: SPACING.md }} /> : null}

          <SectionHeader title={t('Explore')} actionLabel={t('All')} onAction={() => router.push('/(tabs)/explore')} style={{ marginTop: SPACING.lg }} />
          <View style={styles.grid}>
            {EXPLORE.map((x) => (
              <IconTile key={x.href} icon={x.icon} label={t(x.label)} tone={x.tone} onPress={() => router.push(x.href)} />
            ))}
          </View>
        </View>

        <StateView loading={loading} error={error} onRetry={retry}>
          {home ? (
            <>
              {home.events?.length ? (
                <View style={{ marginTop: SPACING.sm }}>
                  <SectionHeader title={t('Coming up')} actionLabel={t('See all')} onAction={() => router.push('/events')} style={{ paddingHorizontal: SPACING.md }} />
                  <ScrollView horizontal showsHorizontalScrollIndicator={false} snapToInterval={cardWidth + 12} decelerationRate="fast" contentContainerStyle={{ paddingHorizontal: SPACING.md, gap: 12 }}>
                    {home.events.map((e, i) => {
                      const d = dayParts(e.starts_at);
                      return (
                        <Pressable key={e.id} onPress={() => router.push(`/events/${e.slug}`)} accessibilityRole="button" style={[styles.event, { width: cardWidth }]}>
                          <LinearGradient colors={GRADIENTS[CARD_TONES[i % CARD_TONES.length]]} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.eventFill}>
                            <View style={styles.eventTop}>
                              <View style={styles.tag}>
                                <AppText style={styles.tagText}>{t('Event')}</AppText>
                              </View>
                              <View style={styles.eventDate}>
                                <AppText style={styles.eventDay}>{d.day}</AppText>
                                <AppText style={styles.eventMonth}>{d.month}</AppText>
                              </View>
                            </View>
                            <AppText style={styles.eventTitle} numberOfLines={2}>
                              {e.title}
                            </AppText>
                            <AppText style={styles.eventMeta} numberOfLines={1}>
                              {[fmtTime(e.starts_at), e.location].filter(Boolean).join(' · ')}
                            </AppText>
                            <View style={styles.eventBtn}>
                              <AppText style={styles.eventBtnText}>{t('See the event')}</AppText>
                              <Ionicons name="arrow-forward" size={15} color={COLORS.primaryDark} />
                            </View>
                          </LinearGradient>
                        </Pressable>
                      );
                    })}
                  </ScrollView>
                </View>
              ) : null}

              <View style={[styles.body, { marginTop: SPACING.lg }]}>
                {home.featured_person ? (
                  <>
                    <SectionHeader eyebrow={t('Notable Langos')} title={t('Know where you come from')} actionLabel={t('All')} onAction={() => router.push('/people')} />
                    <Pressable onPress={() => router.push(`/people/${home.featured_person.slug}`)} accessibilityRole="button" style={styles.person}>
                      <Avatar name={home.featured_person.short_name} photo={home.featured_person.photo} size={64} square />
                      <View style={{ flex: 1 }}>
                        <AppText variant="subheading">{home.featured_person.short_name}</AppText>
                        <AppText variant="caption" style={{ color: COLORS.yellowInk, fontFamily: FONTS.semibold }}>
                          {home.featured_person.title}
                        </AppText>
                        <AppText variant="caption" numberOfLines={2} style={{ marginTop: 2 }}>
                          {home.featured_person.summary}
                        </AppText>
                      </View>
                    </Pressable>
                  </>
                ) : null}

                {home.news?.length ? (
                  <View style={{ marginTop: SPACING.lg }}>
                    <SectionHeader title={t('Latest news')} actionLabel={t('See all')} onAction={() => router.push('/news')} />
                    {home.news.map((n) => (
                      <Row key={n.id} image={n.image} title={n.title} subtitle={n.excerpt} onPress={() => router.push(`/news/${n.slug}`)} />
                    ))}
                  </View>
                ) : null}

                <Pressable onPress={() => router.push('/support')} accessibilityRole="button" style={styles.donate}>
                  <Image source={require('../../assets/welcome/field.jpg')} style={StyleSheet.absoluteFill} contentFit="cover" />
                  <LinearGradient colors={['rgba(20,22,27,0.35)', 'rgba(122,15,24,0.88)']} style={StyleSheet.absoluteFill} />
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
              </View>
            </>
          ) : loading ? (
            <View style={[styles.body, { gap: 12, marginTop: 8 }]}>
              <Skeleton height={130} radius={RADIUS.xl} />
              <Skeleton height={80} radius={RADIUS.lg} />
              <Skeleton height={80} radius={RADIUS.lg} />
            </View>
          ) : null}
        </StateView>
      </ScrollView>
    </View>
  );
}

/** A white pill with an icon, sitting on the dark header. */
function Pill({ icon, label, onPress }) {
  return (
    <Pressable onPress={onPress} accessibilityRole="button" style={({ pressed }) => [styles.pill, pressed && { opacity: 0.88 }]}>
      <Ionicons name={icon} size={16} color={COLORS.primary} />
      <AppText style={styles.pillText}>{label}</AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  body: { paddingHorizontal: SPACING.md },
  top: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: SPACING.md },
  hi: { fontFamily: FONTS.medium, fontSize: 13, color: 'rgba(255,255,255,0.78)' },
  hello: { fontFamily: FONTS.bold, fontSize: 19, lineHeight: 24, color: COLORS.white },
  glass: { backgroundColor: 'rgba(255,255,255,0.10)', borderWidth: 1, borderColor: 'rgba(255,255,255,0.24)', borderRadius: RADIUS.xl, padding: 18, minHeight: 138 },
  liveRow: { flexDirection: 'row', marginBottom: 10 },
  tag: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-start', backgroundColor: 'rgba(0,0,0,0.28)', paddingHorizontal: 12, paddingVertical: 6, borderRadius: RADIUS.full },
  tagText: { fontFamily: FONTS.bold, fontSize: 11.5, letterSpacing: 1, textTransform: 'uppercase', color: COLORS.white },
  word: { fontFamily: 'Inter_700Bold', fontSize: 36, lineHeight: 44, color: COLORS.yellow },
  meaning: { fontFamily: 'Inter_400Regular', fontSize: 15, lineHeight: 22, color: 'rgba(255,255,255,0.92)', paddingRight: 56 },
  go: { position: 'absolute', right: -4, bottom: -4, width: 52, height: 52, borderRadius: 26, backgroundColor: COLORS.white, alignItems: 'center', justifyContent: 'center', ...SHADOW },
  pills: { flexDirection: 'row', gap: 10, marginTop: SPACING.md },
  pill: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: COLORS.white, paddingHorizontal: 15, paddingVertical: 10, borderRadius: RADIUS.full },
  pillText: { fontFamily: FONTS.semibold, fontSize: 13, color: COLORS.black },
  stats: { marginTop: SPACING.md },
  join: { marginTop: SPACING.md },
  statsRow: { flexDirection: 'row', alignItems: 'center', gap: 14 },
  stat: { alignItems: 'center', minWidth: 46 },
  statNum: { fontFamily: FONTS.bold, fontSize: 24, lineHeight: 30, color: COLORS.primaryDark },
  bar: { height: 7, backgroundColor: COLORS.track, borderRadius: 4, overflow: 'hidden', marginVertical: 5 },
  barFill: { height: 7, backgroundColor: COLORS.primary, borderRadius: 4 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 2 },
  event: { borderRadius: RADIUS.xl, ...SHADOW },
  eventFill: { borderRadius: RADIUS.xl, padding: 18, minHeight: 190 },
  eventTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  eventDate: { alignItems: 'center', backgroundColor: 'rgba(255,255,255,0.2)', borderRadius: 14, paddingHorizontal: 12, paddingVertical: 6 },
  eventDay: { fontFamily: FONTS.bold, fontSize: 20, lineHeight: 24, color: COLORS.white },
  eventMonth: { fontFamily: FONTS.bold, fontSize: 10.5, letterSpacing: 1, color: 'rgba(255,255,255,0.85)' },
  eventTitle: { fontFamily: FONTS.bold, fontSize: 21, lineHeight: 27, color: COLORS.white, marginTop: 14 },
  eventMeta: { fontFamily: FONTS.medium, fontSize: 13, color: 'rgba(255,255,255,0.88)', marginTop: 3 },
  eventBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-start', backgroundColor: COLORS.white, paddingHorizontal: 16, paddingVertical: 10, borderRadius: RADIUS.full, marginTop: 'auto' },
  eventBtnText: { fontFamily: FONTS.semibold, fontSize: 13, color: COLORS.primaryDark },
  person: { flexDirection: 'row', gap: 14, alignItems: 'center', backgroundColor: COLORS.yellowMist, borderRadius: RADIUS.xl, padding: SPACING.md },
  donate: { overflow: 'hidden', borderRadius: RADIUS.xl, padding: SPACING.lg, marginTop: SPACING.lg, gap: 6, backgroundColor: COLORS.primaryDark },
  donateTitle: { fontFamily: FONTS.bold, fontSize: 20, lineHeight: 26, color: COLORS.white },
  donateBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-start', backgroundColor: COLORS.yellow, paddingHorizontal: 16, paddingVertical: 10, borderRadius: RADIUS.full, marginTop: 8 },
});
