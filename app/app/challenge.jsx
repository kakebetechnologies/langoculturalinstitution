// The weekly Language Challenge (GET /challenge, POST /challenge/submit): ten "what does this word mean?" questions, the same for everyone,
// one attempt a week. After the last question the member sees the score, their place this week, the right answers, and can share the result.
import { useState } from 'react';
import { Pressable, Share, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import Screen from '../components/Screen';
import SignInPrompt from '../components/SignInPrompt';
import StateView from '../components/StateView';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../constants/theme';
import api, { errorMessage } from '../lib/api';
import { useAuth } from '../lib/auth';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function Challenge() {
  const { t } = useT();
  const { user, refresh } = useAuth();
  const { data, loading, error, retry, reload } = useFetch('/challenge', { cache: false });
  const c = data?.data;
  const [step, setStep] = useState(-1); // -1 = introduction
  const [answers, setAnswers] = useState({});
  const [result, setResult] = useState(null);
  const [busy, setBusy] = useState(false);
  const [problem, setProblem] = useState(null);

  async function submit(all) {
    setBusy(true);
    setProblem(null);
    try {
      const { data: res } = await api.post('/challenge/submit', { answers: all });
      setResult(res.data);
      refresh();
    } catch (e) {
      setProblem(errorMessage(e));
    } finally {
      setBusy(false);
    }
  }

  function pick(q, option) {
    const all = { ...answers, [String(q.id)]: option };
    setAnswers(all);
    if (step + 1 < c.questions.length) setTimeout(() => setStep(step + 1), 250);
    else submit(all);
  }

  const share = (text) => Share.share({ message: text }).catch(() => {});
  const left = c ? (c.days_left <= 1 ? t('last day') : t('{n} days left', { n: c.days_left })) : '';

  return (
    <Screen title={t('Language Challenge')} subtitle={c ? `${t('This week')} · ${left}` : t('Ten questions, once a week')}>
      {!user ? (
        <SignInPrompt title={t('Join to play')} text={t('Create a free account to take the weekly challenge and join the leaderboard.')} next="/challenge" />
      ) : (
        <StateView loading={loading && !data} error={error} onRetry={retry} empty={!!data && !c?.total} emptyIcon="trophy-outline" emptyTitle="No challenge this week" emptyMessage="The dictionary does not have enough words yet.">
          {result ? (
            <Results result={result} t={t} onShare={() => share(result.share_text)} />
          ) : c?.done ? (
            <>
              <Card style={styles.center}>
                <Ionicons name="trophy" size={34} color={COLORS.yellowInk} />
                <AppText style={styles.score}>
                  {c.score}/{c.total}
                </AppText>
                <AppText variant="subheading">{t('You have taken this week’s challenge')}</AppText>
                <AppText variant="caption" style={{ textAlign: 'center', marginTop: 4 }}>
                  {t('A new challenge starts on Monday. See how you compare.')}
                </AppText>
              </Card>
              <Button title={t('Leaderboard')} icon="podium-outline" onPress={() => router.push('/leaders?tab=challenge')} style={{ marginTop: SPACING.md }} />
            </>
          ) : step < 0 ? (
            <>
              <Card>
                <AppText variant="heading">{t('Ten words, ten meanings')}</AppText>
                <AppText style={{ marginTop: 6 }}>{t('Each question shows a Leb Lango word. Choose what it means. Everyone gets the same ten questions this week, and you can play once.')}</AppText>
                <AppText variant="caption" style={{ marginTop: SPACING.sm }}>
                  {t('Words come from the Lango dictionary project. Entries that LCI language experts have checked carry a tick in the dictionary.')}
                </AppText>
              </Card>
              <Button title={t('Start')} onPress={() => setStep(0)} style={{ marginTop: SPACING.md }} />
              <Button title={t('Leaderboard')} variant="outline" onPress={() => router.push('/leaders?tab=challenge')} style={{ marginTop: 10 }} />
            </>
          ) : c ? (
            <>
              <View style={styles.progress}>
                <View style={[styles.progressFill, { width: `${Math.round(((step + 1) / c.questions.length) * 100)}%` }]} />
              </View>
              <AppText variant="caption" style={{ marginTop: 6 }}>
                {t('Question {n} of {total}', { n: step + 1, total: c.questions.length })}
              </AppText>
              <View style={styles.qbox}>
                <AppText variant="eyebrow">{t('What does this word mean?')}</AppText>
                <AppText lango style={styles.word}>
                  {c.questions[step].word}
                </AppText>
              </View>
              <View style={{ gap: 10, marginTop: SPACING.md }}>
                {c.questions[step].options.map((o) => (
                  <Pressable key={o} disabled={busy || answers[String(c.questions[step].id)] !== undefined} onPress={() => pick(c.questions[step], o)} accessibilityRole="button" style={({ pressed }) => [styles.option, answers[String(c.questions[step].id)] === o && styles.chosen, pressed && { opacity: 0.9 }]}>
                    <AppText lango style={[styles.optionText, answers[String(c.questions[step].id)] === o && { color: COLORS.white }]}>
                      {o}
                    </AppText>
                  </Pressable>
                ))}
              </View>
              {busy ? <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.md }}>{t('Checking your answers…')}</AppText> : null}
              {problem ? (
                <View style={{ marginTop: SPACING.md }}>
                  <AppText variant="label" style={{ color: COLORS.danger }}>
                    {problem}
                  </AppText>
                  <Button small title={t('Try again')} onPress={() => submit(answers)} style={{ alignSelf: 'flex-start', marginTop: 8 }} />
                </View>
              ) : null}
            </>
          ) : null}
        </StateView>
      )}
    </Screen>
  );
}

function Results({ result, t, onShare }) {
  return (
    <>
      <Card style={styles.center}>
        <AppText variant="eyebrow">{t('Your score')}</AppText>
        <AppText style={styles.score}>
          {result.score}/{result.total}
        </AppText>
        <AppText variant="subheading">{t('You are number {n} this week so far', { n: result.rank })}</AppText>
      </Card>
      <View style={{ flexDirection: 'row', gap: 10, marginTop: SPACING.md }}>
        <Button title={t('Share')} icon="share-social-outline" onPress={onShare} style={{ flex: 1 }} />
        <Button title={t('Leaderboard')} icon="podium-outline" variant="dark" onPress={() => router.push('/leaders?tab=challenge')} style={{ flex: 1 }} />
      </View>
      <AppText variant="heading" style={{ marginTop: SPACING.lg, marginBottom: SPACING.sm }}>
        {t('The answers')}
      </AppText>
      {result.review.map((r) => (
        <View key={r.id} style={styles.review}>
          <Ionicons name={r.correct ? 'checkmark-circle' : 'close-circle'} size={22} color={r.correct ? COLORS.success : COLORS.danger} />
          <View style={{ flex: 1 }}>
            <AppText lango style={{ fontFamily: FONTS.semibold, fontSize: 16 }}>
              {r.word} = {r.answer}
            </AppText>
            {!r.correct && r.choice ? (
              <AppText lango variant="caption">
                {t('You chose')}: {r.choice}
              </AppText>
            ) : null}
          </View>
        </View>
      ))}
    </>
  );
}

const styles = StyleSheet.create({
  center: { alignItems: 'center', gap: 4 },
  score: { fontFamily: FONTS.bold, fontSize: 48, lineHeight: 56, color: COLORS.primaryDark },
  progress: { height: 8, backgroundColor: COLORS.track, borderRadius: 4, overflow: 'hidden' },
  progressFill: { height: 8, backgroundColor: COLORS.primary, borderRadius: 4 },
  qbox: { alignItems: 'center', backgroundColor: COLORS.white, borderRadius: RADIUS.xl, padding: SPACING.lg, marginTop: SPACING.md, ...SHADOW },
  word: { fontFamily: 'Inter_700Bold', fontSize: 40, lineHeight: 50, color: COLORS.black, marginTop: 6 },
  option: { minHeight: 54, justifyContent: 'center', paddingHorizontal: 20, borderRadius: RADIUS.lg, backgroundColor: COLORS.white, ...SHADOW, shadowOpacity: 0.06 },
  chosen: { backgroundColor: COLORS.navy },
  optionText: { fontFamily: FONTS.semibold, fontSize: 17, color: COLORS.black },
  review: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, padding: 12, marginBottom: 8, ...SHADOW, shadowOpacity: 0.05 },
});
