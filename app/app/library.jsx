// Digital library (GET /api/v1/library): official documents, research papers and historical records. Tap to open or download.
import { Linking } from 'react-native';
import Pill from '../components/Pill';
import Row from '../components/Row';
import Screen from '../components/Screen';
import StateView from '../components/StateView';
import { mediaUrl } from '../lib/api';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function Library() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/library');
  const rows = data?.data ?? [];

  return (
    <Screen title={t('Digital library')} onRefresh={reload} refreshing={refreshing}>
      <StateView loading={loading} error={error} onRetry={retry} empty={!rows.length} emptyIcon="library-outline" emptyTitle="The library is being built" emptyMessage="Official documents, research and historical records appear here as they are published.">
        {rows.map((d) => (
          <Row key={d.id} icon="document-text-outline" title={d.title} subtitle={d.description} onPress={() => Linking.openURL(mediaUrl(d.download_url)).catch(() => {})}>
            <Pill label={`${d.category}${d.year ? ' · ' + d.year : ''}`} tone="grey" style={{ marginTop: 6 }} />
          </Row>
        ))}
      </StateView>
    </Screen>
  );
}
