// Reading one chapter of the Lango Bible (GET /api/v1/bible/{book}/{chapter}): the verses, a text size the reader sets, previous and
// next chapter, and a share button for the verses the reader taps. Opened from a search result it scrolls to and marks that verse.
// The place is remembered ("Continue reading" on the list of books). The copyright line LCI entered is shown under the chapter.
import { useEffect, useRef, useState } from 'react';
import { Pressable, ScrollView, Share, StyleSheet, View } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../../components/AppText';
import Button from '../../../components/Button';
import Screen from '../../../components/Screen';
import StateView from '../../../components/StateView';
import { COLORS, FONTS, RADIUS, SPACING } from '../../../constants/theme';
import { useT } from '../../../lib/i18n';
import { getStored, setStored } from '../../../lib/storage';
import { useFetch } from '../../../lib/useFetch';

/** [1,2,3,5] -> "1-3, 5" */
function range(nums) {
  const out = [];
  [...nums].sort((a, b) => a - b).forEach((n) => {
    const last = out[out.length - 1];
    if (last && n === last[1] + 1) last[1] = n;
    else out.push([n, n]);
  });
  return out.map(([a, b]) => (a === b ? `${a}` : `${a}-${b}`)).join(', ');
}

function SizeButton({ label, hint, onPress }) {
  return (
    <Pressable onPress={onPress} hitSlop={6} accessibilityRole="button" accessibilityLabel={hint} style={styles.size}>
      <AppText style={styles.sizeText}>{label}</AppText>
    </Pressable>
  );
}

export default function Chapter() {
  const { t } = useT();
  const { book, chapter, verse } = useLocalSearchParams();
  const { data, loading, error, retry } = useFetch(`/bible/${book}/${chapter}`);
  const d = data?.data;
  const scroll = useRef(null);
  const target = Number(verse) || 0;
  const scrolled = useRef(false);
  const [size, setSize] = useState(18);
  const [picked, setPicked] = useState([]);

  useEffect(() => {
    getStored('bible_size', 18).then((s) => setSize(Number(s) || 18));
  }, []);
  useEffect(() => {
    if (d) setStored('bible_last', { book_id: d.book.id, chapter: d.chapter, name: d.book.name });
  }, [d]);

  const resize = (delta) => {
    const next = Math.min(28, Math.max(14, size + delta));
    setSize(next);
    setStored('bible_size', next);
  };
  const toggle = (n) => setPicked((p) => (p.includes(n) ? p.filter((x) => x !== n) : [...p, n]));
  const share = () => {
    const chosen = d.verses.filter((v) => picked.includes(v.n));
    Share.share({ message: `“${chosen.map((v) => v.text).join(' ')}”\n— ${d.book.name} ${d.chapter}:${range(picked)} (${d.title})` }).catch(() => {});
  };
  const go = (to) => router.replace(`/bible/${to.book_id}/${to.chapter}`);

  return (
    <Screen
      title={d ? `${d.book.name} ${d.chapter}` : ''}
      scroll={false}
      padded={false}
      right={
        <View style={{ flexDirection: 'row', gap: 8 }}>
          <SizeButton label="A−" hint={t('Smaller text')} onPress={() => resize(-2)} />
          <SizeButton label="A+" hint={t('Larger text')} onPress={() => resize(2)} />
        </View>
      }
      footer={
        picked.length ? (
          <View style={styles.bar}>
            <AppText style={{ flex: 1 }}>{t('{n} selected', { n: picked.length })}</AppText>
            <Button small variant="outline" title={t('Clear')} onPress={() => setPicked([])} />
            <Button small icon="share-outline" title={t('Share')} onPress={share} />
          </View>
        ) : null
      }
    >
      <StateView loading={loading} error={error} onRetry={retry}>
        {d ? (
          <ScrollView ref={scroll} contentContainerStyle={styles.body}>
            {d.verses.map((v) => (
              <Pressable
                key={v.n}
                onPress={() => toggle(v.n)}
                onLayout={(e) => {
                  if (v.n === target && !scrolled.current) {
                    scrolled.current = true;
                    scroll.current?.scrollTo({ y: Math.max(0, e.nativeEvent.layout.y - 90), animated: false });
                  }
                }}
                accessibilityRole="button"
                accessibilityState={{ selected: picked.includes(v.n) }}
                style={[styles.verse, v.n === target && styles.target, picked.includes(v.n) && styles.picked]}
              >
                <AppText style={styles.num}>{v.n}</AppText>
                <AppText lango style={{ flex: 1, fontSize: size, lineHeight: Math.round(size * 1.55), color: COLORS.ink }}>
                  {v.text}
                </AppText>
              </Pressable>
            ))}

            <View style={styles.nav}>
              {d.prev ? <Button small variant="outline" icon="chevron-back" title={t('Previous chapter')} onPress={() => go(d.prev)} /> : <View />}
              {d.next ? <Button small icon="chevron-forward" title={t('Next chapter')} onPress={() => go(d.next)} /> : null}
            </View>
            {d.copyright ? (
              <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.lg }}>
                {d.copyright}
              </AppText>
            ) : null}
          </ScrollView>
        ) : null}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  body: { paddingHorizontal: SPACING.md, paddingBottom: SPACING.xxl },
  verse: { flexDirection: 'row', gap: 10, paddingVertical: 6, paddingHorizontal: 8, borderRadius: RADIUS.md },
  target: { backgroundColor: COLORS.yellowMist },
  picked: { backgroundColor: COLORS.primaryMist },
  num: { fontFamily: FONTS.bold, fontSize: 12, color: COLORS.primaryDark, width: 24, paddingTop: 6, textAlign: 'right' },
  nav: { flexDirection: 'row', justifyContent: 'space-between', marginTop: SPACING.lg },
  bar: { flexDirection: 'row', alignItems: 'center', gap: 10, padding: SPACING.md, backgroundColor: COLORS.white, borderTopWidth: 1, borderTopColor: COLORS.line },
  size: { width: 38, height: 38, borderRadius: 19, backgroundColor: COLORS.white, borderWidth: 1, borderColor: COLORS.gray300, alignItems: 'center', justifyContent: 'center' },
  sizeText: { fontFamily: FONTS.bold, fontSize: 13, color: COLORS.black },
});
