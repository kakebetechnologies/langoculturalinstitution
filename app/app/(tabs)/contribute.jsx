// Contribute — the star feature. Pick what you want to share (cultural observation, natural heritage, a word or proverb, oral
// history, a historical document, a heritage site), then see your own contributions with their review status, anything waiting
// for internet, and the leaderboard. Needs an account; signed-out visitors see why and how to join.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import GradientTile from '../../components/GradientTile';
import Pill from '../../components/Pill';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import SectionHeader from '../../components/SectionHeader';
import SignInPrompt from '../../components/SignInPrompt';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../../constants/theme';
import { CONTRIBUTION_TYPES, STATUS_TONE } from '../../constants/contribute';
import { useAuth } from '../../lib/auth';
import { fmtDate } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useOutbox } from '../../lib/outbox';
import { useFetch } from '../../lib/useFetch';

// the tone names the contribution types use, as the tile tones
const TONE_OF = { red: 'red', sky: 'sky', gold: 'amber', dark: 'slate', green: 'green' };

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
          <View style={styles.grid}>
            {CONTRIBUTION_TYPES.map((c) => (
              <GradientTile
                key={`${c.type}${c.kind ?? ''}`}
                icon={c.icon}
                tone={TONE_OF[c.tone] ?? 'red'}
                title={t(c.title)}
                text={t(c.text)}
                onPress={() => router.push({ pathname: '/contribute/new', params: { type: c.type, kind: c.kind ?? '' } })}
              />
            ))}
          </View>

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
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  board: { flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: COLORS.white, borderRadius: RADIUS.lg + 2, padding: SPACING.md, marginTop: SPACING.lg, ...SHADOW },
});
