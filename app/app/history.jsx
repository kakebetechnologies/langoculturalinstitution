// Our History (GET /api/v1/history): the story in chapters you open one by one, then the timeline and the glossary of Lango
// words used in the history (tap a word to see it in the dictionary). Compiled from published sources, for LCI's historians to review.
import { useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import Accordion from '../components/Accordion';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import RichText from '../components/RichText';
import Screen from '../components/Screen';
import SectionHeader from '../components/SectionHeader';
import StateView from '../components/StateView';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function History() {
  const { t } = useT();
  const { data, loading, error, retry, reload, refreshing } = useFetch('/history');
  const h = data?.data;
  const [openAll, setOpenAll] = useState(0); // bumping this remounts the chapters open or closed
  const [expanded, setExpanded] = useState(false);

  return (
    <Screen title={t('Our History')} onRefresh={reload} refreshing={refreshing}>
      <StateView loading={loading} error={error} onRetry={retry}>
        {h ? (
          <>
            <View style={styles.lead}>
              <AppText variant="eyebrow">{t('The story in brief')}</AppText>
              <AppText style={styles.leadText}>{t('The Lango are a Nilotic people of north-central Uganda. Oral tradition remembers a long migration from the north, a society built on clans rather than kings, a colonial era that created chiefs where there had been none, and a post-independence history in which Lango sons and daughters stood at the centre of the nation.')}</AppText>
              <AppText variant="caption" style={{ marginTop: 8 }}>
                {t('Compiled from published sources and still to be reviewed by LCI’s historians and the Council of Elders.')}
              </AppText>
            </View>

            <View style={styles.tools}>
              <SectionHeader title={t('Read the story')} style={{ flex: 1, marginBottom: 0 }} />
              <Button
                small
                variant="outline"
                title={expanded ? t('Collapse all') : t('Expand all')}
                onPress={() => {
                  setExpanded((e) => !e);
                  setOpenAll((n) => n + 1);
                }}
              />
            </View>
            <View style={{ marginTop: 12 }}>
              {h.sections.map((s, i) => (
                <Accordion key={`${s.slug}-${openAll}`} number={String(i + 1).padStart(2, '0')} eyebrow={s.era} title={s.title} summary={s.summary} defaultOpen={expanded || (i === 0 && openAll === 0)}>
                  <RichText html={s.body_html} />
                </Accordion>
              ))}
            </View>

            {h.timeline.length ? (
              <View style={{ marginTop: SPACING.lg }}>
                <SectionHeader eyebrow={t('At a glance')} title={t('Timeline')} />
                <View style={styles.line}>
                  {h.timeline.map((x, i) => (
                    <View key={i} style={styles.tl}>
                      <View style={styles.dot} />
                      <AppText style={styles.year}>{x.year}</AppText>
                      <AppText variant="subheading">{x.title}</AppText>
                      {x.body ? <AppText variant="caption" style={{ marginTop: 2 }}>{x.body}</AppText> : null}
                    </View>
                  ))}
                </View>
              </View>
            ) : null}

            {h.glossary.length ? (
              <View style={{ marginTop: SPACING.lg }}>
                <SectionHeader eyebrow="Leb Lango" title={t('Lango words in this history')} />
                {h.glossary.map((g) => (
                  <Card key={g.term} onPress={() => router.push({ pathname: '/dictionary', params: { q: g.term } })} style={{ marginBottom: 10, borderLeftWidth: 4, borderLeftColor: COLORS.sky }}>
                    <AppText style={styles.term}>{g.term}</AppText>
                    <AppText>{String(g.meaning).split(';').map((s) => s.trim()).join(' · ')}</AppText>
                    {g.note ? <AppText variant="caption" style={{ marginTop: 2 }}>{g.note}</AppText> : null}
                  </Card>
                ))}
              </View>
            ) : null}

            <Card style={{ marginTop: SPACING.lg, backgroundColor: COLORS.navy, borderColor: COLORS.navy }}>
              <AppText variant="heading" style={{ color: COLORS.white }}>
                {t('Do you hold a piece of Lango history?')}
              </AppText>
              <AppText style={{ color: '#B3B8C1', marginVertical: 8 }}>{t('An old photograph, a letter, a story told by your grandparents — add it to the archive so it is never lost.')}</AppText>
              <Button title={t('Share an oral history')} variant="gold" small onPress={() => router.push({ pathname: '/contribute/new', params: { type: 'oral_history' } })} style={{ alignSelf: 'flex-start' }} />
            </Card>
          </>
        ) : null}
      </StateView>
    </Screen>
  );
}

const styles = StyleSheet.create({
  lead: { backgroundColor: COLORS.paper, borderRadius: RADIUS.lg, padding: SPACING.md + 2, borderWidth: 1, borderColor: '#EFE7BF' },
  leadText: { fontFamily: FONTS.serifSemi, fontSize: 17, lineHeight: 25, color: COLORS.black, marginTop: 4 },
  tools: { flexDirection: 'row', alignItems: 'center', gap: 10, marginTop: SPACING.lg },
  line: { borderLeftWidth: 3, borderLeftColor: '#F1E9BD', marginLeft: 8, paddingLeft: 18 },
  tl: { marginBottom: 18 },
  dot: { position: 'absolute', left: -28, top: 3, width: 15, height: 15, borderRadius: 8, backgroundColor: COLORS.primary, borderWidth: 3, borderColor: COLORS.canvas },
  year: { fontFamily: FONTS.serif, fontSize: 16, color: COLORS.primaryDark },
  term: { fontFamily: FONTS.serif, fontSize: 20, lineHeight: 26, color: COLORS.black },
});
