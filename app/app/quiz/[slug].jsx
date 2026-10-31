// One Lango quiz (GET /quiz/{slug}, POST /quiz/{slug}/submit): the questions come with their answers mixed in a new order each time; after
// the last question the member sees the score, their best score, every right answer with the fact behind it and its source, and can play again.
import { useState } from 'react';
import { Linking, Pressable, Share, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import Card from '../../components/Card';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../../constants/theme';
import api, { errorMessage } from '../../lib/api';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Quiz() {
  const { t } = useT();
  const { slug } = useLocalSearchParams();
  const { data, loading, error, retry, reload } = useFetch(`/quiz/${slug}`, { cache: false });
  const quiz = data?.data;
  const [step, setStep] = useState(-1); // -1 = introduction
  const [answers, setAnswers] = useState({});
  const [result, setResult] = useState(null);
  const [busy, setBusy] = useState(false);
  const [problem, setProblem] = useState(null);

  async function submit(all) {
    setBusy(true);
    setProblem(null);
    try {
      const { data: res } = await api.post(`/quiz/${slug}/submit`, { answers: all });
      setResult(res.data);
    } catch (e) {
      setProblem(errorMessage(e));
    } finally {
      setBusy(false);
    }
  }

  function pick(q, option) {
    const all = { ...answers, [String(q.id)]: option };
    setAnswers(all);
    if (step + 1 < quiz.questions.length) setTimeout(() => setStep(step + 1), 250);
    else submit(all);
  }

  function again() {
    setStep(-1);
    setAnswers({});
    setResult(null);
    setProblem(null);
    reload();
  }

  return (
    <Screen title={quiz?.title ?? t('Quiz')} subtitle={quiz ? t('{n} questions', { n: quiz.total }) : undefined}>
      <StateView loading={loading && !data} error={error} onRetry={retry}>
        {result ? (
          <Results result={result} t={t} onAgain={again} />
        ) : quiz && step < 0 ? (
          <>
            <Card>
              <AppText variant="heading">{quiz.title}</AppText>
              {quiz.summary ? <AppText style={{ marginTop: 6 }}>{quiz.summary}</AppText> : null}
              <AppText variant="caption" style={{ marginTop: SPACING.sm }}>
                {t('Choose one answer for each question. The answers and their sources are shown at the end.')}
              </AppText>
              {quiz.best !== null ? (
                <AppText variant="label" style={{ marginTop: SPACING.sm, color: COLORS.primaryDark }}>
                  {t('Your best score: {score} of {total}', { score: quiz.best, total: quiz.total })}
                </AppText>
              ) : null}
            </Card>
            <Button title={t('Start')} onPress={() => setStep(0)} style={{ marginTop: SPACING.md }} />
          </>
        ) : quiz ? (
          <>
            <View style={styles.progress}>
              <View style={[styles.progressFill, { width: `${Math.round(((step + 1) / quiz.questions.length) * 100)}%` }]} />
            </View>
            <AppText variant="caption" style={{ marginTop: 6 }}>
              {t('Question {n} of {total}', { n: step + 1, total: quiz.questions.length })}
            </AppText>
            <View style={styles.qbox}>
              <AppText style={styles.question}>{quiz.questions[step].question}</AppText>
            </View>
            <View style={{ gap: 10, marginTop: SPACING.md }}>
              {quiz.questions[step].options.map((o) => {
                const chosen = answers[String(quiz.questions[step].id)] === o;
                return (
                  <Pressable key={o} disabled={busy || answers[String(quiz.questions[step].id)] !== undefined} onPress={() => pick(quiz.questions[step], o)} accessibilityRole="button" style={({ pressed }) => [styles.option, chosen && styles.chosen, pressed && { opacity: 0.9 }]}>
                    <AppText style={[styles.optionText, chosen && { color: COLORS.white }]}>{o}</AppText>
                  </Pressable>
                );
              })}
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
    </Screen>
  );
}

function Results({ result, t, onAgain }) {
  const share = () => Share.share({ message: result.share_text }).catch(() => {});
  const verdict = result.score === result.total ? t('Perfect score!') : result.score >= result.total * 0.7 ? t('Well done!') : t('Keep learning, then try again.');
  return (
    <>
      <Card style={styles.center}>
        <AppText variant="eyebrow">{t('Your score')}</AppText>
        <AppText style={styles.score}>
          {result.score}/{result.total}
        </AppText>
        <AppText variant="subheading">{verdict}</AppText>
        {result.best > result.score ? (
          <AppText variant="caption">{t('Your best score: {score} of {total}', { score: result.best, total: result.total })}</AppText>
        ) : null}
      </Card>
      <View style={{ flexDirection: 'row', gap: 10, marginTop: SPACING.md }}>
        <Button title={t('Share')} icon="share-social-outline" onPress={share} style={{ flex: 1 }} />
        <Button title={t('Play again')} icon="refresh" variant="dark" onPress={onAgain} style={{ flex: 1 }} />
      </View>
      <Button title={t('All quizzes')} variant="outline" onPress={() => router.replace('/quiz')} style={{ marginTop: 10 }} />
      <AppText variant="heading" style={{ marginTop: SPACING.lg, marginBottom: SPACING.sm }}>
        {t('The answers')}
      </AppText>
      {result.review.map((r) => (
        <View key={r.id} style={styles.review}>
          <Ionicons name={r.correct ? 'checkmark-circle' : 'close-circle'} size={22} color={r.correct ? COLORS.success : COLORS.danger} style={{ marginTop: 1 }} />
          <View style={{ flex: 1 }}>
            <AppText style={{ fontFamily: FONTS.semibold, fontSize: 15.5 }}>{r.question}</AppText>
            <AppText style={{ marginTop: 4, color: COLORS.success, fontFamily: FONTS.semibold }}>{r.answer}</AppText>
            {!r.correct ? (
              <AppText variant="caption">
                {r.choice ? `${t('You chose')}: ${r.choice}` : t('No answer')}
              </AppText>
            ) : null}
            {r.why ? <AppText variant="caption" style={{ marginTop: 4 }}>{r.why}</AppText> : null}
            {r.source ? (
              r.source_url ? (
                <Pressable onPress={() => Linking.openURL(r.source_url).catch(() => {})} accessibilityRole="link" hitSlop={8}>
                  <AppText variant="caption" style={{ marginTop: 4, color: COLORS.skyDark, textDecorationLine: 'underline' }}>
                    {t('Source')}: {r.source}
                  </AppText>
                </Pressable>
              ) : (
                <AppText variant="caption" style={{ marginTop: 4 }}>
                  {t('Source')}: {r.source}
                </AppText>
              )
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
  qbox: { backgroundColor: COLORS.white, borderRadius: RADIUS.xl, padding: SPACING.lg, marginTop: SPACING.md, ...SHADOW },
  question: { fontFamily: FONTS.serifSemi, fontSize: 20, lineHeight: 28, color: COLORS.black },
  option: { minHeight: 54, justifyContent: 'center', paddingHorizontal: 20, paddingVertical: 12, borderRadius: RADIUS.lg, backgroundColor: COLORS.white, ...SHADOW, shadowOpacity: 0.06 },
  chosen: { backgroundColor: COLORS.navy },
  optionText: { fontFamily: FONTS.semibold, fontSize: 16.5, lineHeight: 22, color: COLORS.black },
  review: { flexDirection: 'row', alignItems: 'flex-start', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, padding: 12, marginBottom: 8, ...SHADOW, shadowOpacity: 0.05 },
});
