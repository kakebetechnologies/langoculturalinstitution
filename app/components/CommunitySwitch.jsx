// Chooses the community the app is about, Lango or Acholi. Used on the first-run intro (onboarding), the welcome and sign-in screens (before there is an account,
// so the right one is used when signing in or creating it) and in Settings (where a signed-in member's own community is changed on their account).
import { Pressable, StyleSheet, View } from 'react-native';
import AppText from './AppText';
import { COLORS, FONTS, RADIUS } from '../constants/theme';
import { useCommunity } from '../lib/community';

export default function CommunitySwitch({ onDark = false, compact = false, onChange, style }) {
  const { community, list, setCommunity } = useCommunity();
  if (list.length < 2) return null; // nothing to choose between

  return (
    <View style={[styles.wrap, onDark ? styles.wrapDark : styles.wrapLight, style]} accessibilityRole="radiogroup">
      {list.map((c) => {
        const on = c.code === community;
        return (
          <Pressable
            key={c.code}
            accessibilityRole="radio"
            accessibilityState={{ selected: on }}
            onPress={() => {
              if (on) return;
              setCommunity(c.code);
              onChange?.(c.code);
            }}
            style={[styles.item, compact && styles.itemCompact, on && styles.itemOn]}
          >
            <AppText style={[styles.label, onDark && !on && styles.labelDark, on && styles.labelOn]}>{c.name}</AppText>
          </Pressable>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'center', padding: 4, borderRadius: RADIUS.lg, alignSelf: 'center' },
  wrapDark: { backgroundColor: 'rgba(255,255,255,0.13)' },
  wrapLight: { backgroundColor: COLORS.gray100 ?? '#E9ECF0' },
  item: { minWidth: 92, paddingVertical: 9, paddingHorizontal: 16, borderRadius: RADIUS.full, alignItems: 'center' },
  itemCompact: { minWidth: 78, paddingHorizontal: 12 },
  itemOn: { backgroundColor: COLORS.yellow },
  label: { fontFamily: FONTS.semibold, fontSize: 14.5, color: COLORS.gray900 },
  labelDark: { color: 'rgba(255,255,255,0.8)' },
  labelOn: { color: COLORS.black },
});
