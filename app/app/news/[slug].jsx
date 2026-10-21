// One news article (GET /api/v1/news/{slug}): cover, title, date and author, the article, and a share button.
import { Share, View } from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import DetailScreen from '../../components/DetailScreen';
import RichText from '../../components/RichText';
import { SPACING } from '../../constants/theme';
import { fmtDate } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Article() {
  const { t } = useT();
  const { slug } = useLocalSearchParams();
  const { data, loading, error, retry } = useFetch(`/news/${slug}`);
  const n = data?.data;

  return (
    <DetailScreen loading={loading} error={error} onRetry={retry} hero={n ? { image: n.image, eyebrow: n.category || t('News'), title: n.title, subtitle: [fmtDate(n.published_at), n.author].filter(Boolean).join(' · '), fallback: require('../../assets/welcome/hill.jpg') } : null}>
      {n ? (
        <>
          <RichText html={n.body_html} />
          <View style={{ marginTop: SPACING.lg }}>
            <Button small variant="outline" icon="share-outline" title={t('Share')} onPress={() => Share.share({ message: `${n.title} — Lango Cultural Institution` }).catch(() => {})} style={{ alignSelf: 'flex-start' }} />
          </View>
          <AppText variant="caption" style={{ marginTop: SPACING.md }}>
            {t('From the Lango Cultural Institution.')}
          </AppText>
        </>
      ) : null}
    </DetailScreen>
  );
}
