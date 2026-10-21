// One district or Lira City (GET /api/v1/districts/{slug}): quick facts (population, area, density, created, headquarters), what
// it is best known for, then people and demography, economy, places to visit, history, neighbours and distances.
import { Linking, StyleSheet, View } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import DetailScreen from '../../components/DetailScreen';
import Facts from '../../components/Facts';
import InfoRow from '../../components/InfoRow';
import Row from '../../components/Row';
import RichText from '../../components/RichText';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { number } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

function Block({ title, html }) {
  if (!html || !String(html).trim()) return null;
  return (
    <View>
      <AppText variant="heading" style={styles.h}>
        {title}
      </AppText>
      <RichText html={html} />
    </View>
  );
}

export default function District() {
  const { t } = useT();
  const { slug } = useLocalSearchParams();
  const { data, loading, error, retry } = useFetch(`/districts/${slug}`);
  const d = data?.data;
  const density = d?.population && d?.area_km2 ? Math.round(d.population / d.area_km2) : null;
  const city = d?.kind === 'city';

  return (
    <DetailScreen loading={loading} error={error} onRetry={retry} hero={d ? { image: d.image, eyebrow: city ? t('City of Lango') : t('District of Lango'), title: city ? d.name : `${d.name} ${t('District')}`, subtitle: d.summary, fallback: require('../../assets/welcome/aerial.jpg') } : null}>
      {d ? (
        <>
          <Facts
            items={[
              d.population ? { label: t('People ({y} census)', { y: d.pop_year }), value: number(d.population) } : null,
              d.area_km2 ? { label: t('Area'), value: `${number(Math.round(d.area_km2))} km²` } : null,
              density ? { label: t('People per km²'), value: number(density) } : null,
              d.headquarters ? { label: city ? t('City centre') : t('Headquarters'), value: d.headquarters } : null,
              d.created_year ? { label: city ? t('Became a city') : t('Created'), value: String(d.created_year) } : null,
            ]}
          />

          <Block title={t('Overview')} html={d.overview_html} />

          {d.known_for.length ? (
            <View>
              <AppText variant="heading" style={styles.h}>
                {t('Best known for')}
              </AppText>
              {d.known_for.map((k, i) => (
                <View key={i} style={styles.known}>
                  <View style={styles.num}>
                    <AppText style={styles.numText}>{i + 1}</AppText>
                  </View>
                  <AppText style={{ flex: 1, color: COLORS.black, fontFamily: FONTS.medium }}>{k}</AppText>
                </View>
              ))}
            </View>
          ) : null}

          <Block title={t('People & demography')} html={d.demography_html} />
          <Block title={t('Economy & livelihoods')} html={d.economy_html} />
          <Block title={t('Places to visit')} html={d.attractions_html} />
          {d.places?.length ? <View style={{ marginTop: 6 }}>{d.places.map((p) => <Row key={p.slug} icon="location-outline" title={p.title} subtitle={p.summary} onPress={() => router.push(`/heritage/${p.slug}`)} />)}</View> : null}
          <Block title={t('History')} html={d.history_html} />

          <AppText variant="heading" style={styles.h}>
            {t('Quick facts')}
          </AppText>
          {d.parent ? <InfoRow icon="git-branch-outline" title={t('Carved from')} subtitle={d.parent} /> : null}
          {d.neighbours ? <InfoRow icon="navigate-outline" title={t('Neighbours')} subtitle={d.neighbours} /> : null}
          {d.subdivisions ? <InfoRow icon="grid-outline" title={t('Divisions')} subtitle={d.subdivisions} /> : null}
          {d.distance ? <InfoRow icon="car-outline" title={t('Distances')} subtitle={d.distance} /> : null}
          {d.lat ? <InfoRow icon="map-outline" title={t('Open in Maps')} subtitle={`${d.lat.toFixed(3)}, ${d.lng.toFixed(3)}`} onPress={() => Linking.openURL(`https://www.openstreetmap.org/?mlat=${d.lat}&mlon=${d.lng}#map=11/${d.lat}/${d.lng}`).catch(() => {})} /> : null}

          {d.people?.length ? (
            <View>
              <AppText variant="heading" style={styles.h}>
                {t('Notable people')}
              </AppText>
              {d.people.map((p) => (
                <Row key={p.slug} avatar={{ name: p.name, photo: p.photo }} title={p.short_name} subtitle={p.title} onPress={() => router.push(`/people/${p.slug}`)} />
              ))}
            </View>
          ) : null}

          <AppText variant="caption" style={{ marginTop: SPACING.lg }}>
            {t('Population figures are from the 2024 National Population and Housing Census (Uganda Bureau of Statistics). Descriptions are compiled from published sources and reviewed by LCI.')}
          </AppText>
        </>
      ) : null}
    </DetailScreen>
  );
}

const styles = StyleSheet.create({
  h: { marginTop: SPACING.lg, marginBottom: 8 },
  known: { flexDirection: 'row', alignItems: 'flex-start', gap: 12, backgroundColor: COLORS.paper, borderRadius: RADIUS.md, padding: 12, marginBottom: 8 },
  num: { width: 26, height: 26, borderRadius: 13, backgroundColor: COLORS.navy, alignItems: 'center', justifyContent: 'center' },
  numText: { fontFamily: FONTS.bold, fontSize: 12.5, color: COLORS.yellow },
});
