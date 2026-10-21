// The bottom bar: Home, Explore, a raised red Contribute button in the middle (the star feature), Learn and Me.
// It draws the tabs the navigator describes, with the flag-stripe line of the Lango logo along its top edge.
import { Pressable, StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS, FONTS, SHADOW } from '../constants/theme';
import AppText from './AppText';

export default function AppTabBar({ state, descriptors, navigation }) {
  const insets = useSafeAreaInsets();

  return (
    <View style={[styles.wrap, { paddingBottom: Math.max(insets.bottom, 8) }]}>
      <View style={styles.flag}>
        <View style={{ flex: 1, backgroundColor: COLORS.navy }} />
        <View style={{ flex: 1, backgroundColor: COLORS.primary }} />
        <View style={{ flex: 1, backgroundColor: COLORS.yellow }} />
        <View style={{ flex: 1, backgroundColor: COLORS.sky }} />
      </View>
      <View style={styles.row}>
        {state.routes.map((route, index) => {
          const { options } = descriptors[route.key];
          const focused = state.index === index;
          const label = options.title ?? route.name;
          const centre = route.name === 'contribute';
          const color = focused ? COLORS.primaryDark : COLORS.gray700;

          const onPress = () => {
            const event = navigation.emit({ type: 'tabPress', target: route.key, canPreventDefault: true });
            if (!focused && !event.defaultPrevented) navigation.navigate(route.name, route.params);
          };

          return (
            <Pressable key={route.key} onPress={onPress} accessibilityRole="button" accessibilityState={{ selected: focused }} accessibilityLabel={label} style={styles.tab}>
              {centre ? (
                <View style={[styles.bubble, focused && { backgroundColor: COLORS.primaryDark }]}>{options.tabBarIcon?.({ focused, color: COLORS.white, size: 26 })}</View>
              ) : (
                options.tabBarIcon?.({ focused, color, size: 23 })
              )}
              <AppText allowFontScaling={false} numberOfLines={1} style={[styles.label, { color: centre ? COLORS.primaryDark : color }, focused && { fontFamily: FONTS.bold }]}>
                {label}
              </AppText>
            </Pressable>
          );
        })}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { backgroundColor: COLORS.white, borderTopWidth: 1, borderTopColor: COLORS.line },
  flag: { position: 'absolute', left: 0, right: 0, top: -1, height: 3, flexDirection: 'row' },
  row: { flexDirection: 'row', alignItems: 'flex-end', paddingTop: 8 },
  tab: { flex: 1, alignItems: 'center', justifyContent: 'flex-end', gap: 3, minHeight: 52 },
  bubble: { width: 54, height: 54, borderRadius: 27, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center', marginTop: -26, borderWidth: 4, borderColor: COLORS.white, ...SHADOW, shadowColor: COLORS.primary, shadowOpacity: 0.35 },
  label: { fontFamily: FONTS.semibold, fontSize: 11 },
});
