// Explore: the heritage of Lango in vivid gradient tiles (the FaithFellows "Community" layout) — clans, districts and Lira City,
// notable Langos, history, heritage (ceremonies, dance, crafts, sites), biodiversity, the library and news. Each tile opens its own list.
// i18n:all (the phrases in this file are listed for translation by `npm run strings`)
import { StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import GradientTile from '../../components/GradientTile';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import SectionHeader from '../../components/SectionHeader';
import { SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';

const TILES = [
  { href: '/clans', icon: 'people', title: 'Clan Explorer', text: 'Histories, totems and leaders', tone: 'green' },
  { href: '/districts', icon: 'map', title: 'Districts & Lira City', text: 'People, economy and places', tone: 'red' },
  { href: '/people', icon: 'star', title: 'Notable Langos', text: 'Stories every young Lango should know', tone: 'amber' },
  { href: '/history', icon: 'time', title: 'Our History', text: 'Chapters, timeline and glossary', tone: 'dark' },
  { href: '/heritage', icon: 'color-palette', title: 'Heritage', text: 'Ceremonies, dance, crafts, sites', tone: 'red' },
  { href: '/species', icon: 'leaf', title: 'Biodiversity', text: 'Plants and animals of Lango', tone: 'green' },
  { href: '/library', icon: 'library', title: 'Digital library', text: 'Documents and records', tone: 'sky' },
  { href: '/news', icon: 'newspaper', title: 'News', text: 'Announcements from LCI', tone: 'slate' },
];

export default function Explore() {
  const { t } = useT();

  return (
    <Screen big title={t('Explore')} subtitle={t('Discover the heritage of the Lango people')}>
      <SectionHeader title={t('Grow your roots')} />
      <Row icon="people-outline" iconTone="green" title={t('Find your clan')} subtitle={t('Search the clan register and see who leads it.')} onPress={() => router.push('/clans')} />
      <View style={styles.grid}>
        {TILES.map((x) => (
          <GradientTile key={x.href} icon={x.icon} tone={x.tone} title={t(x.title)} text={t(x.text)} onPress={() => router.push(x.href)} />
        ))}
      </View>
      <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.lg }}>
        {t('Everything here is checked by LCI before it is published.')}
      </AppText>
    </Screen>
  );
}

const styles = StyleSheet.create({
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12, marginTop: SPACING.sm },
});
