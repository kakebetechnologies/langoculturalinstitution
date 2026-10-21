// Clan Explorer (GET /api/v1/clans): search the clans LCI has registered so far, by name or totem. Each clan opens its own page,
// where its leader (Awitong) and photograph are shown.
import { useState } from 'react';
import { StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Pill from '../../components/Pill';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import StateView from '../../components/StateView';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Clans() {
  const { t } = useT();
  const [q, setQ] = useState('');
  const { data, loading, error, retry, reload, refreshing } = useFetch('/clans', { params: { q: q.trim() || undefined, limit: 100 } });
  const rows = data?.data ?? [];

  return (
    <Screen title={t('Clan Explorer')} subtitle={data ? t('{n} of {total} clans on the app so far', { n: data.meta.total, total: data.registered_total }) : undefined} onRefresh={reload} refreshing={refreshing}>
      <View style={styles.search}>
        <Ionicons name="search" size={20} color={COLORS.gray700} />
        <TextInput value={q} onChangeText={setQ} placeholder={t('Search clans by name or totem')} placeholderTextColor={COLORS.gray500} maxFontSizeMultiplier={MAX_FONT_SCALE} style={styles.input} autoCapitalize="none" />
      </View>
      <StateView loading={loading && !data} error={error} onRetry={retry} empty={!rows.length} emptyIcon="people-outline" emptyTitle={q ? 'No clans match' : 'The clan register is being digitised'} emptyMessage={q ? 'No clan matching your search has been added yet.' : 'LCI is documenting all 149 clans. Each clan page will carry its history, totem and leader once verified.'}>
        {rows.map((c) => (
          <Row key={c.id} icon="people-outline" title={c.name} subtitle={c.summary || (c.totem ? `${t('Totem')}: ${c.totem}` : c.district || '')} onPress={() => router.push(`/clans/${c.slug}`)}>
            {c.verified ? <Pill label={t('Verified')} icon="checkmark" tone="sky" style={{ marginTop: 6 }} /> : null}
          </Row>
        ))}
        <AppText variant="caption" style={{ textAlign: 'center', marginTop: 8 }}>
          {t('LCI has {n} registered clans and is adding them to the app as each one is verified.', { n: data?.registered_total ?? 149 })}
        </AppText>
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  search: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1.5, borderColor: COLORS.gray300, paddingHorizontal: 14, marginBottom: 14 },
  input: { flex: 1, minHeight: 48, fontFamily: FONTS.regular, fontSize: 15.5, color: COLORS.black },
});
