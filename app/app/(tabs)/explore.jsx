// Explore: the heritage of Lango in tiles — clans, districts and Lira City, notable Langos, history, heritage (ceremonies, dance,
// crafts, sites), biodiversity and the library. Each tile opens its own list.
// i18n:all (the phrases in this file are listed for translation by `npm run strings`)
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Screen from '../../components/Screen';
import { COLORS, FONTS, RADIUS, SHADOW, SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';

const TILES = [
  { href: '/clans', icon: 'people-outline', title: 'Clan Explorer', text: 'Histories, totems and leaders', tone: [COLORS.skyMist, COLORS.skyDark] },
  { href: '/districts', icon: 'map-outline', title: 'Districts & Lira City', text: 'People, economy and places', tone: [COLORS.primaryMist, COLORS.primaryDark] },
  { href: '/people', icon: 'ribbon-outline', title: 'Notable Langos', text: 'Stories every young Lango should know', tone: [COLORS.yellowMist, COLORS.yellowInk] },
  { href: '/history', icon: 'time-outline', title: 'Our History', text: 'Chapters, timeline and glossary', tone: [COLORS.gray100, COLORS.navy] },
  { href: '/heritage', icon: 'color-palette-outline', title: 'Heritage', text: 'Ceremonies, dance, crafts, sites', tone: [COLORS.primaryMist, COLORS.primaryDark] },
  { href: '/species', icon: 'leaf-outline', title: 'Biodiversity', text: 'Plants and animals of Lango', tone: [COLORS.skyMist, COLORS.skyDark] },
  { href: '/library', icon: 'library-outline', title: 'Digital library', text: 'Documents and records', tone: [COLORS.yellowMist, COLORS.yellowInk] },
  { href: '/news', icon: 'newspaper-outline', title: 'News', text: 'Announcements from LCI', tone: [COLORS.gray100, COLORS.navy] },
];

export default function Explore() {
  const { t } = useT();

  return (
    <Screen big title={t('Explore')} subtitle={t('Discover the heritage of the Lango people')}>
      <View style={styles.grid}>
        {TILES.map((x) => (
          <Pressable key={x.href} onPress={() => router.push(x.href)} accessibilityRole="button" style={({ pressed }) => [styles.tile, pressed && { opacity: 0.9 }]}>
            <View style={[styles.icon, { backgroundColor: x.tone[0] }]}>
              <Ionicons name={x.icon} size={24} color={x.tone[1]} />
            </View>
            <AppText style={styles.title}>{t(x.title)}</AppText>
            <AppText variant="caption">{t(x.text)}</AppText>
          </Pressable>
        ))}
      </View>
    </Screen>
  );
}

const styles = StyleSheet.create({
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  tile: { width: '48%', flexGrow: 1, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1, borderColor: COLORS.line, padding: SPACING.md, gap: 6, minHeight: 148, ...SHADOW },
  icon: { width: 46, height: 46, borderRadius: RADIUS.lg, alignItems: 'center', justifyContent: 'center', marginBottom: 6 },
  title: { fontFamily: FONTS.serif, fontSize: 17, lineHeight: 22, color: COLORS.black },
});
