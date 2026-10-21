// One clan (GET /api/v1/clans/{slug}): the clan leader (Awitong) with photograph first, then its history, lineage and notable
// members. If no leader has been recorded yet the page says so.
import { StyleSheet, View } from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Avatar from '../../components/Avatar';
import Card from '../../components/Card';
import DetailScreen from '../../components/DetailScreen';
import Pill from '../../components/Pill';
import RichText from '../../components/RichText';
import { COLORS, FONTS, SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Clan() {
  const { t } = useT();
  const { slug } = useLocalSearchParams();
  const { data, loading, error, retry } = useFetch(`/clans/${slug}`);
  const c = data?.data;

  return (
    <DetailScreen loading={loading} error={error} onRetry={retry} hero={c ? { image: c.image, eyebrow: t('Clan'), title: `${c.name} ${t('Clan')}`, subtitle: c.summary || undefined } : null}>
      {c ? (
        <>
          <Card style={styles.leader}>
            <AppText variant="eyebrow">{c.leader?.title ?? 'Awitong'} · {t('clan leader')}</AppText>
            {c.leader ? (
              <View style={styles.leaderRow}>
                <Avatar name={c.leader.name} photo={c.leader.photo} size={72} />
                <View style={{ flex: 1 }}>
                  <AppText variant="heading">{c.leader.name}</AppText>
                  {c.leader.bio ? (
                    <AppText variant="caption" style={{ marginTop: 2 }}>
                      {c.leader.bio}
                    </AppText>
                  ) : null}
                </View>
              </View>
            ) : (
              <AppText style={{ marginTop: 6 }}>{t('The leader of this clan has not been added to the register yet.')}</AppText>
            )}
          </Card>

          <View style={styles.tags}>
            {c.totem ? <Pill label={`${t('Totem')}: ${c.totem}`} tone="gold" /> : null}
            {c.district ? <Pill label={c.district} tone="sky" /> : null}
            <Pill label={c.verified ? t('Verified by LCI') : t('Awaiting verification')} icon={c.verified ? 'checkmark' : undefined} tone={c.verified ? 'green' : 'grey'} />
          </View>

          {c.history_html ? (
            <>
              <AppText variant="heading" style={styles.h}>
                {t('Clan history')}
              </AppText>
              <RichText html={c.history_html} />
            </>
          ) : (
            <Card style={{ marginTop: SPACING.md }}>
              <AppText variant="subheading">{t('History being documented')}</AppText>
              <AppText variant="caption" style={{ marginTop: 4 }}>
                {t('Clan elders can help complete this page by sharing the clan history through the Contribute tab.')}
              </AppText>
            </Card>
          )}
          {c.lineage_html ? (
            <>
              <AppText variant="heading" style={styles.h}>
                {t('Lineage')}
              </AppText>
              <RichText html={c.lineage_html} />
            </>
          ) : null}
          {c.notable_members_html ? (
            <>
              <AppText variant="heading" style={styles.h}>
                {t('Notable members')}
              </AppText>
              <RichText html={c.notable_members_html} />
            </>
          ) : null}
        </>
      ) : null}
    </DetailScreen>
  );
}

const styles = StyleSheet.create({
  leader: { borderTopWidth: 4, borderTopColor: COLORS.yellow },
  leaderRow: { flexDirection: 'row', alignItems: 'center', gap: 14, marginTop: 10 },
  tags: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginTop: SPACING.md },
  h: { marginTop: SPACING.lg, marginBottom: 8, fontFamily: FONTS.serif },
});
