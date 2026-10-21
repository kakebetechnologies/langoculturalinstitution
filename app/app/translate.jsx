// The AI Lango translator (POST /api/v1/translate): English → Leb Lango and back, built from the dictionary and the worked sentences
// of the Practical Lango Grammar (including every word the community has had approved). It says plainly what it is — a
// dictionary-based translator, not a full machine-translation model — and offers unknown words for suggestion.
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import Chip from '../components/Chip';
import KeyboardScroll from '../components/KeyboardScroll';
import ScreenHeader from '../components/ScreenHeader';
import Segmented from '../components/Segmented';
import TextField from '../components/TextField';
import { COLORS, FONTS, RADIUS, SPACING } from '../constants/theme';
import api, { errorMessage } from '../lib/api';
import { useT } from '../lib/i18n';
import { isOffline } from '../lib/net';
import { useFetch } from '../lib/useFetch';

export default function Translate() {
  const { t } = useT();
  const params = useLocalSearchParams();
  const [dir, setDir] = useState(params.dir === 'lango-en' ? 'lango-en' : 'en-lango');
  const [text, setText] = useState(typeof params.text === 'string' ? params.text : '');
  const [res, setRes] = useState(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const greetings = useFetch('/greetings');

  async function run(value = text, direction = dir) {
    if (!value.trim()) return;
    setBusy(true);
    setError('');
    try {
      const { data } = await api.post('/translate', { text: value, direction });
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
        <Segmented
          value={dir}
          onChange={(v) => {
            setDir(v);
            setRes(null);
          }}
          options={[
            { value: 'en-lango', label: 'English → Leb Lango' },
            { value: 'lango-en', label: 'Leb Lango → English' },
          ]}
        />
        <AppText variant="caption" style={{ marginVertical: 8 }}>
          {t('Letters like ε, ɔ and ŋ are optional — type e, o and ng.')}
        </AppText>
        <TextField value={text} onChangeText={setText} multiline placeholder={t('Type a word, phrase or sentence…')} maxLength={600} label={dir === 'en-lango' ? 'English' : 'Leb Lango'} />
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

        {res ? (
          <Card style={{ marginTop: SPACING.lg, backgroundColor: COLORS.paper, borderColor: '#EFE7BF' }}>
            <AppText variant="eyebrow">{dir === 'en-lango' ? 'Leb Lango' : 'English'}</AppText>
            <AppText style={styles.out} selectable>
              {res.translation}
            </AppText>
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
                    setDir('lango-en');
                    setText(g.lango);
                    run(g.lango, 'lango-en');
                  }}
                />
              ))}
            </View>
          </View>
        ) : null}

        <AppText variant="caption" style={{ marginTop: SPACING.lg }}>
          {t('This is a dictionary-based translator, not a full machine-translation model. It looks for the whole sentence among the worked examples of the Practical Lango Grammar, then matches the longest phrases in the dictionary, and finally translates word by word. Leb Lango grammar is rich, so ask a fluent speaker to check longer sentences.')}
        </AppText>
      </KeyboardScroll>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  out: { fontFamily: FONTS.serifSemi, fontSize: 21, lineHeight: 30, color: COLORS.black, marginTop: 4 },
  error: { backgroundColor: COLORS.primaryMist, borderRadius: RADIUS.md, padding: SPACING.md, marginTop: SPACING.md },
});
