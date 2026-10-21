// Notable Langos (GET /api/v1/people): statesmen, soldiers, champions, chiefs and public servants whose stories young people should
// know. Filter by group; each person opens a full story. Portraits appear once properly licensed photographs are added.
import { useState } from 'react';
import { ScrollView, View } from 'react-native';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Chip from '../../components/Chip';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import { SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function People() {
  const { t } = useT();
  const [cat, setCat] = useState('');
  const { data, loading, error, retry, reload, refreshing } = useFetch('/people');
  const all = data?.data ?? [];
  const cats = [...new Set(all.map((p) => p.category))];
  const rows = cat ? all.filter((p) => p.category === cat) : all;

  return (
    <Screen title={t('Notable Langos')} onRefresh={reload} refreshing={refreshing}>
      <AppText style={{ marginBottom: SPACING.md }}>{t('Statesmen, soldiers, champions, chiefs and public servants — the stories every young Lango should know.')}</AppText>
      <StateView loading={loading} error={error} onRetry={retry} empty={!all.length} emptyIcon="ribbon-outline" emptyTitle="No stories yet">
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 8, paddingBottom: 12 }}>
          <Chip label={t('Everyone')} on={cat === ''} onPress={() => setCat('')} />
          {cats.map((c) => (
            <Chip key={c} label={t(data.categories[c] ?? c)} on={cat === c} onPress={() => setCat(c)} />
          ))}
        </ScrollView>
        <View>
          {rows.map((p) => (
            <Row key={p.id} avatar={{ name: p.short_name, photo: p.photo }} title={p.short_name} subtitle={p.title} meta={p.born ? `${p.born}${p.died ? ' – ' + p.died : ''}` : undefined} onPress={() => router.push(`/people/${p.slug}`)} />
          ))}
        </View>
      </StateView>
    </Screen>
  );
}
