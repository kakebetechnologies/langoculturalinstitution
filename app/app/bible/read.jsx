// Reads one chapter of the Lango Bible inside the app: the reader of Bible.com opens straight at the chapter (no page that offers to download the app), with
// the book and chapter in the title and Previous / All books / Next underneath. The books come from GET /api/v1/bible; nothing of the text is copied.
import { StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import BibleFrame from '../../components/BibleFrame';
import Button from '../../components/Button';
import ScreenHeader from '../../components/ScreenHeader';
import { COLORS, SPACING } from '../../constants/theme';
import { readerUrl } from '../../lib/bible';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function BibleRead() {
  const { t } = useT();
  const { v, code, ch } = useLocalSearchParams();
  const version = Number(v);
  const chapter = Math.max(1, Number(ch) || 1);
  const { data } = useFetch('/bible');
  const bible = data?.data;
  const books = (bible?.books ?? []).filter((b) => b.chapters_total > 0);
  const nt = bible?.links?.find((l) => l.key === 'nt');
  const full = bible?.links?.find((l) => l.key === 'full');
  const ntOnly = nt && nt.version === version && (!full || full.version !== version);
  const list = ntOnly ? books.filter((b) => b.testament === 'new') : books;
  const at = list.findIndex((b) => b.code === String(code).toUpperCase());
  const book = at >= 0 ? list[at] : null;
  const title = book ? `${book.name} ${chapter}` : t('Lango Bible');

  const prev = !book ? null : chapter > 1 ? [book, chapter - 1] : at > 0 ? [list[at - 1], list[at - 1].chapters_total] : null;
  const next = !book ? null : chapter < book.chapters_total ? [book, chapter + 1] : at < list.length - 1 ? [list[at + 1], 1] : null;
  const go = (t2) => router.replace({ pathname: '/bible/read', params: { v: String(version), code: t2[0].code, ch: String(t2[1]) } });

  return (
    <View style={styles.screen}>
      <ScreenHeader title={title} />
      {version ? (
        <BibleFrame url={readerUrl(version, code, chapter)} title={title} />
      ) : (
        <View style={styles.none}>
          <Ionicons name="book-outline" size={28} color={COLORS.gray500} />
          <AppText variant="caption">{t('Choose a book and a chapter first.')}</AppText>
          <Button small title={t('All books')} onPress={() => router.replace('/bible')} />
        </View>
      )}
      <View style={styles.bar}>
        <Button small variant="outline" icon="chevron-back" title={t('Previous')} disabled={!prev} onPress={() => prev && go(prev)} style={{ flex: 1 }} />
        <Button small variant="dark" icon="book-outline" title={t('Books')} onPress={() => router.replace('/bible')} style={{ flex: 1 }} />
        <Button small icon="chevron-forward" title={t('Next')} disabled={!next} onPress={() => next && go(next)} style={{ flex: 1 }} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  none: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: 10, padding: SPACING.xl },
  bar: { flexDirection: 'row', gap: 8, padding: SPACING.sm, paddingBottom: SPACING.md, backgroundColor: COLORS.white, borderTopWidth: StyleSheet.hairlineWidth, borderTopColor: COLORS.gray300 },
});
