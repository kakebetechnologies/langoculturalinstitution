// The "Today in Lango" cards on Home. Each one is built from what is already in the archive and stays out of sight when there is nothing to show:
//   ChallengeCard (this week's Language Challenge) · StoryCard (one heritage item, notable Lango or history chapter a day) ·
//   ClanOfWeekCard (one clan featured each week) · PhotoCard (the newest approved community photo) · ClanFeedCard (your clan's standing and latest additions).
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import AppText from '../AppText';
import Pill from '../Pill';
import { COLORS, FONTS, GRADIENTS, RADIUS, SHADOW, SPACING } from '../../constants/theme';
import { mediaUrl } from '../../lib/api';
import { fmtDate } from '../../lib/format';
import { useT } from '../../lib/i18n';

function Cta({ label, light = true }) {
  return (
    <View style={[styles.cta, !light && { backgroundColor: COLORS.primary }]}>
      <AppText style={[styles.ctaText, !light && { color: COLORS.white }]}>{label}</AppText>
      <Ionicons name="arrow-forward" size={15} color={light ? COLORS.primaryDark : COLORS.white} />
    </View>
  );
}

export function ChallengeCard({ challenge, style }) {
  const { t } = useT();
  if (!challenge || !challenge.total) return null;
  const left = challenge.days_left <= 1 ? t('last day') : t('{n} days left', { n: challenge.days_left });

  return (
    <Pressable onPress={() => router.push('/challenge')} accessibilityRole="button" style={({ pressed }) => [styles.wrap, style, pressed && styles.pressed]}>
      <LinearGradient colors={GRADIENTS.amber} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.fill}>
        <View style={styles.row}>
          <View style={styles.icon}>
            <Ionicons name="trophy" size={24} color={COLORS.white} />
          </View>
          <View style={{ flex: 1 }}>
            <AppText style={styles.eyebrow}>{t('Weekly Language Challenge')}</AppText>
            <AppText style={styles.title}>{challenge.done ? t('You scored {score} out of {total}', { score: challenge.score, total: challenge.total }) : t('Test your Leb Lango')}</AppText>
            <AppText style={styles.sub}>{challenge.done ? t('See where you stand this week') : `${t('{n} questions', { n: challenge.total })} · ${left}`}</AppText>
          </View>
        </View>
        <Cta label={challenge.done ? t('Leaderboard') : t('Play now')} />
      </LinearGradient>
    </Pressable>
  );
}

export function StoryCard({ story, style }) {
  const { t } = useT();
  if (!story) return null;

  return (
    <Pressable onPress={() => router.push(story.route)} accessibilityRole="button" style={({ pressed }) => [styles.wrap, style, pressed && styles.pressed]}>
      <View style={[styles.fill, { minHeight: 190, padding: 0 }]}>
        {story.image ? <Image source={{ uri: mediaUrl(story.image) }} style={StyleSheet.absoluteFill} contentFit="cover" /> : <LinearGradient colors={GRADIENTS.dark} style={StyleSheet.absoluteFill} />}
        <LinearGradient colors={['rgba(20,22,27,0.15)', 'rgba(20,22,27,0.9)']} style={StyleSheet.absoluteFill} />
        <View style={styles.storyBody}>
          <AppText style={styles.eyebrow}>{t("Today's story")} · {t(story.label)}</AppText>
          <AppText style={styles.title} numberOfLines={2}>
            {story.title}
          </AppText>
          <AppText style={styles.sub} numberOfLines={2}>
            {story.excerpt}
          </AppText>
          <View style={[styles.row, { justifyContent: 'space-between', marginTop: 10 }]}>
            <AppText style={styles.sub}>{t('{n} min read', { n: story.minutes })}</AppText>
            <Cta label={t('Read')} />
          </View>
        </View>
      </View>
    </Pressable>
  );
}

export function ClanOfWeekCard({ clan, style }) {
  const { t } = useT();
  if (!clan) return null;
  const line = clan.totem ? `${t('Totem')}: ${clan.totem}` : clan.summary;

  return (
    <Pressable onPress={() => router.push(clan.is_mine ? '/clan-group' : `/clans/${clan.slug}`)} accessibilityRole="button" style={({ pressed }) => [styles.wrap, style, pressed && styles.pressed]}>
      <LinearGradient colors={GRADIENTS.green} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.fill}>
        <View style={styles.row}>
          <View style={styles.icon}>
            <Ionicons name="people" size={24} color={COLORS.white} />
          </View>
          <View style={{ flex: 1 }}>
            <AppText style={styles.eyebrow}>{t('Clan of the week')}</AppText>
            <AppText style={styles.title}>{clan.name}</AppText>
            {line ? (
              <AppText style={styles.sub} numberOfLines={2}>
                {line}
              </AppText>
            ) : null}
          </View>
        </View>
        <AppText style={[styles.sub, { marginTop: 10 }]}>
          {clan.members === 1 ? t('1 registered member') : t('{n} registered members', { n: clan.members })}
          {clan.is_mine ? ` · ${t('this is your clan')}` : ''}
        </AppText>
        <Cta label={clan.is_mine ? t('Open your clan group') : t('Meet the clan')} />
      </LinearGradient>
    </Pressable>
  );
}

export function PhotoCard({ photo, style }) {
  const { t } = useT();
  if (!photo?.image) return null;

  return (
    <Pressable onPress={() => router.push('/(tabs)/contribute')} accessibilityRole="button" accessibilityLabel={t('Photo of the day')} style={({ pressed }) => [styles.wrap, style, pressed && styles.pressed]}>
      <View style={[styles.fill, { minHeight: 200, padding: 0 }]}>
        <Image source={{ uri: mediaUrl(photo.image) }} style={StyleSheet.absoluteFill} contentFit="cover" />
        <LinearGradient colors={['rgba(20,22,27,0)', 'rgba(20,22,27,0.88)']} locations={[0.35, 1]} style={StyleSheet.absoluteFill} />
        <View style={styles.storyBody}>
          <AppText style={styles.eyebrow}>{t('Photo of the day')}</AppText>
          <AppText style={styles.title} numberOfLines={2}>
            {photo.title}
          </AppText>
          <AppText style={styles.sub}>{[photo.shared_by ? t('by {name}', { name: photo.shared_by }) : t('shared by a member'), photo.clan ? `${photo.clan} ${t('clan')}` : null, fmtDate(photo.created_at)].filter(Boolean).join(' · ')}</AppText>
          <View style={{ flexDirection: 'row', marginTop: 8 }}>
            <Cta label={t('Share yours')} />
          </View>
        </View>
      </View>
    </Pressable>
  );
}

export function ClanFeedCard({ feed, style }) {
  const { t } = useT();
  if (!feed) return null;
  const stat = (value, label) => (
    <View style={styles.stat}>
      <AppText style={styles.statValue}>{value}</AppText>
      <AppText variant="caption" numberOfLines={2} style={{ textAlign: 'center' }}>
        {label}
      </AppText>
    </View>
  );

  return (
    <View style={[styles.feed, style]}>
      <View style={styles.row}>
        <View style={[styles.icon, { backgroundColor: COLORS.successMist }]}>
          <Ionicons name="people" size={22} color="#146B3C" />
        </View>
        <View style={{ flex: 1 }}>
          <AppText variant="heading" numberOfLines={1}>
            {t('{clan} clan this week', { clan: feed.clan.name })}
          </AppText>
          <AppText variant="caption">{t('Your clan is number {rank} of {of}', { rank: feed.rank, of: feed.of })}</AppText>
        </View>
      </View>
      <View style={styles.stats}>
        {stat(`#${feed.rank}`, t('rank'))}
        {stat(feed.members, t('registered'))}
        {stat(feed.contributions, t('added'))}
        {stat(feed.learners, t('learners this month'))}
      </View>
      {feed.latest.length ? (
        <View style={{ marginTop: SPACING.sm }}>
          <AppText variant="label" style={{ marginBottom: 6 }}>
            {t('Latest from your clan')}
          </AppText>
          {feed.latest.map((x) => (
            <View key={x.id} style={styles.latest}>
              {x.image ? <Image source={{ uri: mediaUrl(x.image) }} style={styles.thumb} contentFit="cover" /> : <View style={[styles.thumb, { alignItems: 'center', justifyContent: 'center', backgroundColor: COLORS.yellowMist }]}><Ionicons name="document-text-outline" size={18} color={COLORS.yellowInk} /></View>}
              <View style={{ flex: 1 }}>
                <AppText style={{ fontFamily: FONTS.semibold, fontSize: 14 }} numberOfLines={1}>
                  {x.title}
                </AppText>
                <AppText variant="caption">{t(x.type_label)}</AppText>
              </View>
            </View>
          ))}
        </View>
      ) : (
        <AppText variant="caption" style={{ marginTop: SPACING.sm }}>
          {t('Nothing approved yet. A word, a proverb or a photo from you would be the first.')}
        </AppText>
      )}
      <View style={styles.links}>
        <Pressable onPress={() => router.push('/leaders?tab=clans')} accessibilityRole="button" style={styles.link}>
          <AppText style={styles.linkText}>{t('Clan leaderboard')}</AppText>
          <Ionicons name="chevron-forward" size={15} color={COLORS.primaryDark} />
        </Pressable>
        <Pressable onPress={() => router.push('/clan-group')} accessibilityRole="button" style={styles.link}>
          <AppText style={styles.linkText}>{t('Clan group')}</AppText>
          <Ionicons name="chevron-forward" size={15} color={COLORS.primaryDark} />
        </Pressable>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { borderRadius: RADIUS.xl, ...SHADOW, marginTop: SPACING.md },
  pressed: { opacity: 0.93, transform: [{ scale: 0.99 }] },
  fill: { borderRadius: RADIUS.xl, padding: 16, overflow: 'hidden', backgroundColor: COLORS.navy },
  row: { flexDirection: 'row', alignItems: 'center', gap: 14 },
  icon: { width: 48, height: 48, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.22)', alignItems: 'center', justifyContent: 'center' },
  eyebrow: { fontFamily: FONTS.bold, fontSize: 11, letterSpacing: 1.1, textTransform: 'uppercase', color: 'rgba(255,255,255,0.85)' },
  title: { fontFamily: FONTS.bold, fontSize: 19, lineHeight: 25, color: COLORS.white, marginTop: 1 },
  sub: { fontFamily: FONTS.medium, fontSize: 13, lineHeight: 18, color: 'rgba(255,255,255,0.9)', marginTop: 2 },
  cta: { flexDirection: 'row', alignItems: 'center', gap: 6, alignSelf: 'flex-start', backgroundColor: COLORS.white, paddingHorizontal: 15, paddingVertical: 9, borderRadius: RADIUS.full, marginTop: 12 },
  ctaText: { fontFamily: FONTS.semibold, fontSize: 13, color: COLORS.primaryDark },
  storyBody: { flex: 1, justifyContent: 'flex-end', padding: 16, minHeight: 190 },
  feed: { backgroundColor: COLORS.white, borderRadius: RADIUS.xl, padding: SPACING.md, marginTop: SPACING.md, ...SHADOW },
  stats: { flexDirection: 'row', gap: 8, marginTop: SPACING.md },
  stat: { flex: 1, alignItems: 'center', backgroundColor: COLORS.canvas, borderRadius: 14, paddingVertical: 10, paddingHorizontal: 4 },
  statValue: { fontFamily: FONTS.bold, fontSize: 20, lineHeight: 26, color: COLORS.primaryDark },
  latest: { flexDirection: 'row', alignItems: 'center', gap: 10, paddingVertical: 6 },
  thumb: { width: 40, height: 40, borderRadius: 10, backgroundColor: COLORS.paper2 },
  links: { flexDirection: 'row', justifyContent: 'space-between', marginTop: SPACING.sm, paddingTop: SPACING.sm, borderTopWidth: 1, borderTopColor: COLORS.line },
  link: { flexDirection: 'row', alignItems: 'center', gap: 2 },
  linkText: { fontFamily: FONTS.semibold, fontSize: 13.5, color: COLORS.primaryDark },
});
