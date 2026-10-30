// Home (GET /api/v1/home): a centred title bar with the streak and the notification bell, a photo banner with the welcome and a search box
// that overlaps it, the word of the day, a grid of shortcut tiles (Explore, Listen and read), a "Coming up" carousel of events, today's
// cards, a notable Lango, the latest news and the donation call. Signed-in members also see their points and level. Pull down to
// refresh; the last copy shows with no internet.
import { useState } from 'react';
import { Pressable, RefreshControl, ScrollView, StyleSheet, TextInput, View, useWindowDimensions } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import AppText from '../../components/AppText';
import Avatar from '../../components/Avatar';
import Button from '../../components/Button';
import Card from '../../components/Card';
import ClanGroupCard from '../../components/clan/ClanGroupCard';
import { ChallengeCard, ClanFeedCard, ClanOfWeekCard, PhotoCard, StoryCard } from '../../components/daily/TodayCards';
import { MilestoneSheet, StreakChip } from '../../components/daily/Streaks';
import IconTile from '../../components/IconTile';
import Logo from '../../components/Logo';
import Row from '../../components/Row';
import { HeaderAction } from '../../components/ScreenHeader';
import SectionHeader from '../../components/SectionHeader';
import Skeleton from '../../components/Skeleton';
import StateView from '../../components/StateView';
import SyncBanner from '../../components/SyncBanner';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS, SHADOW, SHADOW_SM, SPACING, TONES } from '../../constants/theme';
import { useAuth } from '../../lib/auth';
import { useDailyCheckIn } from '../../lib/checkin';
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
  { href: '/assistant', icon: 'chatbubbles', label: 'Ask', tone: 'amber' },
];
const LISTEN = [
  { href: '/radio', icon: 'radio', label: 'Radio', tone: 'red' },
  { href: '/podcast', icon: 'mic', label: 'Podcast', tone: 'amber' },
  { href: '/bible', icon: 'book', label: 'Bible', tone: 'sky' },
  { href: '/support', icon: 'heart', label: 'Give', tone: 'green' },
];
const CARD_TONES = ['red', 'sky', 'amber', 'green'];

export default function Home() {
  const { t } = useT();
  const insets = useSafeAreaInsets();
  const { user, refresh } = useAuth();
  const { milestone, clear } = useDailyCheckIn(user, refresh);
  const { width } = useWindowDimensions();
  const { data, loading, error, reload, retry, refreshing } = useFetch('/home');
  const [q, setQ] = useState('');
  const home = data?.data;
  const unread = home?.unread ?? 0;
  const wotd = home?.word_of_the_day;
  const cardWidth = Math.min(width - 72, 400);

  const search = () => router.push({ pathname: '/dictionary', params: q.trim() ? { q: q.trim() } : {} });

  return (
    <View style={styles.screen}>
      <ScrollView
        style={{ flex: 1 }}
        contentContainerStyle={{ paddingBottom: SPACING.xxl }}
        keyboardShouldPersistTaps="handled"
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={reload} tintColor={COLORS.primary} colors={[COLORS.primary]} progressBackgroundColor={COLORS.white} />}
      >
        <View style={[styles.topBar, { paddingTop: insets.top + 10 }]}>
          <View style={styles.logoBtn}>
            <Logo size={30} />
          </View>
          <AppText style={[styles.pageTitle, { top: insets.top + 10 }]} pointerEvents="none">
            {t('Home')}
          </AppText>
          <View style={styles.right}>
            <StreakChip streaks={home?.streaks ?? user?.streaks} />
            {user ? <HeaderAction icon="notifications-outline" label={t('Notifications')} badge={unread} onPress={() => router.push('/notifications')} /> : <Button small title={t('Sign in')} variant="light" onPress={() => router.push('/(auth)/welcome')} />}
          </View>
        </View>

        <View style={styles.heroWrap}>
          <View style={styles.hero}>
            <Image source={require('../../assets/welcome/ridge.jpg')} style={StyleSheet.absoluteFill} contentFit="cover" />
            <LinearGradient colors={['rgba(14,16,20,0.2)', 'rgba(14,16,20,0.86)']} style={StyleSheet.absoluteFill} />
            <AppText style={styles.heroEyebrow}>{t('Our Culture. Our People. Our Future.')}</AppText>
            <AppText style={styles.heroTitle} numberOfLines={2}>
              {user ? t('Welcome back, {name}', { name: firstName(user.name) }) : t('Welcome to Lango')}
            </AppText>
            <AppText style={styles.heroSub}>{t('Apwoyo!')}</AppText>
          </View>
          <View style={styles.search}>
            <Ionicons name="search" size={20} color={COLORS.gray700} />
            <TextInput
              value={q}
              onChangeText={setQ}
              onSubmitEditing={search}
              returnKeyType="search"
              placeholder={t('Search a word in Leb Lango or English')}
              placeholderTextColor={COLORS.gray500}
              maxFontSizeMultiplier={MAX_FONT_SCALE}
              style={styles.input}
              autoCapitalize="none"
            />
            <Pressable onPress={search} accessibilityRole="button" accessibilityLabel={t('Search')} style={styles.searchGo}>
              <Ionicons name="arrow-forward" size={18} color={COLORS.white} />
            </Pressable>
          </View>
        </View>

        <SyncBanner />

        <View style={styles.body}>
          {wotd ? (
            <Pressable onPress={() => router.push('/word-of-the-day')} accessibilityRole="button" accessibilityLabel={t('Word of the day quiz')} style={({ pressed }) => [styles.wotd, pressed && { opacity: 0.93, transform: [{ scale: 0.99 }] }]}>
              <View style={styles.sun}>
                <Ionicons name="sunny" size={22} color="#B46B00" />
              </View>
              <View style={{ flex: 1 }}>
                <AppText variant="eyebrow">{t('Word of the day')}</AppText>
                <AppText lango style={styles.word} numberOfLines={1}>
                  {wotd.lango}
                </AppText>
                <AppText lango style={styles.meaning} numberOfLines={2}>
                  {String(wotd.english).split(';').map((s) => s.trim()).join(' · ')}
                </AppText>
              </View>
              <View style={styles.go}>
                <Ionicons name={home?.word_quiz?.answered ? 'checkmark' : 'arrow-forward'} size={20} color={COLORS.white} />
              </View>
            </Pressable>
          ) : null}

          {user ? (
            <Card onPress={() => router.push('/(tabs)/me')} style={styles.stats}>
              <View style={styles.statsRow}>
                <Avatar name={user.name} size={46} />
                <View style={styles.stat}>
                  <AppText style={styles.statNum}>{user.counts.approved}</AppText>
                  <AppText variant="caption">{t('approved')}</AppText>
                </View>
                <View style={styles.stat}>
                  <AppText style={[styles.statNum, { color: '#B46B00' }]}>{user.counts.pending}</AppText>
                  <AppText variant="caption">{t('pending')}</AppText>
                </View>
                <View style={styles.stat}>
                  <AppText style={[styles.statNum, { color: COLORS.skyDark }]}>{user.points}</AppText>
                  <AppText variant="caption">{t('points')}</AppText>
                </View>
              </View>
              <View style={styles.levelRow}>
                <AppText variant="label">{t(user.level.name)}</AppText>
                <AppText variant="caption">{user.level.next_name ? t('{n} points to {next}', { n: Math.max(0, user.level.next_at - user.points), next: t(user.level.next_name) }) : t('Top level reached')}</AppText>
              </View>
              <View style={styles.bar}>
                <View style={[styles.barFill, { width: `${Math.round(user.level.progress * 100)}%` }]} />
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

          <SectionHeader title={t('Listen and read')} style={{ marginTop: SPACING.md }} />
          <View style={styles.grid}>
            {LISTEN.map((x) => (
              <IconTile key={x.href} icon={x.icon} label={t(x.label)} tone={x.tone} onPress={() => router.push(x.href)} />
            ))}
          </View>
        </View>

        <StateView loading={loading} error={error} onRetry={retry}>
          {home ? (
            <>
              {home.events?.length ? (
                <View style={{ marginTop: SPACING.md }}>
                  <SectionHeader title={t('Coming up')} actionLabel={t('See all')} onAction={() => router.push('/events')} style={{ paddingHorizontal: SPACING.md }} />
                  <ScrollView horizontal showsHorizontalScrollIndicator={false} snapToInterval={cardWidth + 12} decelerationRate="fast" contentContainerStyle={{ paddingHorizontal: SPACING.md, paddingBottom: 14, gap: 12 }}>
                    {home.events.map((e, i) => {
                      const d = dayParts(e.starts_at);
                      const tone = TONES[CARD_TONES[i % CARD_TONES.length]];
                      return (
                        <Pressable key={e.id} onPress={() => router.push(`/events/${e.slug}`)} accessibilityRole="button" style={({ pressed }) => [styles.event, { width: cardWidth }, pressed && { opacity: 0.93 }]}>
                          <View style={styles.eventTop}>
                            <View style={[styles.dateTile, { backgroundColor: tone.bg }]}>
                              <AppText style={[styles.eventDay, { color: tone.fg }]}>{d.day}</AppText>
                              <AppText style={[styles.eventMonth, { color: tone.fg }]}>{d.month}</AppText>
                            </View>
                            <View style={{ flex: 1 }}>
                              <View style={[styles.when, { backgroundColor: tone.bg }]}>
                                <AppText style={[styles.whenText, { color: tone.fg }]}>{e.days_left === 0 ? t('Today') : e.days_left === 1 ? t('Tomorrow') : t('In {n} days', { n: e.days_left })}</AppText>
                              </View>
                              <AppText style={styles.eventTitle} numberOfLines={2}>
                                {e.title}
                              </AppText>
                            </View>
                          </View>
                          <View style={styles.eventMeta}>
                            <Ionicons name="location-outline" size={15} color={COLORS.gray700} />
                            <AppText variant="caption" numberOfLines={1} style={{ flex: 1 }}>
                              {[fmtTime(e.starts_at), e.location].filter(Boolean).join(' · ')}
                            </AppText>
                          </View>
                          <View style={styles.eventBtn}>
                            <AppText style={styles.eventBtnText}>{t('See the event')}</AppText>
                            <Ionicons name="arrow-forward" size={15} color={COLORS.primaryDark} />
                          </View>
                        </Pressable>
                      );
                    })}
                  </ScrollView>
                </View>
              ) : null}

              <View style={styles.body}>
                <SectionHeader title={t('Today in Lango')} style={{ marginBottom: 0 }} />
                <ChallengeCard challenge={home.challenge} />
                <StoryCard story={home.story} />
                <ClanOfWeekCard clan={home.clan_of_week} />
                <PhotoCard photo={home.photo_of_day} />
                <ClanFeedCard feed={home.clan_feed} />

                {home.featured_person ? (
                  <>
                    <SectionHeader eyebrow={t('Notable Langos')} title={t('Know where you come from')} actionLabel={t('All')} onAction={() => router.push('/people')} style={{ marginTop: SPACING.lg }} />
                    <Pressable onPress={() => router.push(`/people/${home.featured_person.slug}`)} accessibilityRole="button" style={styles.person}>
                      <Avatar name={home.featured_person.short_name} photo={home.featured_person.photo} size={64} square />
                      <View style={{ flex: 1 }}>
                        <AppText variant="subheading">{home.featured_person.short_name}</AppText>
                        <AppText variant="caption" style={{ color: '#B46B00', fontFamily: FONTS.semibold }}>
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
      <MilestoneSheet days={milestone} onClose={clear} />
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  body: { paddingHorizontal: SPACING.md },
  topBar: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: SPACING.md, paddingBottom: 12 },
  logoBtn: { width: 44, height: 44, borderRadius: 22, backgroundColor: COLORS.white, alignItems: 'center', justifyContent: 'center', ...SHADOW_SM },
  pageTitle: { position: 'absolute', left: 0, right: 0, height: 44, lineHeight: 44, textAlign: 'center', fontFamily: FONTS.bold, fontSize: 21, color: COLORS.black },
  right: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  heroWrap: { paddingHorizontal: SPACING.md },
  hero: { height: 196, borderRadius: RADIUS.xl + 2, overflow: 'hidden', backgroundColor: COLORS.navy, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 20, paddingBottom: 28, ...SHADOW },
  heroEyebrow: { fontFamily: FONTS.bold, fontSize: 10.5, letterSpacing: 1.2, textTransform: 'uppercase', color: COLORS.yellow, textAlign: 'center' },
  heroTitle: { fontFamily: FONTS.bold, fontSize: 26, lineHeight: 32, color: COLORS.white, textAlign: 'center', marginTop: 6 },
  heroSub: { fontFamily: FONTS.medium, fontSize: 14, color: 'rgba(255,255,255,0.8)', marginTop: 2 },
  search: { flexDirection: 'row', alignItems: 'center', gap: 10, marginTop: -26, marginHorizontal: 14, backgroundColor: COLORS.white, borderRadius: RADIUS.full, paddingLeft: 18, paddingRight: 6, ...SHADOW, shadowOpacity: 0.12 },
  input: { flex: 1, minHeight: 52, fontFamily: 'Inter_400Regular', fontSize: 15, color: COLORS.black },
  searchGo: { width: 42, height: 42, borderRadius: 21, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  wotd: { flexDirection: 'row', alignItems: 'center', gap: 12, marginTop: SPACING.md, backgroundColor: COLORS.white, borderRadius: RADIUS.lg + 2, padding: 14, ...SHADOW },
  sun: { width: 48, height: 48, borderRadius: 16, backgroundColor: '#FFF1D6', alignItems: 'center', justifyContent: 'center' },
  word: { fontFamily: 'Inter_700Bold', fontSize: 27, lineHeight: 34, color: COLORS.primaryDark },
  meaning: { fontFamily: 'Inter_400Regular', fontSize: 14, lineHeight: 20, color: COLORS.gray900 },
  go: { width: 42, height: 42, borderRadius: 21, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  stats: { marginTop: SPACING.md },
  join: { marginTop: SPACING.md },
  statsRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: 10 },
  stat: { alignItems: 'center', flex: 1 },
  statNum: { fontFamily: FONTS.bold, fontSize: 24, lineHeight: 30, color: COLORS.primaryDark },
  levelRow: { flexDirection: 'row', alignItems: 'baseline', justifyContent: 'space-between', marginTop: SPACING.sm + 2 },
  bar: { height: 8, backgroundColor: COLORS.track, borderRadius: 4, overflow: 'hidden', marginTop: 6 },
  barFill: { height: 8, backgroundColor: COLORS.yellow, borderRadius: 4 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', marginHorizontal: -4 },
  event: { borderRadius: RADIUS.lg + 2, padding: 16, backgroundColor: COLORS.white, ...SHADOW },
  eventTop: { flexDirection: 'row', alignItems: 'flex-start', gap: 12 },
  dateTile: { width: 58, alignItems: 'center', borderRadius: 16, paddingVertical: 8 },
  eventDay: { fontFamily: FONTS.bold, fontSize: 22, lineHeight: 26 },
  eventMonth: { fontFamily: FONTS.bold, fontSize: 10.5, letterSpacing: 1 },
  when: { alignSelf: 'flex-start', paddingHorizontal: 10, paddingVertical: 3, borderRadius: RADIUS.full },
  whenText: { fontFamily: FONTS.bold, fontSize: 10.5, letterSpacing: 0.6, textTransform: 'uppercase' },
  eventTitle: { fontFamily: FONTS.bold, fontSize: 17, lineHeight: 22, color: COLORS.black, marginTop: 6 },
  eventMeta: { flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 12 },
  eventBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-start', marginTop: 12 },
  eventBtnText: { fontFamily: FONTS.semibold, fontSize: 13.5, color: COLORS.primaryDark },
  person: { flexDirection: 'row', gap: 14, alignItems: 'center', backgroundColor: COLORS.white, borderRadius: RADIUS.lg + 2, padding: SPACING.md, ...SHADOW },
  donate: { overflow: 'hidden', borderRadius: RADIUS.xl, padding: SPACING.lg, marginTop: SPACING.lg, gap: 6, backgroundColor: COLORS.primaryDark },
  donateTitle: { fontFamily: FONTS.bold, fontSize: 20, lineHeight: 26, color: COLORS.white },
  donateBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-start', backgroundColor: COLORS.yellow, paddingHorizontal: 16, paddingVertical: 10, borderRadius: RADIUS.full, marginTop: 8 },
});
