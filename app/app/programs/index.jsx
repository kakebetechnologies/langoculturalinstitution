// Programs (GET /api/v1/programs): cultural preservation, education, youth, conservation, development and digital innovation,
// each with its progress and a way to sponsor it.
import { useState } from 'react';
import { ScrollView, StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import Chip from '../../components/Chip';
import Pill from '../../components/Pill';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import { COLORS } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Programs() {
  const { t } = useT();
  const [cat, setCat] = useState('');
  const { data, loading, error, retry, reload, refreshing } = useFetch('/programs');
  const all = data?.data ?? [];
  const rows = cat ? all.filter((p) => p.category === cat) : all;
  const cats = [...new Set(all.map((p) => p.category))];

  return (
    <Screen title={t('Programs')} onRefresh={reload} refreshing={refreshing}>
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!all.length} emptyIcon="leaf-outline" emptyTitle="No programs yet">
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 8, paddingBottom: 12 }}>
          <Chip label={t('All')} on={cat === ''} onPress={() => setCat('')} />
          {cats.map((c) => (
            <Chip key={c} label={t(data.categories[c] ?? c)} on={cat === c} onPress={() => setCat(c)} />
          ))}
        </ScrollView>
        {rows.map((p) => (
          <Row key={p.id} image={p.image} icon={p.image ? undefined : 'leaf-outline'} title={p.title} subtitle={p.summary} onPress={() => router.push(`/programs/${p.slug}`)}>
            <View style={styles.bar}>
              <View style={[styles.fill, { width: `${p.progress}%` }]} />
            </View>
            <Pill label={t(p.category_label)} tone="grey" style={{ marginTop: 6 }} />
          </Row>
        ))}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  bar: { height: 5, backgroundColor: COLORS.gray100, borderRadius: 3, overflow: 'hidden', marginTop: 8 },
  fill: { height: 5, backgroundColor: COLORS.yellow },
});
