// The word of the day as a ten-second quiz (GET /daily/word, POST /daily/word/answer): the Leb Lango word, four possible meanings,
// then the answer, the other meanings, an example when there is one, and the member's "words learned in a row" streak. It can be shared.
// Everyone gets the same word and the same four choices on the same day.
import { useState } from 'react';
import { Pressable, Share, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import Screen from '../components/Screen';
import StateView from '../components/StateView';
import { COLORS, FONTS, GRADIENTS, RADIUS, SHADOW, SPACING } from '../constants/theme';
import api, { errorMessage } from '../lib/api';
import { useAuth } from '../lib/auth';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function WordOfTheDay() {
  const { t } = useT();
  const { user, refresh } = useAuth();
  const { data, loading, error, retry } = useFetch('/daily/word', { cache: false });
  const q = data?.data;
  const [picked, setPicked] = useState(null);
  const [result, setResult] = useState(null);
  const [busy, setBusy] = useState(false);
  const [problem, setProblem] = useState(null);

  const answered = !!result || !!q?.answered;
  const answer = result?.answer ?? q?.answer;
  const correct = result ? result.correct : q?.was_correct;
  const meanings = result?.all_meanings ?? q?.all_meanings;
  const streaks = result?.streaks ?? user?.streaks;

  async function choose(option) {
    if (answered || busy) return;
    setPicked(option);
    setBusy(true);
    setProblem(null);
    try {
      const { data: res } = await api.post('/daily/word/answer', { choice: option });
      setResult(res.data);
      if (user) refresh();
    } catch (e) {
      setProblem(errorMessage(e));
      setPicked(null);
    } finally {
      setBusy(false);
    }
  }

  const share = () => Share.share({ message: q.share_text }).catch(() => {});

  return (
    <Screen title={t('Word of the day')} subtitle={t('Learn one Leb Lango word every day')}>
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!!data && !q?.word} emptyIcon="book-outline" emptyTitle="No word today" emptyMessage="Come back tomorrow.">
        {q?.word ? (
          <>
            <LinearGradient colors={GRADIENTS.deep} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.hero}>
              <AppText style={styles.eyebrow}>{q.date}</AppText>
              <AppText lango style={styles.word}>
                {q.word.lango}
              </AppText>
              {q.word.pos ? <AppText style={styles.pos}>{q.word.pos}</AppText> : null}
              <AppText style={styles.ask}>{answered ? (correct ? t('Correct! Well done.') : t('Not quite. Now you know.')) : t('What does it mean?')}</AppText>
            </LinearGradient>

            <View style={{ marginTop: SPACING.md, gap: 10 }}>
              {q.options.map((o) => {
                const isAnswer = answered && answer && o.toLowerCase() === answer.toLowerCase();
                const isWrongPick = answered && picked === o && !isAnswer;
                return (
                  <Pressable key={o} disabled={answered || busy} onPress={() => choose(o)} accessibilityRole="button" style={({ pressed }) => [styles.option, isAnswer && styles.right, isWrongPick && styles.wrong, pressed && { opacity: 0.9 }]}>
                    <AppText lango style={[styles.optionText, (isAnswer || isWrongPick) && { color: COLORS.white }]}>
                      {o}
                    </AppText>
                    {isAnswer ? <Ionicons name="checkmark-circle" size={22} color={COLORS.white} /> : isWrongPick ? <Ionicons name="close-circle" size={22} color={COLORS.white} /> : null}
                  </Pressable>
                );
              })}
            </View>
            {problem ? (
              <AppText variant="label" style={{ color: COLORS.danger, marginTop: SPACING.sm }}>
                {problem}
              </AppText>
            ) : null}

            {answered ? (
              <>
                <Card style={{ marginTop: SPACING.md }}>
                  <AppText variant="eyebrow">{t('All meanings')}</AppText>
                  <AppText lango style={styles.meanings}>
                    {String(meanings ?? answer).split(';').map((s) => s.trim()).join(' · ')}
                  </AppText>
                  {(result?.example_lg || q.word.example_lg) ? (
                    <View style={{ marginTop: SPACING.sm }}>
                      <AppText variant="eyebrow">{t('Example')}</AppText>
                      <AppText lango style={styles.meanings}>
                        {result?.example_lg ?? q.word.example_lg}
                      </AppText>
                      {(result?.example_en ?? q.word.example_en) ? <AppText variant="caption">{result?.example_en ?? q.word.example_en}</AppText> : null}
                    </View>
                  ) : null}
                  {!q.word.verified ? <AppText variant="caption" style={{ marginTop: SPACING.sm }}>{t('This entry comes from the Lango dictionary project and is still being checked by LCI.')}</AppText> : null}
                </Card>

                {user && streaks ? (
                  <Card style={styles.streak}>
                    <Ionicons name="flame" size={26} color="#F26A1B" />
                    <View style={{ flex: 1 }}>
                      <AppText variant="subheading">{streaks.words === 1 ? t('1 day of learning in a row') : t('{n} days of learning in a row', { n: streaks.words })}</AppText>
                      <AppText variant="caption">{t('Come back tomorrow for a new word.')}</AppText>
                    </View>
                  </Card>
                ) : !user ? (
                  <Card style={{ marginTop: SPACING.md }}>
                    <AppText variant="subheading">{t('Keep a learning streak')}</AppText>
                    <AppText variant="caption" style={{ marginTop: 2 }}>
                      {t('Create a free account to count the days you learn a word.')}
                    </AppText>
                    <Button small title={t('Create an account')} onPress={() => router.push('/(auth)/register')} style={{ alignSelf: 'flex-start', marginTop: SPACING.sm }} />
                  </Card>
                ) : null}

                <View style={styles.actions}>
                  <Button title={t('Share')} icon="share-social-outline" onPress={share} style={{ flex: 1 }} />
                  <Button title={t('Dictionary')} icon="book-outline" variant="outline" onPress={() => router.push({ pathname: '/dictionary', params: { q: q.word.lango } })} style={{ flex: 1 }} />
                </View>
                <Button title={t('Weekly Language Challenge')} icon="trophy-outline" variant="dark" onPress={() => router.push('/challenge')} style={{ marginTop: 10 }} />
              </>
            ) : null}
          </>
        ) : null}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  hero: { borderRadius: RADIUS.xl, padding: SPACING.lg, alignItems: 'center', ...SHADOW },
  eyebrow: { fontFamily: FONTS.bold, fontSize: 11.5, letterSpacing: 1.2, color: 'rgba(255,255,255,0.7)' },
  word: { fontFamily: 'Inter_700Bold', fontSize: 44, lineHeight: 54, color: COLORS.yellow, marginTop: 6 },
  pos: { fontFamily: FONTS.medium, fontSize: 14, color: 'rgba(255,255,255,0.8)' },
  ask: { fontFamily: FONTS.bold, fontSize: 17, color: COLORS.white, marginTop: SPACING.md },
  option: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', minHeight: 56, paddingHorizontal: 20, borderRadius: RADIUS.lg, backgroundColor: COLORS.white, ...SHADOW, shadowOpacity: 0.06 },
  right: { backgroundColor: COLORS.success },
  wrong: { backgroundColor: COLORS.danger },
  optionText: { fontFamily: FONTS.semibold, fontSize: 17, color: COLORS.black },
  meanings: { fontFamily: FONTS.regular, fontSize: 16, lineHeight: 24, color: COLORS.black, marginTop: 2 },
  streak: { flexDirection: 'row', alignItems: 'center', gap: 12, marginTop: SPACING.md },
  actions: { flexDirection: 'row', gap: 10, marginTop: SPACING.md },
});
