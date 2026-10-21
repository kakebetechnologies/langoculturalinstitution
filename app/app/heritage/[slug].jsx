// One heritage item (GET /api/v1/heritage/{slug}): its story, where it is and a link to watch or listen when there is media.
import { Linking, View } from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import DetailScreen from '../../components/DetailScreen';
import InfoRow from '../../components/InfoRow';
import Pill from '../../components/Pill';
import RichText from '../../components/RichText';
import { SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function HeritageItem() {
  const { t } = useT();
  const { slug } = useLocalSearchParams();
  const { data, loading, error, retry } = useFetch(`/heritage/${slug}`);
  const h = data?.data;

  return (
    <DetailScreen loading={loading} error={error} onRetry={retry} hero={h ? { image: h.image, eyebrow: t(h.category_label), title: h.title, subtitle: h.summary, fallback: require('../../assets/welcome/field.jpg') } : null}>
      {h ? (
        <>
          <View style={{ flexDirection: 'row', gap: 8, flexWrap: 'wrap', marginBottom: SPACING.md }}>
            {h.district ? <Pill label={h.district} icon="location" tone="sky" /> : null}
            {h.verified ? <Pill label={t('Verified')} icon="checkmark" tone="green" /> : null}
          </View>
          <RichText html={h.body_html} />
          {h.video_url ? <InfoRow icon="play-circle-outline" title={t('Watch the video')} onPress={() => Linking.openURL(h.video_url).catch(() => {})} /> : null}
          {h.audio ? <InfoRow icon="musical-notes-outline" title={t('Listen to the recording')} onPress={() => Linking.openURL(h.audio).catch(() => {})} /> : null}
          {h.lat ? <InfoRow icon="map-outline" title={t('Open in Maps')} subtitle={`${h.lat.toFixed(3)}, ${h.lng.toFixed(3)}`} onPress={() => Linking.openURL(`https://www.openstreetmap.org/?mlat=${h.lat}&mlon=${h.lng}#map=14/${h.lat}/${h.lng}`).catch(() => {})} /> : null}
        </>
      ) : null}
    </DetailScreen>
  );
}
