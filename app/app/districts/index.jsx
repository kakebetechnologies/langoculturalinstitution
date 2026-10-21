// Districts and Lira City (GET /api/v1/districts): the sub-region's totals, then one row per district with its population,
// headquarters and what it is best known for. Each opens its own page with demography, economy, places and history.
import { router } from 'expo-router';
import { View } from 'react-native';
import AppText from '../../components/AppText';
import Facts from '../../components/Facts';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import { SPACING } from '../../constants/theme';
import { number } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Districts() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/districts');
  const rows = data?.data ?? [];

  return (
    <Screen title={t('Districts & Lira City')} onRefresh={reload} refreshing={refreshing}>
      <StateView loading={loading} error={error} onRetry={retry} empty={!rows.length} emptyIcon="map-outline" emptyTitle="No districts yet">
        <Facts items={[{ label: t('People (2024 census)'), value: number(data?.totals?.population) }, { label: t('Districts and cities'), value: rows.length }, { label: t('Combined area'), value: `${number(Math.round(data?.totals?.area_km2 ?? 0))} km²` }]} />
        <AppText variant="heading" style={{ marginTop: SPACING.lg, marginBottom: 10 }}>
          {t('Choose a district')}
        </AppText>
        {rows.map((d) => (
          <Row
            key={d.id}
            image={d.image}
            icon={d.image ? undefined : d.kind === 'city' ? 'business-outline' : 'location-outline'}
            iconTone={d.kind === 'city' ? 'red' : 'sky'}
            title={d.kind === 'city' ? `${d.name} · ${t('City')}` : d.name}
            subtitle={d.known_for[0] ?? d.summary}
            meta={[d.population ? `${number(d.population)} ${t('people')}` : '', d.headquarters ? `${t('HQ')}: ${d.headquarters}` : ''].filter(Boolean).join(' · ')}
            onPress={() => router.push(`/districts/${d.slug}`)}
          />
        ))}
        <View style={{ height: 4 }} />
        <AppText variant="caption">{t('Population figures: Uganda Bureau of Statistics, National Population and Housing Census 2024.')}</AppText>
      </StateView>
    </Screen>
  );
}
