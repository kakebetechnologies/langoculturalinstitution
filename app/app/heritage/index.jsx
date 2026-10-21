// Heritage (GET /api/v1/heritage): ceremonies, attire, crafts, music, dance, storytelling, symbols and sites — filter by category and search.
import { useState } from 'react';
import { ScrollView, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import Chip from '../../components/Chip';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Heritage() {
  const { t } = useT();
  const [cat, setCat] = useState('');
  const [q, setQ] = useState('');
  const { data, loading, error, retry, reload, refreshing } = useFetch('/heritage', { params: { category: cat || undefined, q: q.trim() || undefined, limit: 100 } });
  const rows = data?.data ?? [];

  return (
    <Screen title={t('Heritage')} onRefresh={reload} refreshing={refreshing}>
      <View style={styles.search}>
        <Ionicons name="search" size={20} color={COLORS.gray700} />
        <TextInput value={q} onChangeText={setQ} placeholder={t('Search ceremonies, dances, sites…')} placeholderTextColor={COLORS.gray500} maxFontSizeMultiplier={MAX_FONT_SCALE} style={styles.input} autoCapitalize="none" />
      </View>
      <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ gap: 8, paddingBottom: 12 }}>
        <Chip label={t('All')} on={cat === ''} onPress={() => setCat('')} />
        {Object.entries(data?.categories ?? {}).map(([k, label]) => (
          <Chip key={k} label={t(label)} on={cat === k} onPress={() => setCat(k)} />
        ))}
      </ScrollView>
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!rows.length} emptyIcon="color-palette-outline" emptyTitle="Nothing here yet" emptyMessage="Heritage stories are added as they are verified.">
        {rows.map((h) => (
          <Row key={h.id} image={h.image} icon={h.image ? undefined : 'color-palette-outline'} title={h.title} subtitle={h.summary} meta={t(h.category_label)} onPress={() => router.push(`/heritage/${h.slug}`)} />
        ))}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  search: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1.5, borderColor: COLORS.gray300, paddingHorizontal: 14, marginBottom: 12 },
  input: { flex: 1, minHeight: 48, fontFamily: FONTS.regular, fontSize: 15.5, color: COLORS.black },
});
