// Learn Leb Lango: a search box into the dictionary, the AI translator, proverbs, the phrasebook (everyday greetings),
// the offline dictionary status, and a shortcut to suggest a word.
import { useState } from 'react';
import { Pressable, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import GradientTile from '../../components/GradientTile';
import Screen from '../../components/Screen';
import SectionHeader from '../../components/SectionHeader';
import { COLORS, MAX_FONT_SCALE, RADIUS, SHADOW, SPACING } from '../../constants/theme';
import { useOfflineDictionary } from '../../lib/dictionary';
import { useT } from '../../lib/i18n';
import { useFetch } from '../../lib/useFetch';

export default function Learn() {
  const { t } = useT();
  const [q, setQ] = useState('');
  const offline = useOfflineDictionary();
  const { data } = useFetch('/home');
  const wotd = data?.data?.word_of_the_day;

  const search = () => router.push({ pathname: '/dictionary', params: q.trim() ? { q: q.trim() } : {} });

  return (
    <Screen big title={t('Learn Leb Lango')} subtitle={t('Dictionary, translator, proverbs and phrases')}>
      <View style={styles.search}>
        <Ionicons name="search" size={20} color={COLORS.gray700} />
        <TextInput
          value={q}
          onChangeText={setQ}
          onSubmitEditing={search}
          returnKeyType="search"
          placeholder={t('Search a word in Leb Lango or English')}
          placeholderTextColor={COLORS.gray500}
          maxFontSizeMultiplier={MAX_FONT_SCALE}
          style={styles.input}
          autoCapitalize="none"
        />
        <Pressable onPress={search} accessibilityRole="button" accessibilityLabel={t('Search')} style={styles.searchGo}>
          <Ionicons name="arrow-forward" size={18} color={COLORS.white} />
        </Pressable>
      </View>

      {wotd ? (
        <Pressable onPress={() => router.push({ pathname: '/dictionary', params: { q: wotd.lango } })} accessibilityRole="button" style={styles.wotd}>
          <View style={styles.sun}>
            <Ionicons name="sunny" size={22} color="#B46B00" />
          </View>
          <View style={{ flex: 1 }}>
            <AppText variant="eyebrow">{t('Word of the day')}</AppText>
            <AppText lango style={styles.word}>
              {wotd.lango}
            </AppText>
            <AppText lango style={styles.meaning} numberOfLines={2}>
              {String(wotd.english).split(';').map((s) => s.trim()).join(' · ')}
            </AppText>
          </View>
          <View style={styles.go}>
            <Ionicons name="arrow-forward" size={20} color={COLORS.white} />
          </View>
        </Pressable>
      ) : null}

      <View style={{ marginTop: SPACING.lg }}>
        <SectionHeader title={t('Tools')} />
        <View style={styles.grid}>
          <GradientTile icon="book" tone="amber" title={t('Dictionary')} text={t('Lango–English and English–Lango, with examples')} onPress={() => router.push('/dictionary')} />
          <GradientTile icon="language" tone="sky" title={t('AI Lango translator')} text={t('Words, phrases and sentences, both ways')} onPress={() => router.push('/translate')} />
          <GradientTile icon="ribbon" tone="red" title={t('Proverbs & sayings')} text={t('The wisdom of the elders')} onPress={() => router.push('/proverbs')} />
          <GradientTile icon="chatbubble-ellipses" tone="slate" title={t('Phrasebook')} text={t('Everyday greetings and replies')} onPress={() => router.push('/phrasebook')} />
          <GradientTile icon="add-circle" tone="green" full style={{ marginTop: 0 }} title={t('Suggest a word')} text={t('Add a word or a name; our language team reviews it')} onPress={() => router.push({ pathname: '/contribute/new', params: { type: 'language', kind: 'word' } })} />
        </View>
      </View>

      <Pressable onPress={() => router.push('/settings')} accessibilityRole="button" style={styles.offline}>
        <Ionicons name={offline.ready ? 'checkmark-circle' : 'cloud-download-outline'} size={22} color={offline.ready ? COLORS.success : COLORS.skyDark} />
        <View style={{ flex: 1 }}>
          <AppText variant="subheading">{offline.ready ? t('Dictionary saved on this phone') : t('Use the dictionary without internet')}</AppText>
          <AppText variant="caption">{offline.ready ? t('{n} words available offline', { n: offline.count.toLocaleString('en-US') }) : t('Download it once in Settings.')}</AppText>
        </View>
        <Ionicons name="chevron-forward" size={18} color={COLORS.gray500} />
      </Pressable>
    </Screen>
  );
}

const styles = StyleSheet.create({
  search: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.full, paddingLeft: 18, paddingRight: 6, ...SHADOW, shadowOpacity: 0.1 },
  input: { flex: 1, minHeight: 52, fontFamily: 'Inter_400Regular', fontSize: 16, color: COLORS.black },
  searchGo: { width: 42, height: 42, borderRadius: 21, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  go: { width: 42, height: 42, borderRadius: 21, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  wotd: { flexDirection: 'row', alignItems: 'center', gap: 12, marginTop: SPACING.md, backgroundColor: COLORS.white, borderRadius: RADIUS.lg + 2, padding: 14, ...SHADOW },
  sun: { width: 48, height: 48, borderRadius: 16, backgroundColor: '#FFF1D6', alignItems: 'center', justifyContent: 'center' },
  word: { fontFamily: 'Inter_700Bold', fontSize: 28, lineHeight: 35, color: COLORS.primaryDark },
  meaning: { fontFamily: 'Inter_400Regular', fontSize: 14.5, lineHeight: 21, color: COLORS.gray900 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  offline: { flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, padding: SPACING.md, marginTop: SPACING.lg, ...SHADOW },
});
