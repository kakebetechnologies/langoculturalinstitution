// Contribute — the star feature. Pick what you want to share (cultural observation, natural heritage, a word or proverb, oral
// history, a historical document, a heritage site), then see your own contributions with their review status, anything waiting
// for internet, and the leaderboard. Needs an account; signed-out visitors see why and how to join.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Pill from '../../components/Pill';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import SectionHeader from '../../components/SectionHeader';
import SignInPrompt from '../../components/SignInPrompt';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { CONTRIBUTION_TYPES, STATUS_TONE } from '../../constants/contribute';
import { useAuth } from '../../lib/auth';
import { fmtDate } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useOutbox } from '../../lib/outbox';
import { useFetch } from '../../lib/useFetch';

const TONES = { red: [COLORS.primaryMist, COLORS.primaryDark], sky: [COLORS.skyMist, COLORS.skyDark], gold: [COLORS.yellowMist, COLORS.yellowInk], dark: [COLORS.gray100, COLORS.navy] };

export default function Contribute() {
  const { t } = useT();
  const { user } = useAuth();
  const { items } = useOutbox();
  const mine = useFetch('/me/contributions', { params: { limit: 8 }, enabled: !!user, cache: !!user });

  return (
    <Screen big title={t('Contribute')} subtitle={t('Add to the Lango archive. Every contribution is reviewed.')} onRefresh={user ? mine.reload : undefined} refreshing={mine.refreshing}>
      {!user ? (
        <SignInPrompt title={t('Join to contribute')} text={t('Create a free account to share heritage, words and stories — and to follow each contribution through review.')} next="/(tabs)/contribute" />
      ) : (
        <>
          <SectionHeader eyebrow={t('Step 1')} title={t('What would you like to share?')} />
          {CONTRIBUTION_TYPES.map((c) => {
            const tone = TONES[c.tone];
            return (
              <Pressable key={`${c.type}${c.kind ?? ''}`} onPress={() => router.push({ pathname: '/contribute/new', params: { type: c.type, kind: c.kind ?? '' } })} accessibilityRole="button" style={({ pressed }) => [styles.card, pressed && { opacity: 0.9 }]}>
                <View style={[styles.icon, { backgroundColor: tone[0] }]}>
                  <Ionicons name={c.icon} size={24} color={tone[1]} />
                </View>
                <View style={{ flex: 1 }}>
                  <AppText style={styles.title}>{t(c.title)}</AppText>
                  <AppText variant="caption">{t(c.text)}</AppText>
                </View>
                <Ionicons name="chevron-forward" size={18} color={COLORS.gray500} />
              </Pressable>
            );
          })}

          {items.length ? (
            <View style={{ marginTop: SPACING.lg }}>
              <SectionHeader title={t('Waiting for internet')} />
              {items.map((q) => (
                <Row key={q.id} icon="cloud-upload-outline" iconTone="gold" title={q.title || t('Contribution')} subtitle={q.error ?? t('It will be sent automatically.')} />
              ))}
            </View>
          ) : null}

          <View style={{ marginTop: SPACING.lg }}>
            <SectionHeader title={t('My contributions')} actionLabel={mine.data?.meta?.total > 8 ? t('See all') : undefined} onAction={() => router.push('/contributions')} />
            {(mine.data?.data ?? []).length ? (
              mine.data.data.map((c) => (
                <Row key={c.id} icon="document-text-outline" title={c.title} subtitle={`${t(c.type_label)} · ${fmtDate(c.created_at)}`} onPress={() => router.push({ pathname: '/contribute/track', params: { code: c.code } })}>
                  <Pill label={t(c.status_label)} tone={STATUS_TONE[c.status] ?? 'grey'} style={{ marginTop: 6 }} />
                </Row>
              ))
            ) : (
              <AppText variant="caption">{mine.loading ? t('Loading…') : t('You have not contributed yet. Your first approved contribution earns the “First contribution” badge.')}</AppText>
            )}
          </View>

          <Pressable onPress={() => router.push('/leaderboard')} accessibilityRole="button" style={styles.board}>
            <Ionicons name="trophy-outline" size={22} color={COLORS.yellowInk} />
            <AppText style={{ flex: 1, fontFamily: FONTS.semibold, color: COLORS.black }}>{t('Top contributors')}</AppText>
            <Ionicons name="chevron-forward" size={18} color={COLORS.gray500} />
          </Pressable>
        </>
      )}
    </Screen>
  );
}

const styles = StyleSheet.create({
  card: { flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1, borderColor: COLORS.line, padding: SPACING.md, marginBottom: 10 },
  icon: { width: 48, height: 48, borderRadius: RADIUS.lg, alignItems: 'center', justifyContent: 'center' },
  title: { fontFamily: FONTS.serif, fontSize: 17, lineHeight: 22, color: COLORS.black },
  board: { flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: COLORS.yellowMist, borderRadius: RADIUS.lg, padding: SPACING.md, marginTop: SPACING.lg },
});
