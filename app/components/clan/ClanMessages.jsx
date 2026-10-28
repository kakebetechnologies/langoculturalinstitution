// The message wall of a clan group: everyone in the group reads and writes here. New messages arrive by polling every few seconds
// while the screen is open. Long-press a message to report it (or delete it, if it is yours or you look after the clan).
// Plain text only, up to 1000 characters.
import { useCallback, useEffect, useRef, useState } from 'react';
import { ActivityIndicator, FlatList, Platform, Pressable, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import AppText from '../AppText';
import Sheet from '../Sheet';
import StateView from '../StateView';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS, SHADOW, SPACING } from '../../constants/theme';
import api, { errorMessage } from '../../lib/api';
import { useAuth } from '../../lib/auth';
import { fmtDate, fmtTime, toDate } from '../../lib/format';
import { useT } from '../../lib/i18n';

const POLL_MS = 8000;
const MAX = 1000;
const MODERATORS = ['super_admin', 'admin', 'moderator', 'clan_leader'];

const sameDay = (a, b) => {
  const x = toDate(a), y = toDate(b);
  return !!x && !!y && x.toDateString() === y.toDateString();
};

export default function ClanMessages({ clanId, onRead }) {
  const { t } = useT();
  const { user } = useAuth();
  const [items, setItems] = useState([]);
  const [state, setState] = useState({ loading: true, error: null, more: false, older: false });
  const [text, setText] = useState('');
  const [sending, setSending] = useState(false);
  const [sendError, setSendError] = useState(null);
  const [menu, setMenu] = useState(null);
  const [thanks, setThanks] = useState(false);
  const list = useRef(null);
  const newest = useRef(0);
  const stick = useRef(true);

  const merge = useCallback((incoming) => {
    setItems((prev) => {
      const byId = new Map(prev.map((m) => [m.id, m]));
      incoming.forEach((m) => byId.set(m.id, m));
      const next = [...byId.values()].sort((a, b) => a.id - b.id);
      newest.current = next.length ? next[next.length - 1].id : 0;
      return next;
    });
  }, []);

  const loadFirst = useCallback(async () => {
    setState((s) => ({ ...s, loading: true, error: null }));
    try {
      const { data } = await api.get(`/clans/${clanId}/messages`);
      setItems([]);
      merge(data.data);
      setState({ loading: false, error: null, more: !!data.has_more, older: false });
      onRead?.();
    } catch (e) {
      setState((s) => ({ ...s, loading: false, error: errorMessage(e) }));
    }
  }, [clanId, merge, onRead]);

  useEffect(() => {
    loadFirst();
  }, [loadFirst]);

  // New messages from the others.
  useEffect(() => {
    const id = setInterval(async () => {
      try {
        const { data } = await api.get(`/clans/${clanId}/messages`, { params: { after: newest.current } });
        if (data.data.length) merge(data.data);
      } catch {
        // Offline for a moment: try again at the next tick.
      }
    }, POLL_MS);
    return () => clearInterval(id);
  }, [clanId, merge]);

  useEffect(() => {
    if (stick.current) setTimeout(() => list.current?.scrollToEnd({ animated: true }), 60);
  }, [items.length]);

  async function older() {
    if (state.older || !state.more || !items.length) return;
    setState((s) => ({ ...s, older: true }));
    try {
      stick.current = false;
      const { data } = await api.get(`/clans/${clanId}/messages`, { params: { before: items[0].id } });
      merge(data.data);
      setState((s) => ({ ...s, older: false, more: !!data.has_more }));
    } catch {
      setState((s) => ({ ...s, older: false }));
    }
  }

  async function send() {
    const body = text.trim();
    if (!body || sending) return;
    setSending(true);
    setSendError(null);
    try {
      const { data } = await api.post(`/clans/${clanId}/messages`, { body });
      stick.current = true;
      merge([data.data]);
      setText('');
    } catch (e) {
      setSendError(errorMessage(e));
    } finally {
      setSending(false);
    }
  }

  async function remove(m) {
    setMenu(null);
    try {
      await api.delete(`/clans/${clanId}/messages/${m.id}`);
      setItems((prev) => prev.filter((x) => x.id !== m.id));
    } catch (e) {
      setSendError(errorMessage(e));
    }
  }

  async function report(m) {
    try {
      await api.post(`/clans/${clanId}/messages/${m.id}/report`, {});
      setThanks(true);
      setTimeout(() => {
        setThanks(false);
        setMenu(null);
      }, 1400);
    } catch (e) {
      setMenu(null);
      setSendError(errorMessage(e));
    }
  }

  const canRemove = (m) => m.mine || MODERATORS.includes(user?.role);

  return (
    <View style={{ flex: 1 }}>
      <StateView loading={state.loading} error={state.error} onRetry={loadFirst}>
        <FlatList
          ref={list}
          data={items}
          keyExtractor={(m) => String(m.id)}
          contentContainerStyle={{ padding: SPACING.md, paddingBottom: SPACING.sm, flexGrow: 1 }}
          onScrollBeginDrag={() => {
            stick.current = false;
          }}
          ListHeaderComponent={
            state.more ? (
              <Pressable onPress={older} style={styles.older} accessibilityRole="button">
                {state.older ? <ActivityIndicator color={COLORS.primary} /> : <AppText style={styles.olderText}>{t('Show earlier messages')}</AppText>}
              </Pressable>
            ) : null
          }
          ListEmptyComponent={
            <View style={styles.empty}>
              <View style={styles.emptyIcon}>
                <Ionicons name="chatbubbles" size={28} color={COLORS.primaryDark} />
              </View>
              <AppText variant="heading">{t('Say hello to your clan')}</AppText>
              <AppText style={{ textAlign: 'center', marginTop: 4 }}>{t('No one has written yet. Be the first: greet the others, share news, ask a question.')}</AppText>
            </View>
          }
          renderItem={({ item, index }) => {
            const prev = items[index - 1];
            return (
              <View>
                {!prev || !sameDay(prev.created_at, item.created_at) ? (
                  <AppText variant="caption" style={styles.day}>
                    {fmtDate(item.created_at)}
                  </AppText>
                ) : null}
                <Pressable onLongPress={() => setMenu(item)} delayLongPress={350} style={[styles.row, item.mine && { justifyContent: 'flex-end' }]} accessibilityHint={t('Press and hold for more')}>
                  <View style={[styles.bubble, item.mine ? styles.mine : styles.theirs]}>
                    {!item.mine ? (
                      <AppText style={styles.author} numberOfLines={1}>
                        {item.user.name} <AppText style={styles.level}>· {t(item.user.level)}</AppText>
                      </AppText>
                    ) : null}
                    <AppText style={[styles.body, item.mine && { color: COLORS.white }]} selectable>
                      {item.body}
                    </AppText>
                    <AppText style={[styles.time, item.mine && { color: 'rgba(255,255,255,0.8)' }]}>{fmtTime(item.created_at)}</AppText>
                  </View>
                </Pressable>
              </View>
            );
          }}
        />
      </StateView>

      {sendError ? (
        <View style={styles.error} accessibilityRole="alert">
          <AppText variant="label" style={{ color: COLORS.danger, flex: 1 }}>
            {sendError}
          </AppText>
          <Pressable onPress={() => setSendError(null)} hitSlop={10}>
            <Ionicons name="close" size={18} color={COLORS.danger} />
          </Pressable>
        </View>
      ) : null}

      <View style={styles.bar}>
        <TextInput
          value={text}
          onChangeText={setText}
          placeholder={t('Write to your clan…')}
          placeholderTextColor={COLORS.gray500}
          multiline
          maxLength={MAX}
          maxFontSizeMultiplier={MAX_FONT_SCALE}
          style={styles.input}
          onFocus={() => {
            stick.current = true;
          }}
        />
        <Pressable onPress={send} disabled={!text.trim() || sending} accessibilityRole="button" accessibilityLabel={t('Send')} style={[styles.send, (!text.trim() || sending) && { opacity: 0.45 }]}>
          {sending ? <ActivityIndicator color={COLORS.white} /> : <Ionicons name="send" size={18} color={COLORS.white} />}
        </Pressable>
      </View>

      <Sheet visible={!!menu} title={t('Message')} onClose={() => setMenu(null)}>
        {thanks ? (
          <AppText style={{ paddingVertical: SPACING.md }}>{t('Thank you. A moderator will look at this message.')}</AppText>
        ) : menu ? (
          <View>
            <AppText variant="caption" numberOfLines={3} style={{ marginBottom: SPACING.md }}>
              {menu.body}
            </AppText>
            {!menu.mine ? (
              <Pressable onPress={() => report(menu)} style={styles.action} accessibilityRole="button">
                <Ionicons name="flag-outline" size={20} color={COLORS.primaryDark} />
                <AppText style={styles.actionText}>{t('Report this message')}</AppText>
              </Pressable>
            ) : null}
            {canRemove(menu) ? (
              <Pressable onPress={() => remove(menu)} style={styles.action} accessibilityRole="button">
                <Ionicons name="trash-outline" size={20} color={COLORS.danger} />
                <AppText style={[styles.actionText, { color: COLORS.danger }]}>{t('Delete this message')}</AppText>
              </Pressable>
            ) : null}
          </View>
        ) : null}
      </Sheet>
    </View>
  );
}

const styles = StyleSheet.create({
  day: { textAlign: 'center', marginVertical: SPACING.sm },
  row: { flexDirection: 'row', marginBottom: 8 },
  bubble: { maxWidth: '82%', paddingHorizontal: 14, paddingVertical: 9, borderRadius: 18 },
  mine: { backgroundColor: COLORS.primary, borderBottomRightRadius: 6 },
  theirs: { backgroundColor: COLORS.white, borderBottomLeftRadius: 6, ...SHADOW, shadowOpacity: 0.05, shadowRadius: 6, elevation: 1 },
  author: { fontFamily: FONTS.bold, fontSize: 12.5, color: COLORS.primaryDark, marginBottom: 2 },
  level: { fontFamily: FONTS.medium, fontSize: 11.5, color: COLORS.gray700 },
  body: { fontFamily: FONTS.regular, fontSize: 15, lineHeight: 21, color: COLORS.black },
  time: { fontFamily: FONTS.regular, fontSize: 10.5, color: COLORS.gray500, alignSelf: 'flex-end', marginTop: 3 },
  older: { alignSelf: 'center', paddingVertical: 8, paddingHorizontal: 16, marginBottom: 8 },
  olderText: { fontFamily: FONTS.semibold, fontSize: 13, color: COLORS.primaryDark },
  empty: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: SPACING.xl },
  emptyIcon: { width: 64, height: 64, borderRadius: 32, backgroundColor: COLORS.primaryMist, alignItems: 'center', justifyContent: 'center', marginBottom: SPACING.md },
  error: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: COLORS.dangerMist, marginHorizontal: SPACING.md, borderRadius: 12, padding: 10 },
  bar: { flexDirection: 'row', alignItems: 'flex-end', gap: 10, paddingHorizontal: SPACING.md, paddingVertical: 10, backgroundColor: COLORS.canvas },
  input: { flex: 1, minHeight: 46, maxHeight: 120, backgroundColor: COLORS.white, borderRadius: 23, paddingHorizontal: 18, paddingTop: Platform.OS === 'ios' ? 13 : 10, paddingBottom: Platform.OS === 'ios' ? 13 : 10, fontFamily: 'Inter_400Regular', fontSize: 15, color: COLORS.black, ...SHADOW, shadowOpacity: 0.05, elevation: 1, ...Platform.select({ web: { outlineStyle: 'none' } }) },
  send: { width: 46, height: 46, borderRadius: 23, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  action: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 14, borderTopWidth: 1, borderTopColor: COLORS.line },
  actionText: { fontFamily: FONTS.semibold, fontSize: 15.5, color: COLORS.black },
});
