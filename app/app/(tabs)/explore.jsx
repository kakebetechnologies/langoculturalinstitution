// Explore: the heritage of Lango as soft white feature cards (the "Listen and read" four first, then clans, districts and Lira City,
// notable Langos, history, heritage, biodiversity, the library and news). Each card opens its own list.
// i18n:all (the phrases in this file are listed for translation by `npm run strings`)
import { StyleSheet, View } from 'react-native';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import GradientTile from '../../components/GradientTile';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import SectionHeader from '../../components/SectionHeader';
import { SPACING } from '../../constants/theme';
import { useCommunity } from '../../lib/community';
import { useT } from '../../lib/i18n';

const LISTEN = [
  { href: '/radio', icon: 'radio', title: 'Lango Radio', text: 'Music, talk and stories', tone: 'red' },
  { href: '/podcast', icon: 'mic', title: 'Lango Podcast', text: 'Stories from our elders', tone: 'amber' },
  { href: '/bible', icon: 'book', title: 'Lango Bible', text: 'Read and search', tone: 'sky' },
  { href: '/donate', icon: 'heart', title: 'Support', text: 'Help keep the Lango App running', tone: 'green' },
];

const TILES = [
  { href: '/clans', icon: 'people', title: 'Clan Explorer', text: 'Histories, totems and leaders', tone: 'green' },
  { href: '/districts', icon: 'map', title: 'Districts & Lira City', text: 'People, economy and places', tone: 'red' },
  { href: '/people', icon: 'star', title: 'Notable Langos', text: 'Stories every young Lango should know', tone: 'amber' },
  { href: '/history', icon: 'time', title: 'Our History', text: 'Chapters, timeline and glossary', tone: 'slate' },
  { href: '/heritage', icon: 'color-palette', title: 'Heritage', text: 'Ceremonies, dance, crafts, sites', tone: 'red' },
  { href: '/species', icon: 'leaf', title: 'Biodiversity', text: 'Plants and animals of Lango', tone: 'green' },
  { href: '/library', icon: 'library', title: 'Digital library', text: 'Documents and records', tone: 'sky' },
  { href: '/news', icon: 'newspaper', title: 'News', text: 'News and stories', tone: 'slate' },
];

export default function Explore() {
  const { t } = useT();
  const { features } = useCommunity();
  const listen = LISTEN.filter((x) => (x.href === '/radio' ? features.radio : x.href === '/podcast' ? features.podcast : x.href === '/bible' ? features.bible : true));
  const tiles = TILES.filter((x) => (x.href === '/species' ? features.species : x.href === '/library' ? features.library : true));

  return (
    <Screen big title={t('Explore')} subtitle={t('Discover the heritage of the Lango people')}>
      <SectionHeader title={t('Listen and read')} />
      <View style={styles.grid}>
        {listen.map((x) => (
          <GradientTile key={x.href} icon={x.icon} tone={x.tone} title={t(x.title)} text={t(x.text)} onPress={() => router.push(x.href)} />
        ))}
      </View>
      <SectionHeader title={t('Grow your roots')} style={{ marginTop: SPACING.lg }} />
      <Row icon="people-outline" iconTone="green" title={t('Find your clan')} subtitle={t('Search the clan register and see who leads it.')} onPress={() => router.push('/clans')} />
      <View style={styles.grid}>
        {tiles.map((x) => (
          <GradientTile key={x.href} icon={x.icon} tone={x.tone} title={t(x.title)} text={t(x.text)} onPress={() => router.push(x.href)} />
        ))}
      </View>
      <AppText variant="caption" style={{ textAlign: 'center', marginTop: SPACING.lg }}>
        {t('Everything here is checked before it is published.')}
      </AppText>
    </Screen>
  );
}

const styles = StyleSheet.create({
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12, marginTop: SPACING.xs },
});
