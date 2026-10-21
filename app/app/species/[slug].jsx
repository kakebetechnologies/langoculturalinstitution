// One species (GET /api/v1/species/{slug}): conservation status, habitat, traditional uses and description.
import { View } from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import DetailScreen from '../../components/DetailScreen';
import InfoRow from '../../components/InfoRow';
import Pill from '../../components/Pill';
import RichText from '../../components/RichText';
import { SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function SpeciesPage() {
  const { t } = useT();
  const { slug } = useLocalSearchParams();
  const { data, loading, error, retry } = useFetch(`/species/${slug}`);
  const s = data?.data;

  return (
    <DetailScreen loading={loading} error={error} onRetry={retry} hero={s ? { image: s.image, eyebrow: s.scientific_name || t(s.kind), title: s.name_lg ? `${s.name} · ${s.name_lg}` : s.name, fallback: require('../../assets/welcome/field.jpg') } : null}>
      {s ? (
        <>
          <View style={{ flexDirection: 'row', gap: 8, flexWrap: 'wrap', marginBottom: SPACING.md }}>
            <Pill label={t(s.conservation_label)} tone={s.conservation === 'endangered' || s.conservation === 'critical' ? 'red' : 'grey'} />
            {s.medicinal ? <Pill label={t('Traditional medicinal plant')} icon="medkit" tone="green" /> : null}
          </View>
          {s.habitat ? <InfoRow icon="earth-outline" title={t('Habitat')} subtitle={s.habitat} /> : null}
          {s.uses ? <InfoRow icon="flask-outline" title={t('Traditional uses')} subtitle={s.uses} /> : null}
          <AppText style={{ height: 8 }} />
          <RichText html={s.body_html} />
        </>
      ) : null}
    </DetailScreen>
  );
}
