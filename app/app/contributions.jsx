// All of my contributions with their review status (GET /api/v1/me/contributions), newest first, with "load more".
import { useEffect, useState } from 'react';
import { router } from 'expo-router';
import Pill from '../components/Pill';
import Row from '../components/Row';
import Screen from '../components/Screen';
import StateView from '../components/StateView';
import Button from '../components/Button';
import { STATUS_TONE } from '../constants/contribute';
import api, { errorMessage } from '../lib/api';
import { fmtDate } from '../lib/format';
import { useT } from '../lib/i18n';

export default function MyContributions() {
  const { t } = useT();
  const [state, setState] = useState({ items: [], page: 0, more: true, loading: true, error: null, busy: false });

  async function load(page) {
    setState((s) => ({ ...s, busy: page > 1, loading: page === 1, error: null }));
    try {
      const { data } = await api.get('/me/contributions', { params: { page, limit: 20 } });
      setState((s) => ({ items: page === 1 ? data.data : [...s.items, ...data.data], page, more: data.meta.has_more, loading: false, busy: false, error: null }));
    } catch (e) {
      setState((s) => ({ ...s, loading: false, busy: false, error: s.items.length ? null : errorMessage(e) }));
    }
  }

  useEffect(() => {
    load(1);
  }, []);

  return (
    <Screen title={t('My contributions')} onRefresh={() => load(1)} refreshing={false}>
      <StateView loading={state.loading} error={state.error} onRetry={() => load(1)} empty={!state.items.length} emptyIcon="camera-outline" emptyTitle="No contributions yet" emptyMessage="Your first approved contribution earns the “First contribution” badge." emptyAction={{ title: 'Contribute now', onPress: () => router.replace('/(tabs)/contribute') }}>
        {state.items.map((c) => (
          <Row key={c.id} icon="document-text-outline" title={c.title} subtitle={`${t(c.type_label)} · ${fmtDate(c.created_at)}`} onPress={() => router.push({ pathname: '/contribute/track', params: { code: c.code } })}>
            <Pill label={t(c.status_label)} tone={STATUS_TONE[c.status] ?? 'grey'} style={{ marginTop: 6 }} />
          </Row>
        ))}
        {state.more ? <Button title={t('Load more')} variant="outline" small loading={state.busy} onPress={() => load(state.page + 1)} style={{ alignSelf: 'center', marginTop: 8 }} /> : null}
      </StateView>
    </Screen>
  );
}
