// The AI cultural assistant (POST /api/v1/assistant): answers only from the Lango register and reviewed archive — "Who is the clan
// leader of …?", what a district is known for, who a notable Lango was, what a word means — and shows its sources. If it does
// not know it says so, rather than guessing. Answers come back as simple HTML (bold, line breaks, a leader's photo).
// i18n:all (the phrases in this file are listed for translation by `npm run strings`)
import { useRef, useState } from 'react';
import { FlatList, KeyboardAvoidingView, Linking, Platform, Pressable, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import AppText from '../components/AppText';
import Chip from '../components/Chip';
import RichText from '../components/RichText';
import ScreenHeader from '../components/ScreenHeader';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS, SPACING, SHADOW } from '../constants/theme';
import api, { errorMessage } from '../lib/api';
import { useT } from '../lib/i18n';

const SUGGESTIONS = ['Who is the clan leader of Otengoro Olang?', 'Tell me about Amolatar', 'What is Lira City known for?', 'Who was Milton Obote?', 'What is an Awitong?', 'What does yoo mean?'];

export default function Assistant() {
  const { t } = useT();
  const list = useRef(null);
  const [text, setText] = useState('');
  const [busy, setBusy] = useState(false);
  const [msgs, setMsgs] = useState([{ id: 'hi', who: 'bot', html: `<p>${t('Hello! I can answer questions about Lango history, clans, leaders, districts, traditions and the dictionary. I only use reviewed content from the LCI archive, so if I do not know something I will say so.')}</p>`, sources: [] }]);

  async function ask(q) {
    const question = (q ?? text).trim();
    if (!question || busy) return;
    setText('');
    setBusy(true);
    setMsgs((m) => [...m, { id: `u${Date.now()}`, who: 'me', html: null, text: question }]);
    try {
      const { data } = await api.post('/assistant', { q: question });
      setMsgs((m) => [...m, { id: `b${data.data.id}`, who: 'bot', html: data.data.answer_html, sources: data.data.sources ?? [] }]);
    } catch (e) {
      setMsgs((m) => [...m, { id: `e${Date.now()}`, who: 'bot', html: `<p>${errorMessage(e)}</p>`, sources: [] }]);
    } finally {
      setBusy(false);
      setTimeout(() => list.current?.scrollToEnd({ animated: true }), 80);
    }
  }

  return (
    <KeyboardAvoidingView style={styles.screen} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScreenHeader title={t('AI cultural assistant')} subtitle={t('Answers only from reviewed LCI content')} />
      <FlatList
        ref={list}
        data={msgs}
        keyExtractor={(m) => m.id}
        contentContainerStyle={{ padding: SPACING.md, gap: 10 }}
        onContentSizeChange={() => list.current?.scrollToEnd({ animated: true })}
        keyboardShouldPersistTaps="handled"
        renderItem={({ item }) =>
          item.who === 'me' ? (
            <View style={[styles.bubble, styles.me]}>
              <AppText style={{ color: COLORS.white }}>{item.text}</AppText>
            </View>
          ) : (
            <View style={[styles.bubble, styles.bot]}>
              <RichText html={item.html} compact />
              {item.sources?.length ? (
                <View style={styles.sources}>
                  <AppText variant="caption">{t('Sources')}: </AppText>
                  {item.sources.map((s, i) => (
                    <AppText key={i} variant="caption" style={{ color: COLORS.primaryDark, textDecorationLine: 'underline' }} onPress={() => Linking.openURL(s.url).catch(() => {})}>
                      {s.title}
                      {i < item.sources.length - 1 ? ' · ' : ''}
                    </AppText>
                  ))}
                </View>
              ) : null}
            </View>
          )
        }
        ListFooterComponent={
          msgs.length <= 1 ? (
            <View style={styles.sugg}>
              {SUGGESTIONS.map((s) => (
                <Chip key={s} label={t(s)} onPress={() => ask(t(s))} />
              ))}
            </View>
          ) : busy ? (
            <AppText variant="caption" style={{ marginTop: 4 }}>
              {t('Thinking…')}
            </AppText>
          ) : null
        }
      />
      <View style={styles.bar}>
        <TextInput value={text} onChangeText={setText} onSubmitEditing={() => ask()} placeholder={t('Ask a question…')} placeholderTextColor={COLORS.gray500} maxFontSizeMultiplier={MAX_FONT_SCALE} style={styles.input} maxLength={300} returnKeyType="send" />
        <Pressable onPress={() => ask()} accessibilityRole="button" accessibilityLabel={t('Ask')} style={[styles.send, (!text.trim() || busy) && { opacity: 0.5 }]}>
          <Ionicons name="arrow-up" size={20} color={COLORS.white} />
        </Pressable>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  bubble: { maxWidth: '92%', borderRadius: RADIUS.lg, padding: 12 },
  me: { alignSelf: 'flex-end', backgroundColor: COLORS.navy, borderBottomRightRadius: 4 },
  bot: { alignSelf: 'flex-start', backgroundColor: COLORS.white, borderBottomLeftRadius: 4, ...SHADOW, shadowOpacity: 0.05, elevation: 1 },
  sources: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 6, paddingTop: 6, borderTopWidth: 1, borderTopColor: COLORS.gray100 },
  sugg: { flexDirection: 'row', flexWrap: 'wrap', gap: 8, marginTop: 4 },
  bar: { flexDirection: 'row', alignItems: 'center', gap: 8, padding: SPACING.md, backgroundColor: COLORS.white, borderTopWidth: 1, borderTopColor: COLORS.line },
  input: { flex: 1, minHeight: 46, borderWidth: 1.5, borderColor: COLORS.gray300, borderRadius: RADIUS.full, paddingHorizontal: 16, fontFamily: FONTS.regular, fontSize: 15.5, color: COLORS.black },
  send: { width: 44, height: 44, borderRadius: 22, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
});
