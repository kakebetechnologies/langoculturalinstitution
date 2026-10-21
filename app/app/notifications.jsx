// Your notifications (GET /api/v1/me/notifications): contribution approved, more information needed, and so on. Tapping one marks
// it read and opens the contribution. "Mark all read" clears the badge.
import { router } from 'expo-router';
import Button from '../components/Button';
import Row from '../components/Row';
import Screen from '../components/Screen';
import StateView from '../components/StateView';
import { COLORS } from '../constants/theme';
import api from '../lib/api';
import { useAuth } from '../lib/auth';
import { fmtDate } from '../lib/format';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function Notifications() {
  const { t } = useT();
  const { user } = useAuth();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/me/notifications', { enabled: !!user, cache: false });
  const rows = data?.data ?? [];

  async function open(n) {
    if (!n.is_read) await api.post('/me/notifications/read', { id: n.id }).catch(() => {});
    reload();
    if (n.data?.code) router.push({ pathname: '/contribute/track', params: { code: n.data.code } });
  }

  return (
    <Screen
      title={t('Notifications')}
      onRefresh={reload}
      refreshing={refreshing}
      right={rows.some((n) => !n.is_read) ? <Button small variant="ghost" title={t('Mark all read')} onPress={async () => { await api.post('/me/notifications/read', { all: true }).catch(() => {}); reload(); }} /> : undefined}
    >
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!rows.length} emptyIcon="notifications-outline" emptyTitle="You are all caught up" emptyMessage="You will hear here when a reviewer approves or asks about a contribution.">
        {rows.map((n) => (
          <Row key={n.id} icon={n.type === 'contribution' ? 'document-text-outline' : 'notifications-outline'} iconTone={n.is_read ? 'dark' : 'gold'} title={n.title} subtitle={n.body} meta={fmtDate(n.created_at)} onPress={() => open(n)} style={!n.is_read ? { backgroundColor: COLORS.yellowMist, borderColor: '#F1E39A' } : undefined} />
        ))}
      </StateView>
    </Screen>
  );
}
