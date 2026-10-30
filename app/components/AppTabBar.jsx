// The bottom bar: a floating white bar with soft shadow, five tabs (Home, Explore, Contribute, Learn, Me). The tab you are on gets a red
// icon on a light red tint; Contribute, the star feature, is always the raised red button in the middle, in a white ring.
// The "now playing" bar (MiniPlayer) sits above it while Lango Radio or a podcast episode plays.
import { Pressable, StyleSheet, View, useWindowDimensions } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS, FONTS, RADIUS, SHADOW } from '../constants/theme';
import AppText from './AppText';
import MiniPlayer from './MiniPlayer';

export default function AppTabBar({ state, descriptors, navigation }) {
  const insets = useSafeAreaInsets();
  const { width: screen } = useWindowDimensions();
  const width = Math.min(screen, 560) - 28;

  return (
    <>
      <MiniPlayer />
      <View style={[styles.wrap, { paddingBottom: Math.max(insets.bottom, 10) }]}>
        <View style={[styles.bar, { width }]}>
          {state.routes.map((route, index) => {
            const { options } = descriptors[route.key];
            const focused = state.index === index;
            const label = options.title ?? route.name;
            const center = route.name === 'contribute';

            const onPress = () => {
              const event = navigation.emit({ type: 'tabPress', target: route.key, canPreventDefault: true });
              if (!focused && !event.defaultPrevented) navigation.navigate(route.name, route.params);
            };

            return (
              <Pressable
                key={route.key}
                onPress={onPress}
                onLongPress={() => navigation.emit({ type: 'tabLongPress', target: route.key })}
                accessibilityRole="button"
                accessibilityState={{ selected: focused }}
                accessibilityLabel={label}
                style={styles.tab}
              >
                {center ? (
                  <View style={styles.centerRing}>
                    <LinearGradient colors={['#FF4B55', COLORS.primary, '#B30E18']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.centerBtn}>
                      <Ionicons name="add" size={32} color="#fff" />
                    </LinearGradient>
                  </View>
                ) : (
                  <View style={[styles.iconWrap, focused && styles.iconOn]}>{options.tabBarIcon?.({ focused, color: focused ? COLORS.primaryDark : COLORS.gray700, size: 23 })}</View>
                )}
                <AppText allowFontScaling={false} numberOfLines={1} style={[styles.label, focused && styles.labelOn, center && { marginTop: 1 }]}>
                  {label}
                </AppText>
              </Pressable>
            );
          })}
        </View>
      </View>
    </>
  );
}

const styles = StyleSheet.create({
  wrap: { backgroundColor: COLORS.canvas, alignItems: 'center', paddingTop: 6 },
  bar: { flexDirection: 'row', alignItems: 'flex-end', height: 70, borderRadius: 32, backgroundColor: 'rgba(255,255,255,0.98)', borderWidth: 1, borderColor: '#FFFFFF', paddingHorizontal: 6, paddingBottom: 8, ...SHADOW, shadowOpacity: 0.12, shadowRadius: 22, shadowOffset: { width: 0, height: 10 } },
  tab: { flex: 1, alignItems: 'center', justifyContent: 'flex-end', gap: 2 },
  iconWrap: { width: 48, height: 32, borderRadius: RADIUS.full, alignItems: 'center', justifyContent: 'center' },
  iconOn: { backgroundColor: COLORS.primaryMist },
  centerRing: { width: 66, height: 66, borderRadius: 33, marginTop: -26, backgroundColor: '#FFFFFF', alignItems: 'center', justifyContent: 'center', shadowColor: '#0F172A', shadowOpacity: 0.12, shadowRadius: 10, shadowOffset: { width: 0, height: 4 }, elevation: 6 },
  centerBtn: { width: 56, height: 56, borderRadius: 28, alignItems: 'center', justifyContent: 'center', shadowColor: COLORS.primary, shadowOpacity: 0.45, shadowRadius: 12, shadowOffset: { width: 0, height: 6 }, elevation: 8 },
  label: { fontFamily: FONTS.semibold, fontSize: 10.5, color: COLORS.gray700 },
  labelOn: { fontFamily: FONTS.bold, color: COLORS.primaryDark },
});
