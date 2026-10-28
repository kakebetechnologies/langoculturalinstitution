// One dictionary entry: the Leb Lango word big in serif, its part of speech and topic, the English meanings separated by dots,
// an example sentence when there is one, and a "Checked" / "Community" tag (offline entries say so quietly).
import { StyleSheet, View } from 'react-native';
import { COLORS, FONTS, RADIUS, SPACING, SHADOW } from '../constants/theme';
import { useT } from '../lib/i18n';
import AppText from './AppText';
import Pill from './Pill';

const CATS = { animal: 'Animals', bird: 'Birds', plant: 'Plants & trees', place: 'Places', name: 'Personal names', food: 'Food & drink', body: 'Body & health', family: 'Family & kinship', nature: 'Nature & weather', object: 'Tools & household', culture: 'Culture & ceremony', verb: 'Actions', other: 'Other' };

export default function WordCard({ word }) {
  const { t } = useT();
  const senses = String(word.english).split(';').map((s) => s.trim()).filter(Boolean);

  return (
    <View style={styles.card}>
      <View style={styles.top}>
        <AppText style={styles.word} selectable>
          {word.lango}
        </AppText>
        {word.pos ? <AppText style={styles.pos}>{word.pos}</AppText> : null}
        <View style={{ flex: 1 }} />
        {word.community ? <Pill label={t('Community')} icon="people" tone="gold" /> : word.verified ? <Pill label={t('Checked')} icon="checkmark" tone="sky" /> : null}
      </View>
      <AppText style={styles.meaning} selectable>
        {senses.join('  ·  ')}
      </AppText>
      {word.category && CATS[word.category] ? <Pill label={t(CATS[word.category])} tone="grey" style={{ marginTop: 8 }} /> : null}
      {word.example_lg ? (
        <AppText variant="caption" style={{ marginTop: 8, fontStyle: 'italic' }}>
          {word.example_lg}
          {word.example_en ? ` — ${word.example_en}` : ''}
        </AppText>
      ) : null}
      {word.contributor ? (
        <AppText variant="caption" style={{ marginTop: 6 }}>
          {t('Suggested by {name}', { name: word.contributor })}
        </AppText>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg, ...SHADOW, borderLeftWidth: 4, borderLeftColor: COLORS.yellow, padding: SPACING.md, marginBottom: 10 },
  top: { flexDirection: 'row', alignItems: 'baseline', gap: 8, flexWrap: 'wrap' },
  word: { fontFamily: FONTS.serif, fontSize: 22, lineHeight: 28, color: COLORS.black },
  pos: { fontFamily: FONTS.medium, fontStyle: 'italic', fontSize: 13, color: COLORS.primaryDark },
  meaning: { fontFamily: FONTS.regular, fontSize: 15, lineHeight: 22, color: COLORS.gray900, marginTop: 2 },
});
