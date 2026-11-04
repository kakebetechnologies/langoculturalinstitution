// Compare words: Lango and Acholi side by side. Type an English word, a Leb Lango word or a Leb Acholi word (GET /api/v1/compare?q=): the website
// finds the English meanings of what was typed and shows, for each meaning, the words of both dictionaries. The two dictionaries are matched through
// English only, so every pair is a likely equivalent and never a checked one, and the screen says so. Works for members of either community.
import { useState } from 'react';
import { Pressable, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import AppText from '../components/AppText';
import Card from '../components/Card';
import Screen from '../components/Screen';
import StateView from '../components/StateView';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS, SHADOW, SPACING } from '../constants/theme';
import { useFetch } from '../lib/useFetch';
import { useT } from '../lib/i18n';

const EXAMPLES = ['water', 'fire', 'mother', 'cattle'];

function Column({ language, words }) {
  const { t } = useT();
  return (
    <View style={styles.col}>
      <AppText style={styles.colHead}>{language}</AppText>
      {words.length ? (
        words.slice(0, 4).map((w, i) => (
          <View key={`${w.headword}-${i}`} style={styles.word}>
            <AppText lango style={styles.headword}>
              {w.headword}
              {w.pos ? <AppText style={styles.pos}> {w.pos}</AppText> : null}
            </AppText>
            <AppText lango style={styles.gloss} numberOfLines={2}>
              {String(w.english).split(';').map((x) => x.trim()).join(' · ')}
              {w.verified ? ` · ${t('checked')}` : ''}
            </AppText>
          </View>
        ))
      ) : (
        <AppText style={styles.none}>{t('No match yet')}</AppText>
      )}
    </View>
  );
}

export default function Compare() {
  const { t } = useT();
  const [draft, setDraft] = useState('');
  const [q, setQ] = useState('');
  const { data, loading, error, retry } = useFetch('/compare', { params: { q }, enabled: q.length > 0, cache: false });
  const result = data?.data;
  const search = (value = draft) => setQ(value.trim());

  return (
    <Screen title={t('Compare words')} subtitle={t('The languages of every community, side by side')}>
      <View style={styles.search}>
        <Ionicons name="search" size={20} color={COLORS.gray700} />
        <TextInput
          value={draft}
          onChangeText={setDraft}
          onSubmitEditing={() => search()}
          returnKeyType="search"
          placeholder={t('An English, Leb Lango or Leb Acholi word')}
          placeholderTextColor={COLORS.gray500}
          maxFontSizeMultiplier={MAX_FONT_SCALE}
          style={styles.input}
          autoCapitalize="none"
        />
        <Pressable onPress={() => search()} accessibilityRole="button" accessibilityLabel={t('Compare')} style={styles.go}>
          <Ionicons name="arrow-forward" size={18} color={COLORS.white} />
        </Pressable>
      </View>

      <View style={styles.note}>
        <AppText style={styles.noteText}>
          <AppText style={{ fontFamily: FONTS.bold }}>{t('Likely equivalents, not checked ones.')} </AppText>
          {t('The two dictionaries are matched through their English meanings. Related languages share words that differ in meaning, so a language expert should confirm a pair before you rely on it.')}
        </AppText>
      </View>

      {!q ? (
        <View style={styles.examples}>
          <AppText variant="caption">{t('Try')}</AppText>
          {EXAMPLES.map((x) => (
            <Pressable key={x} onPress={() => { setDraft(x); search(x); }} style={styles.chip} accessibilityRole="button">
              <AppText style={styles.chipText}>{x}</AppText>
            </Pressable>
          ))}
        </View>
      ) : (
        <StateView loading={loading} error={error} onRetry={retry} empty={!result?.groups?.length} emptyTitle={t('Nothing found')} emptyMessage={t('Try an English word such as water.')}>
          {(result?.groups ?? []).map((g) => (
            <Card key={g.english} style={{ marginTop: SPACING.md }}>
              <AppText variant="subheading" style={{ marginBottom: 8 }}>
                {g.english}
              </AppText>
              <View style={styles.cols}>
                {(result.communities ?? []).map((c) => (
                  <Column key={c.code} language={c.language} words={g[c.code] ?? []} />
                ))}
              </View>
            </Card>
          ))}
          {result && Object.values(result.available ?? {}).some((n) => !n) ? <AppText variant="caption" style={{ marginTop: SPACING.md }}>{t('A community whose dictionary is not published yet shows no words.')}</AppText> : null}
        </StateView>
      )}
    </Screen>
  );
}

const styles = StyleSheet.create({
  search: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.full, paddingLeft: 18, paddingRight: 6, ...SHADOW, shadowOpacity: 0.1 },
  input: { flex: 1, minHeight: 52, fontFamily: 'Inter_400Regular', fontSize: 16, color: COLORS.black },
  go: { width: 42, height: 42, borderRadius: 21, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  note: { backgroundColor: COLORS.yellowMist, borderRadius: RADIUS.md, padding: 12, marginTop: SPACING.md },
  noteText: { fontFamily: FONTS.regular, fontSize: 13.5, lineHeight: 19, color: COLORS.yellowInk },
  examples: { flexDirection: 'row', flexWrap: 'wrap', alignItems: 'center', gap: 8, marginTop: SPACING.lg },
  chip: { backgroundColor: COLORS.white, borderRadius: RADIUS.full, paddingVertical: 8, paddingHorizontal: 16, ...SHADOW, shadowOpacity: 0.08 },
  chipText: { fontFamily: FONTS.semibold, fontSize: 14.5, color: COLORS.black },
  cols: { flexDirection: 'row', flexWrap: 'wrap', gap: 14 },
  col: { flex: 1, minWidth: 130 },
  colHead: { fontFamily: FONTS.bold, fontSize: 11, letterSpacing: 1.2, textTransform: 'uppercase', color: COLORS.primaryDark, marginBottom: 6 },
  word: { paddingVertical: 6, borderTopWidth: StyleSheet.hairlineWidth, borderTopColor: COLORS.gray200 ?? '#E3E5EA' },
  headword: { fontFamily: 'Inter_700Bold', fontSize: 17, color: COLORS.black },
  pos: { fontFamily: 'Inter_400Regular', fontSize: 12, color: COLORS.gray700 },
  gloss: { fontFamily: 'Inter_400Regular', fontSize: 13.5, lineHeight: 19, color: COLORS.gray900 },
  none: { fontFamily: FONTS.regular, fontSize: 13.5, color: COLORS.gray700 },
});
