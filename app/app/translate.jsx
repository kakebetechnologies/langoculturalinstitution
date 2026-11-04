// The AI Lango translator (POST /api/v1/translate): English → Leb Lango and back, built from the dictionary and the worked sentences
// of the Practical Lango Grammar (including every word the community has had approved). It says plainly what it is — a
// dictionary-based translator, not a full machine-translation model — and offers unknown words for suggestion.
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import Chip from '../components/Chip';
import KeyboardScroll from '../components/KeyboardScroll';
import ScreenHeader from '../components/ScreenHeader';
import TextField from '../components/TextField';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';
import api, { errorMessage } from '../lib/api';
import { useCommunity } from '../lib/community';
import { useT } from '../lib/i18n';
import { isOffline } from '../lib/net';
import { useFetch } from '../lib/useFetch';

export default function Translate() {
  const { t } = useT();
  const { info, languages } = useCommunity();
  const params = useLocalSearchParams();
  const [from, setFrom] = useState(params.dir === 'lango-en' ? info.code : 'en');
  const [to, setTo] = useState(params.dir === 'lango-en' ? 'en' : info.code);
  const nameOf = (code) => languages.find((l) => l.code === code)?.name ?? code;
  // two different languages: choosing one that the other side already has swaps them
  const pickFrom = (code) => {
    setRes(null);
    if (code === to) setTo(from);
    setFrom(code);
  };
  const pickTo = (code) => {
    setRes(null);
    if (code === from) setFrom(to);
    setTo(code);
  };
  const [text, setText] = useState(typeof params.text === 'string' ? params.text : '');
  const [res, setRes] = useState(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const greetings = useFetch('/greetings');

  async function run(value = text, source = from, target = to) {
    if (!value.trim()) return;
    setBusy(true);
    setError('');
    try {
      const { data } = await api.post('/translate', { text: value, from: source, to: target });
      setRes(data.data);
    } catch (e) {
      setRes(null);
      setError(isOffline(e) ? t('The translator needs an internet connection. The offline dictionary (Settings) can still look up single words.') : errorMessage(e));
    } finally {
      setBusy(false);
    }
  }

  return (
    <KeyboardAvoidingView style={styles.screen} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScreenHeader title={t('AI Lango translator')} />
      <KeyboardScroll contentContainerStyle={{ padding: SPACING.md, paddingBottom: SPACING.xxl }} keyboardShouldPersistTaps="handled">
        <AppText variant="eyebrow">{t('From')}</AppText>
        <View style={styles.pick}>
          {languages.map((l) => (
            <Chip key={`f-${l.code}`} label={l.name} on={from === l.code} onPress={() => pickFrom(l.code)} />
          ))}
        </View>
        <AppText variant="eyebrow" style={{ marginTop: SPACING.sm }}>{t('To')}</AppText>
        <View style={styles.pick}>
          {languages.map((l) => (
            <Chip key={`t-${l.code}`} label={l.name} on={to === l.code} onPress={() => pickTo(l.code)} />
          ))}
        </View>
        <AppText variant="caption" style={{ marginVertical: 8 }}>
          {t('Letters like ε, ɔ and ŋ are optional — type e, o and ng.')}
        </AppText>
        <TextField value={text} onChangeText={setText} multiline placeholder={t('Type a word…')} maxLength={600} label={nameOf(from)} />
        <View style={styles.soon}>
          <Ionicons name="time-outline" size={20} color={COLORS.yellowInk} style={{ marginTop: 1 }} />
          <AppText variant="caption" style={{ flex: 1, color: COLORS.black }}>
            {t('Translating whole sentences is coming soon. For now, type one word and see its meaning; the dictionary also works word by word.')}
          </AppText>
        </View>
        <View style={{ flexDirection: 'row', gap: 10 }}>
          <Button title={t('Translate')} icon="language-outline" onPress={() => run()} loading={busy} style={{ flex: 1 }} />
          <Button
            title={t('Clear')}
            variant="outline"
            onPress={() => {
              setText('');
              setRes(null);
              setError('');
            }}
          />
        </View>

        {error ? (
          <View style={styles.error}>
            <AppText variant="label" style={{ color: COLORS.danger }}>
              {error}
            </AppText>
          </View>
        ) : null}

        {res?.coming_soon ? (
          <Card style={{ marginTop: SPACING.lg, backgroundColor: COLORS.paper, borderColor: '#EFE7BF' }}>
            <AppText variant="subheading">{t('Sentence translation is coming soon')}</AppText>
            <AppText style={{ marginTop: 6 }}>{t('For now the dictionary works word by word: tap a word to look it up.')}</AppText>
            <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginTop: SPACING.md }}>
              {(res.words ?? []).map((w) => (
                <Chip key={w} label={w} onPress={() => router.push({ pathname: '/dictionary', params: { q: w } })} />
              ))}
            </View>
          </Card>
        ) : res ? (
          <Card style={{ marginTop: SPACING.lg, backgroundColor: COLORS.paper, borderColor: '#EFE7BF' }}>
            <AppText variant="eyebrow">{nameOf(to)}</AppText>
            <AppText style={styles.out} selectable>
              {res.translation}
            </AppText>
            {res.likely ? <AppText variant="caption" style={{ marginTop: 6 }}>{t('A likely equivalent, matched through English: not checked by a language expert.')}</AppText> : null}
            {res.unknown_words?.length ? (
              <View style={{ marginTop: SPACING.md }}>
                <AppText variant="caption">{t('Not in the dictionary yet — tap to suggest it:')}</AppText>
                <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginTop: 8 }}>
                  {res.unknown_words.map((w) => (
                    <Chip key={w} label={w} onPress={() => router.push({ pathname: '/contribute/new', params: { type: 'language', kind: 'word', word: w } })} />
                  ))}
                </View>
              </View>
            ) : null}
          </Card>
        ) : null}

        {greetings.data?.data?.length ? (
          <View style={{ marginTop: SPACING.lg }}>
            <AppText variant="subheading" style={{ marginBottom: 8 }}>
              {t('Try a greeting')}
            </AppText>
            <View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 8 }}>
              {greetings.data.data.slice(0, 6).map((g) => (
                <Chip
                  key={g.lango}
                  label={g.lango}
                  onPress={() => {
                    setFrom(info.code);
                    setTo('en');
                    setText(g.lango);
                    run(g.lango, info.code, 'en');
                  }}
                />
              ))}
            </View>
          </View>
        ) : null}

        <AppText variant="caption" style={{ marginTop: SPACING.lg }}>
          {t('Words and the set phrases of the dictionary are translated now, using the Lango dictionary and the worked examples of the Practical Lango Grammar. Whole sentences are coming soon: Leb Lango grammar is rich, so we want them to be right before we offer them.')}
        </AppText>
      </KeyboardScroll>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  out: { fontFamily: FONTS.serifSemi, fontSize: 21, lineHeight: 30, color: COLORS.black, marginTop: 4 },
  soon: { flexDirection: 'row', gap: 10, alignItems: 'flex-start', backgroundColor: COLORS.yellowMist, borderRadius: RADIUS.md, padding: SPACING.md, marginBottom: SPACING.md },
  error: { backgroundColor: COLORS.primaryMist, borderRadius: RADIUS.md, padding: SPACING.md, marginTop: SPACING.md },
  pick: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginTop: 6 },
});
