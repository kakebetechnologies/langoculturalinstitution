// News from LCI (GET /api/v1/news): announcements and stories, newest first, with "load more".
import { useEffect, useState } from 'react';
import { router } from 'expo-router';
import Button from '../../components/Button';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import api, { errorMessage } from '../../lib/api';
import { fmtDate } from '../../lib/format';
import { useT } from '../../lib/i18n';

export default function News() {
  const { t } = useT();
  const [s, setS] = useState({ items: [], page: 0, more: true, loading: true, busy: false, error: null });

  async function load(page) {
    setS((x) => ({ ...x, loading: page === 1, busy: page > 1, error: null }));
    try {
      const { data } = await api.get('/news', { params: { page, limit: 15 } });
      setS((x) => ({ items: page === 1 ? data.data : [...x.items, ...data.data], page, more: data.meta.has_more, loading: false, busy: false, error: null }));
    } catch (e) {
      setS((x) => ({ ...x, loading: false, busy: false, error: x.items.length ? null : errorMessage(e) }));
    }
  }
  useEffect(() => {
    load(1);
  }, []);

  return (
    <Screen title={t('News')} onRefresh={() => load(1)} refreshing={false}>
      <StateView loading={s.loading} error={s.error} onRetry={() => load(1)} empty={!s.items.length} emptyIcon="newspaper-outline" emptyTitle="No news yet">
        {s.items.map((n) => (
          <Row key={n.id} image={n.image} icon={n.image ? undefined : 'newspaper-outline'} title={n.title} subtitle={n.excerpt} meta={fmtDate(n.published_at)} onPress={() => router.push(`/news/${n.slug}`)} />
        ))}
        {s.more ? <Button title={t('Load more')} variant="outline" small loading={s.busy} onPress={() => load(s.page + 1)} style={{ alignSelf: 'center', marginTop: 8 }} /> : null}
      </StateView>
    </Screen>
  );
}
