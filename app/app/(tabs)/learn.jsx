// Learn Leb Lango: a search box into the dictionary, the AI translator, proverbs, the phrasebook (everyday greetings),
// the offline dictionary status, and a shortcut to suggest a word.
import { useState } from 'react';
import { Pressable, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import AppText from '../../components/AppText';
import Row from '../../components/Row';
import Screen from '../../components/Screen';
import SectionHeader from '../../components/SectionHeader';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS, SPACING } from '../../constants/theme';
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
        <Pressable onPress={search} accessibilityRole="button" accessibilityLabel={t('Search')} style={styles.go}>
          <Ionicons name="arrow-forward" size={18} color={COLORS.white} />
        </Pressable>
      </View>

      {wotd ? (
        <Pressable onPress={() => router.push({ pathname: '/dictionary', params: { q: wotd.lango } })} accessibilityRole="button" style={styles.wotd}>
          <View style={{ flex: 1 }}>
            <AppText variant="eyebrow" style={{ color: '#9AA1AC' }}>
              {t('Word of the day')}
            </AppText>
            <AppText style={styles.word}>{wotd.lango}</AppText>
            <AppText style={styles.meaning} numberOfLines={2}>
              {String(wotd.english).split(';').map((s) => s.trim()).join(' · ')}
            </AppText>
          </View>
          <Ionicons name="chevron-forward" size={22} color={COLORS.yellow} />
        </Pressable>
      ) : null}

      <View style={{ marginTop: SPACING.lg }}>
        <SectionHeader title={t('Tools')} />
        <Row icon="book-outline" iconTone="gold" title={t('Dictionary')} subtitle={t('Lango–English and English–Lango, with examples')} onPress={() => router.push('/dictionary')} />
        <Row icon="language-outline" iconTone="sky" title={t('AI Lango translator')} subtitle={t('Words, phrases and sentences, both ways')} onPress={() => router.push('/translate')} />
        <Row icon="ribbon-outline" iconTone="red" title={t('Proverbs & sayings')} subtitle={t('The wisdom of the elders')} onPress={() => router.push('/proverbs')} />
        <Row icon="chatbubble-ellipses-outline" iconTone="dark" title={t('Phrasebook')} subtitle={t('Everyday greetings and replies')} onPress={() => router.push('/phrasebook')} />
        <Row icon="add-circle-outline" iconTone="gold" title={t('Suggest a word')} subtitle={t('Add a word or a name; our language team reviews it')} onPress={() => router.push({ pathname: '/contribute/new', params: { type: 'language', kind: 'word' } })} />
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
  search: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1.5, borderColor: COLORS.gray300, paddingLeft: 14, paddingRight: 6 },
  input: { flex: 1, minHeight: 52, fontFamily: FONTS.regular, fontSize: 16, color: COLORS.black },
  go: { width: 40, height: 40, borderRadius: 8, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  wotd: { flexDirection: 'row', alignItems: 'center', gap: 10, backgroundColor: COLORS.navy, borderRadius: RADIUS.lg, borderTopWidth: 4, borderTopColor: COLORS.yellow, padding: SPACING.md + 2, marginTop: SPACING.md },
  word: { fontFamily: FONTS.serif, fontSize: 30, lineHeight: 38, color: COLORS.yellow },
  meaning: { fontFamily: FONTS.regular, fontSize: 15, lineHeight: 22, color: COLORS.white },
  offline: { flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1, borderColor: COLORS.line, padding: SPACING.md, marginTop: SPACING.sm },
});
