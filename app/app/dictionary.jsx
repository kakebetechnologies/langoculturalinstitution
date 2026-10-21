// The dictionary: search in Leb Lango or English (words typed on an ordinary keyboard are found too: e for ε, o for ɔ, ng for ŋ),
// browse A–Z, and read each entry with its meanings, topic, example and a "Checked" / "Community" tag. Works from the website
// when online; with no internet it searches the copy downloaded in Settings. Unknown word? Suggest it from the empty state.
import { useEffect, useRef, useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Pill from '../components/Pill';
import Screen from '../components/Screen';
import Segmented from '../components/Segmented';
import StateView from '../components/StateView';
import WordCard from '../components/WordCard';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS, SPACING } from '../constants/theme';
import api, { errorMessage } from '../lib/api';
import { browseOffline, searchOffline, useOfflineDictionary } from '../lib/dictionary';
import { useT } from '../lib/i18n';
import { isOffline } from '../lib/net';

const LETTERS = 'abcdefghijklmnopqrstuvwxyz'.split('');

export default function Dictionary() {
  const { t } = useT();
  const params = useLocalSearchParams();
  const offlineDict = useOfflineDictionary();
  const [q, setQ] = useState(typeof params.q === 'string' ? params.q : '');
  const [dir, setDir] = useState('all');
  const [letter, setLetter] = useState(typeof params.q === 'string' && params.q ? '' : 'a');
  const [s, setS] = useState({ items: [], page: 1, more: false, loading: true, error: null, offline: false, total: 0, busy: false });
  const latest = useRef(0);

  async function load(page = 1) {
    const id = ++latest.current;
    const query = q.trim();
    if (page === 1) setS((x) => ({ ...x, loading: true, error: null }));
    else setS((x) => ({ ...x, busy: true }));
    try {
      const { data } = await api.get('/dictionary', { params: { q: query || undefined, dir, letter: query ? undefined : letter, page, limit: 30 }, timeout: 12000 });
      if (id !== latest.current) return;
      setS((x) => ({ items: page === 1 ? data.data : [...x.items, ...data.data], page, more: data.meta.has_more, total: data.meta.total, loading: false, busy: false, error: null, offline: false }));
    } catch (e) {
      if (id !== latest.current) return;
      if (isOffline(e) && offlineDict.ready) {
        const items = query ? searchOffline(query, dir) : browseOffline(letter);
        setS({ items, page: 1, more: false, total: items.length, loading: false, busy: false, error: null, offline: true });
      } else setS((x) => ({ ...x, loading: false, busy: false, error: errorMessage(e) }));
    }
  }

  // Search as you type (after a short pause), and whenever the direction or letter changes.
  useEffect(() => {
    const timer = setTimeout(() => load(1), q ? 350 : 0);
    return () => clearTimeout(timer);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [q, dir, letter, offlineDict.ready]);

  const header = (
    <View>
      <View style={styles.search}>
        <Ionicons name="search" size={20} color={COLORS.gray700} />
        <TextInput value={q} onChangeText={(v) => { setQ(v); if (v) setLetter(''); else setLetter('a'); }} placeholder={t('Search a word, e.g. yoo, dano, water')} placeholderTextColor={COLORS.gray500} maxFontSizeMultiplier={MAX_FONT_SCALE} style={styles.input} autoCapitalize="none" autoCorrect={false} returnKeyType="search" />
        {q ? (
          <Pressable onPress={() => { setQ(''); setLetter('a'); }} hitSlop={10} accessibilityRole="button" accessibilityLabel={t('Clear')}>
            <Ionicons name="close-circle" size={20} color={COLORS.gray500} />
          </Pressable>
        ) : null}
      </View>
      <Segmented value={dir} onChange={setDir} options={[{ value: 'all', label: t('Both') }, { value: 'lg', label: 'Leb Lango' }, { value: 'en', label: 'English' }]} style={{ marginTop: 10 }} />
      {!q ? (
        <FlatList
          data={LETTERS}
          horizontal
          showsHorizontalScrollIndicator={false}
          keyExtractor={(l) => l}
          style={{ marginTop: 10 }}
          contentContainerStyle={{ gap: 6 }}
          renderItem={({ item }) => (
            <Pressable onPress={() => setLetter(item)} accessibilityRole="button" style={[styles.letter, letter === item && styles.letterOn]}>
              <AppText style={[styles.letterText, letter === item && { color: COLORS.white }]}>{item.toUpperCase()}</AppText>
            </Pressable>
          )}
        />
      ) : null}
      <View style={styles.status}>
        {s.offline ? <Pill label={t('Offline dictionary')} icon="cloud-offline" tone="gold" /> : null}
        <AppText variant="caption" style={{ flex: 1 }}>
          {s.loading ? t('Searching…') : q ? t('{n} results for “{q}”', { n: s.total.toLocaleString('en-US'), q: q.trim() }) : t('{n} words beginning with {l}', { n: s.total.toLocaleString('en-US'), l: letter.toUpperCase() })}
        </AppText>
      </View>
    </View>
  );

  return (
    <Screen title={t('Dictionary')} scroll={false}>
      <FlatList
        data={s.error || s.loading ? [] : s.items}
        keyExtractor={(w) => String(w.id)}
        renderItem={({ item }) => <WordCard word={item} />}
        ListHeaderComponent={header}
        keyboardShouldPersistTaps="handled"
        onEndReachedThreshold={0.4}
        onEndReached={() => s.more && !s.busy && load(s.page + 1)}
        contentContainerStyle={{ paddingBottom: SPACING.xxl }}
        ListEmptyComponent={
          <StateView
            loading={s.loading}
            error={s.error}
            onRetry={() => load(1)}
            empty
            emptyIcon="book-outline"
            emptyTitle="No entry found"
            emptyMessage="This word may not be in the dictionary yet. Try the translator, or suggest it and our language experts will review it."
          >
            <View />
          </StateView>
        }
        ListFooterComponent={
          s.busy ? (
            <ActivityIndicator color={COLORS.primary} style={{ margin: 16 }} />
          ) : !s.loading && !s.error && q.trim() && !s.items.length ? (
            <View style={{ gap: 10, alignItems: 'center' }}>
              <Button title={t('Suggest this word')} icon="add-circle-outline" onPress={() => router.push({ pathname: '/contribute/new', params: { type: 'language', kind: 'word', word: q.trim() } })} />
              <Button title={t('Try the translator')} variant="outline" onPress={() => router.push({ pathname: '/translate', params: { text: q.trim(), dir: 'lango-en' } })} />
            </View>
          ) : s.items.length ? (
            <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.md }}>
              {t('“Checked” means an LCI language expert has confirmed the entry. Unmarked entries come from the original Lango dictionary project and are still being reviewed.')}
            </AppText>
          ) : null
        }
      />
    </Screen>
  );
}

const styles = StyleSheet.create({
  search: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1.5, borderColor: COLORS.gray300, paddingHorizontal: 14 },
  input: { flex: 1, minHeight: 50, fontFamily: FONTS.regular, fontSize: 16, color: COLORS.black },
  letter: { width: 36, height: 36, borderRadius: 8, borderWidth: 1.5, borderColor: COLORS.line, backgroundColor: COLORS.white, alignItems: 'center', justifyContent: 'center' },
  letterOn: { backgroundColor: COLORS.navy, borderColor: COLORS.navy },
  letterText: { fontFamily: FONTS.bold, fontSize: 13, color: COLORS.black },
  status: { flexDirection: 'row', alignItems: 'center', gap: 8, marginVertical: SPACING.md },
});
