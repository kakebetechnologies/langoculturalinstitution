// A clan's archive: everything its members contributed that LCI reviewers approved (words, proverbs, oral histories, photographs…).
// A name is only shown when the contributor asked to be credited. Shortcuts add to the archive: a word, a proverb, a photo.
import { RefreshControl, ScrollView, StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import AppText from '../AppText';
import Button from '../Button';
import Pill from '../Pill';
import Row from '../Row';
import StateView from '../StateView';
import { COLORS, SPACING } from '../../constants/theme';
import { fmtDate } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function ClanArchive({ clanId, clanName }) {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch(`/clans/${clanId}/archive`, { params: { limit: 50 }, cache: false });
  const rows = data?.data ?? [];

  return (
    <ScrollView contentContainerStyle={{ padding: SPACING.md, paddingBottom: SPACING.xxl }} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={reload} tintColor={COLORS.primary} colors={[COLORS.primary]} />}>
      <AppText variant="heading">{t('Add to the {clan} archive', { clan: clanName })}</AppText>
      <AppText variant="caption" style={{ marginTop: 2, marginBottom: SPACING.sm }}>
        {t('A word or proverb with its meaning, or a photograph. Reviewers check it, then it joins the archive.')}
      </AppText>
      <View style={styles.actions}>
        <Button small title={t('Suggest a word')} icon="chatbubbles-outline" onPress={() => router.push({ pathname: '/contribute/new', params: { type: 'language', kind: 'word' } })} />
        <Button small title={t('Add a proverb')} icon="ribbon-outline" variant="dark" onPress={() => router.push({ pathname: '/contribute/new', params: { type: 'language', kind: 'proverb' } })} />
        <Button small title={t('Take a photo')} icon="camera-outline" variant="outline" onPress={() => router.push('/(tabs)/contribute')} />
      </View>

      <AppText variant="heading" style={{ marginTop: SPACING.lg, marginBottom: SPACING.sm }}>
        {t('Approved by LCI')}
      </AppText>
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!rows.length} emptyIcon="images-outline" emptyTitle="Nothing in the archive yet" emptyMessage="When LCI approves what members of this clan contribute, it appears here.">
        {rows.map((r) => (
          <Row key={r.id} image={r.image} icon={r.image ? undefined : r.type === 'language' ? 'chatbubbles-outline' : 'document-text-outline'} iconTone="gold" title={r.title} subtitle={r.excerpt}>
            <View style={styles.meta}>
              <Pill label={t(r.type_label)} tone="sky" />
              <AppText variant="caption">{[r.shared_by, fmtDate(r.created_at)].filter(Boolean).join(' · ')}</AppText>
            </View>
          </Row>
        ))}
      </StateView>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  actions: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  meta: { flexDirection: 'row', alignItems: 'center', gap: 8, marginTop: 6, flexWrap: 'wrap' },
});
