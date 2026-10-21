// A pick-one field: looks like a text field, opens a sheet with the options (and a search box when there are many).
import { useMemo, useState } from 'react';
import { FlatList, Pressable, StyleSheet, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, MAX_FONT_SCALE, RADIUS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';
import AppText from './AppText';
import Sheet from './Sheet';

/** options: [{ value, label, sub? }] */
export default function Select({ label, value, options, onChange, placeholder, error, searchable = false, style }) {
  const { t } = useT();
  const [open, setOpen] = useState(false);
  const [q, setQ] = useState('');
  const current = options.find((o) => String(o.value) === String(value));
  const shown = useMemo(() => (q.trim() ? options.filter((o) => `${o.label} ${o.sub ?? ''}`.toLowerCase().includes(q.trim().toLowerCase())) : options), [options, q]);

  return (
    <View style={[styles.wrap, style]}>
      {label ? (
        <AppText variant="label" style={{ marginBottom: 6 }}>
          {label}
        </AppText>
      ) : null}
      <Pressable onPress={() => setOpen(true)} accessibilityRole="button" style={[styles.field, !!error && { borderColor: COLORS.danger }]}>
        <AppText style={[styles.value, !current && { color: COLORS.gray500 }]} numberOfLines={1}>
          {current ? current.label : placeholder ?? t('Select…')}
        </AppText>
        <Ionicons name="chevron-down" size={18} color={COLORS.gray700} />
      </Pressable>
      {error ? (
        <AppText variant="caption" style={{ color: COLORS.danger, marginTop: 4 }}>
          {error}
        </AppText>
      ) : null}

      <Sheet visible={open} title={label ?? ''} onClose={() => setOpen(false)} tall={options.length > 8}>
        {searchable || options.length > 10 ? (
          <View style={styles.search}>
            <Ionicons name="search" size={18} color={COLORS.gray700} />
            <TextInput value={q} onChangeText={setQ} placeholder={t('Search…')} placeholderTextColor={COLORS.gray500} maxFontSizeMultiplier={MAX_FONT_SCALE} style={styles.searchInput} autoCapitalize="none" />
          </View>
        ) : null}
        <FlatList
          data={shown}
          keyExtractor={(o) => String(o.value)}
          keyboardShouldPersistTaps="handled"
          renderItem={({ item }) => {
            const on = String(item.value) === String(value);
            return (
              <Pressable
                onPress={() => {
                  onChange(item.value);
                  setOpen(false);
                  setQ('');
                }}
                style={styles.option}
                accessibilityRole="button"
                accessibilityState={{ selected: on }}
              >
                <View style={{ flex: 1 }}>
                  <AppText style={[styles.optionText, on && { fontFamily: FONTS.bold }]}>{item.label}</AppText>
                  {item.sub ? <AppText variant="caption">{item.sub}</AppText> : null}
                </View>
                {on ? <Ionicons name="checkmark" size={20} color={COLORS.primary} /> : null}
              </Pressable>
            );
          }}
          ListEmptyComponent={<AppText style={{ textAlign: 'center', padding: SPACING.lg }}>{t('Nothing matches your search.')}</AppText>}
        />
      </Sheet>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { marginBottom: SPACING.md },
  field: { flexDirection: 'row', alignItems: 'center', minHeight: 50, paddingHorizontal: SPACING.md, borderRadius: RADIUS.md, borderWidth: 1.5, borderColor: COLORS.gray300, backgroundColor: COLORS.white, gap: 8 },
  value: { flex: 1, fontFamily: FONTS.regular, fontSize: 15, color: COLORS.black },
  search: { flexDirection: 'row', alignItems: 'center', gap: 8, borderWidth: 1.5, borderColor: COLORS.gray300, borderRadius: RADIUS.md, paddingHorizontal: 12, marginBottom: 8 },
  searchInput: { flex: 1, minHeight: 44, fontFamily: FONTS.regular, fontSize: 15, color: COLORS.black },
  option: { flexDirection: 'row', alignItems: 'center', paddingVertical: 13, borderBottomWidth: 1, borderBottomColor: COLORS.gray100 },
  optionText: { fontFamily: FONTS.medium, fontSize: 15.5, color: COLORS.black },
});
