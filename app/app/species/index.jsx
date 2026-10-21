// Biodiversity (GET /api/v1/species): plants and animals of Lango, including the endangered and the medicinal. Spot one yourself?
// "Report a sighting" opens the contribution form for natural heritage.
import { router } from 'expo-router';
import Button from '../../components/Button';
import Pill from '../../components/Pill';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Species() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/species');
  const rows = data?.data ?? [];
  const report = () => router.push({ pathname: '/contribute/new', params: { type: 'natural' } });

  return (
    <Screen title={t('Biodiversity')} onRefresh={reload} refreshing={refreshing} footer={null}>
      <Button title={t('Report a sighting')} icon="camera-outline" onPress={report} style={{ marginBottom: 14 }} />
      <StateView loading={loading} error={error} onRetry={retry} empty={!rows.length} emptyIcon="leaf-outline" emptyTitle="The register is just starting" emptyMessage="Species are added as sightings are verified. Be the first to report one." emptyAction={{ title: 'Report a sighting', onPress: report }}>
        {rows.map((s) => (
          <Row key={s.id} image={s.image} icon={s.image ? undefined : 'leaf-outline'} title={s.name_lg ? `${s.name} (${s.name_lg})` : s.name} subtitle={s.scientific_name} onPress={() => router.push(`/species/${s.slug}`)}>
            <Pill label={t(s.conservation_label)} tone="grey" style={{ marginTop: 6 }} />
          </Row>
        ))}
      </StateView>
    </Screen>
  );
}
