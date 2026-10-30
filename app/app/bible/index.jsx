// Lango Bible, the books (GET /api/v1/bible): Old and New Testament, a search across the verses, and "continue reading" where the
// member stopped last. Only books that have text are listed; the text is added in the admin panel by LCI, so until then the
// screen says the Bible is being prepared. Nothing here is written by the app.
import { useEffect, useState } from 'react';
import { StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import Segmented from '../../components/Segmented';
import StateView from '../../components/StateView';
import TextField from '../../components/TextField';
import { COLORS, SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { getStored } from '../../lib/storage';
import { useFetch } from '../../lib/useFetch';

function Results({ term }) {
  const { t } = useT();
  const { data, loading, error, retry } = useFetch('/bible/search', { params: { q: term }, cache: false });
  const rows = data?.data ?? [];

  return (
    <StateView loading={loading} error={error} onRetry={retry} empty={!rows.length} emptyIcon="search-outline" emptyTitle="No verse found" emptyMessage="Try another word, or check the spelling.">
      {rows.map((r) => (
        <Row
          key={`${r.book_id}:${r.chapter}:${r.verse}`}
          icon="book-outline"
          iconTone="red"
          title={`${r.book} ${r.chapter}:${r.verse}`}
          subtitle={r.text}
          numberOfLines={1}
          onPress={() => router.push({ pathname: `/bible/${r.book_id}/${r.chapter}`, params: { verse: String(r.verse) } })}
        />
      ))}
      {data?.more ? (
        <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.sm }}>
          {t('Showing the first 40 verses. Type more of the phrase to narrow it down.')}
        </AppText>
      ) : null}
    </StateView>
  );
}

export default function Bible() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/bible');
  const bible = data?.data;
  const [testament, setTestament] = useState('old');
  const [q, setQ] = useState('');
  const [term, setTerm] = useState('');
  const [last, setLast] = useState(null);

  useEffect(() => {
    getStored('bible_last', null).then(setLast);
  }, []);
  useEffect(() => {
    const id = setTimeout(() => setTerm(q.trim()), 400);
    return () => clearTimeout(id);
  }, [q]);

  const books = (bible?.books ?? []).filter((b) => b.verses > 0);
  const shown = books.filter((b) => b.testament === testament);
  const missing = (bible?.books ?? []).length - books.length;

  return (
    <Screen title={bible?.title || t('Lango Bible')} onRefresh={reload} refreshing={refreshing}>
      <StateView
        loading={loading}
        error={error}
        onRetry={retry}
        empty={!!bible && !bible.ready}
        emptyIcon="book-outline"
        emptyTitle="The Lango Bible is being prepared"
        emptyMessage="The text will appear here once LCI has added it with the permission of those who hold the translation."
      >
        {bible ? (
          <>
            <TextField value={q} onChangeText={setQ} placeholder={t('Search the Bible')} autoCapitalize="none" returnKeyType="search" />
            {term.length >= 2 ? (
              <Results term={term} />
            ) : (
              <>
                {last ? (
                  <Row icon="bookmark" iconTone="gold" title={t('Continue reading')} subtitle={`${last.name} ${last.chapter}`} onPress={() => router.push(`/bible/${last.book_id}/${last.chapter}`)} />
                ) : null}
                <View style={{ marginVertical: SPACING.sm }}>
                  <Segmented value={testament} onChange={setTestament} options={[{ value: 'old', label: t('Old Testament') }, { value: 'new', label: t('New Testament') }]} />
                </View>
                {shown.length ? (
                  shown.map((b) => <Row key={b.id} icon="book-outline" title={b.name} subtitle={[b.name_lg ? b.name_en : null, t('{n} chapters', { n: b.chapters })].filter(Boolean).join(' · ')} onPress={() => router.push(`/bible/${b.id}`)} />)
                ) : (
                  <View style={styles.none}>
                    <Ionicons name="book-outline" size={26} color={COLORS.gray500} />
                    <AppText variant="caption" style={{ textAlign: 'center' }}>
                      {t('No books of this part are available yet.')}
                    </AppText>
                  </View>
                )}
                {missing > 0 ? (
                  <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.md }}>
                    {t('More books are added as LCI completes them.')}
                  </AppText>
                ) : null}
              </>
            )}
          </>
        ) : null}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  none: { alignItems: 'center', gap: 8, padding: SPACING.xl },
});
