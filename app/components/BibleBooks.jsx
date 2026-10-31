// The books of the Lango Bible, then the chapters of the one you tap, then the chapter itself. It is used while the text is not stored in the app: on a phone the
// chapter opens in the reader of Bible.com inside the app (app/bible/read.jsx), straight at the text; on the web it opens in its own tab. With both editions set in the website's Site settings there is a switch
// between the full Bible and the New Testament.
import { useState } from 'react';
import { Linking, Platform, View } from 'react-native';
import { router } from 'expo-router';
import AppText from './AppText';
import Chip from './Chip';
import Row from './Row';
import Segmented from './Segmented';
import { COLORS, SPACING } from '../constants/theme';
import { readerUrl } from '../lib/bible';
import { useT } from '../lib/i18n';

export default function BibleBooks({ bible }) {
  const { t } = useT();
  const links = (bible?.links ?? []).filter((l) => l.version);
  const full = links.find((l) => l.key === 'full');
  const nt = links.find((l) => l.key === 'nt');
  const [edition, setEdition] = useState(full ? 'full' : 'nt');
  const [testament, setTestament] = useState(full ? 'old' : 'new');
  const [open, setOpen] = useState(null);

  const link = edition === 'nt' ? nt : full;
  const books = (bible?.books ?? []).filter((b) => b.chapters_total > 0 && b.testament === (edition === 'nt' ? 'new' : testament));
  if (!link) return null;

  return (
    <View>
      {full && nt ? (
        <Segmented
          value={edition}
          onChange={(v) => {
            setEdition(v);
            setOpen(null);
            if (v === 'nt') setTestament('new');
          }}
          options={[
            { value: 'full', label: t('Full Bible') },
            { value: 'nt', label: t('New Testament (La79)') },
          ]}
        />
      ) : null}
      {edition === 'full' ? (
        <View style={{ marginTop: full && nt ? SPACING.sm : 0 }}>
          <Segmented
            value={testament}
            onChange={(v) => {
              setTestament(v);
              setOpen(null);
            }}
            options={[
              { value: 'old', label: t('Old Testament') },
              { value: 'new', label: t('New Testament') },
            ]}
          />
        </View>
      ) : null}
      <AppText variant="caption" style={{ marginVertical: SPACING.sm }}>
        {t('Choose a book, then a chapter. The Bible opens here in Leb Lango.')}
      </AppText>
      {books.map((b) => (
        <View key={b.code}>
          <Row
            icon={open === b.code ? 'book' : 'book-outline'}
            iconTone="red"
            title={b.name}
            meta={t('{n} chapters', { n: b.chapters_total })}
            onPress={() => setOpen(open === b.code ? null : b.code)}
          />
          {open === b.code ? (
            <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 8, paddingHorizontal: 4, paddingBottom: SPACING.md, marginTop: -4 }}>
              {Array.from({ length: b.chapters_total }, (_, i) => i + 1).map((c) => (
                <Chip key={c} label={String(c)} onPress={() => (Platform.OS === 'web' ? Linking.openURL(readerUrl(link.version, b.code, c)).catch(() => {}) : router.push({ pathname: '/bible/read', params: { v: String(link.version), code: b.code, ch: String(c) } }))} />
              ))}
            </View>
          ) : null}
        </View>
      ))}
      <AppText variant="caption" style={{ marginTop: SPACING.md, color: COLORS.gray700 }}>
        {t('The text is shown from the Bible Society of Uganda’s own pages. This app does not copy it, because it belongs to its publisher.')}
      </AppText>
    </View>
  );
}
