// The bottom bar (the FaithFellows pill, in the Lango logo colours): a floating dark pill with a round red button that rises out of a
// soft notch over the tab you are on, and glides to the next tab when you change. Every tab keeps its name under its icon.
// Contribute, the star feature, always shows as a red "+" so it is easy to find even when another tab is active.
// It draws the tabs the navigator describes: Home, Explore, Contribute, Learn and Me.
import { useEffect, useRef, useState } from 'react';
import { Animated, Easing, Pressable, StyleSheet, View, useWindowDimensions } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import Svg, { Circle, Defs, LinearGradient as SvgGradient, Mask, Rect, Stop } from 'react-native-svg';
import { COLORS, FONTS } from '../constants/theme';
import AppText from './AppText';

const HEIGHT = 74; // the pill
const RISE = 24; // how far the round button rises above the pill
const SIDE = 14; // space left and right of the pill

const SCOOP_R = 36; // radius of the circular bite the raised button sits in
const SCOOP_DEPTH = 21; // how deep the bite goes into the pill

export default function AppTabBar({ state, descriptors, navigation }) {
  const insets = useSafeAreaInsets();
  const { width: screen } = useWindowDimensions();
  const width = Math.min(screen, 560) - SIDE * 2;
  const tab = width / state.routes.length;
  const slide = useRef(new Animated.Value(state.index)).current;
  const place = (value) => Math.max(SCOOP_R + 6, Math.min(width - SCOOP_R - 6, value * tab + tab / 2));
  const [cx, setCx] = useState(place(state.index));

  useEffect(() => {
    const id = slide.addListener(({ value }) => setCx(place(value)));
    Animated.timing(slide, { toValue: state.index, duration: 280, easing: Easing.out(Easing.cubic), useNativeDriver: false }).start();

    return () => slide.removeListener(id);
  }, [state.index, tab, slide]);

  return (
    <View style={[styles.wrap, { paddingBottom: Math.max(insets.bottom, 8) }]}>
      <View style={{ width, height: HEIGHT + RISE, marginHorizontal: SIDE, alignSelf: 'center' }}>
        <View style={[styles.pill, { height: HEIGHT }]}>
          <Svg width={width} height={HEIGHT}>
            <Defs>
              <SvgGradient id="deep" x1="0" y1="0" x2="1" y2="1">
                <Stop offset="0" stopColor="#14161B" />
                <Stop offset="1" stopColor="#3A1217" />
              </SvgGradient>
              <Mask id="bite" x="0" y="0" width={width} height={HEIGHT}>
                <Rect x="0" y="0" width={width} height={HEIGHT} fill="#fff" />
                <Circle cx={cx} cy={SCOOP_DEPTH - SCOOP_R} r={SCOOP_R} fill="#000" />
              </Mask>
            </Defs>
            <Rect x="0" y="0" width={width} height={HEIGHT} rx="30" fill="url(#deep)" mask="url(#bite)" />
          </Svg>
        </View>

        <View style={[styles.bubble, { left: cx - 29 }]} pointerEvents="none">
          <LinearGradient colors={['#FF5B64', COLORS.primary, '#A60D17']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.bubbleFill}>
            {descriptors[state.routes[state.index].key].options.tabBarIcon?.({ focused: true, color: '#fff', size: 25 })}
          </LinearGradient>
        </View>

        <View style={[styles.row, { height: HEIGHT }]}>
          {state.routes.map((route, index) => {
            const { options } = descriptors[route.key];
            const focused = state.index === index;
            const label = options.title ?? route.name;
            const star = route.name === 'contribute';

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
                {focused ? null : star ? (
                  <View style={styles.star}>
                    <Ionicons name="add" size={22} color="#fff" />
                  </View>
                ) : (
                  options.tabBarIcon?.({ focused: false, color: 'rgba(255,255,255,0.72)', size: 22 })
                )}
                <AppText allowFontScaling={false} numberOfLines={1} style={[styles.label, focused && styles.labelOn]}>
                  {label}
                </AppText>
              </Pressable>
            );
          })}
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { backgroundColor: COLORS.canvas },
  pill: { position: 'absolute', left: 0, right: 0, bottom: 0, borderRadius: 30, shadowColor: '#14161B', shadowOpacity: 0.35, shadowRadius: 18, shadowOffset: { width: 0, height: 10 }, elevation: 12 },
  bubble: { position: 'absolute', top: 0, width: 58, height: 58, borderRadius: 29, padding: 4, backgroundColor: COLORS.canvas, shadowColor: COLORS.primary, shadowOpacity: 0.5, shadowRadius: 12, shadowOffset: { width: 0, height: 6 }, elevation: 14 },
  bubbleFill: { flex: 1, borderRadius: 25, alignItems: 'center', justifyContent: 'center' },
  row: { position: 'absolute', left: 0, right: 0, bottom: 0, flexDirection: 'row' },
  tab: { flex: 1, alignItems: 'center', justifyContent: 'flex-end', gap: 3, paddingBottom: 10 },
  star: { width: 30, height: 30, borderRadius: 15, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center' },
  label: { fontFamily: FONTS.semibold, fontSize: 11, color: 'rgba(255,255,255,0.72)' },
  labelOn: { fontFamily: FONTS.bold, color: '#FFFFFF' },
});
