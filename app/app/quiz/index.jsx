// Lango quizzes (GET /api/v1/quiz): short multiple-choice quizzes about the Lango people, their land, culture and famous sons and daughters.
// Every answer comes with the fact behind it and where the fact comes from. The member's best score on each quiz is shown.
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import { SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

const ICONS = { users: 'people', heart: 'heart', shield: 'shield-checkmark', award: 'ribbon', star: 'star', book: 'book', globe: 'globe', flag: 'flag' };
const TONES = ['sky', 'red', 'gold', 'green'];

export default function Quizzes() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/quiz', { cache: false });
  const sets = data?.data ?? [];

  return (
    <Screen title={t('Lango quizzes')} subtitle={t('Test what you know about the Lango people')} onRefresh={reload} refreshing={refreshing}>
      <AppText style={{ marginBottom: SPACING.md }}>
        {t('Short multiple-choice quizzes. After each quiz you see the right answers, the fact behind them and where the fact comes from. Play as often as you like; your best score is kept.')}
      </AppText>
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!!data && !sets.length} emptyIcon="help-circle-outline" emptyTitle="No quizzes yet" emptyMessage="Check back soon.">
        {sets.map((s, i) => (
          <Row
            key={s.slug}
            icon={ICONS[s.icon] ?? 'help-circle'}
            iconTone={TONES[i % TONES.length]}
            title={s.title}
            subtitle={s.summary}
            meta={s.best !== null ? t('Best score {score} of {total}', { score: s.best, total: s.questions }) : t('{n} questions', { n: s.questions })}
            onPress={() => router.push(`/quiz/${s.slug}`)}
          />
        ))}
      </StateView>
    </Screen>
  );
}
