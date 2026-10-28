// A white rounded square holding a gradient icon, with its name underneath: the "Explore" shortcut grids on Home and Learn.
// `badge` shows a small count. Four fit on a phone row.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, FONTS, GRADIENTS, RADIUS, SHADOW } from '../constants/theme';
import AppText from './AppText';

export default function IconTile({ icon, label, tone = 'red', onPress, badge = 0 }) {
  return (
    <Pressable onPress={onPress} accessibilityRole="button" accessibilityLabel={label} style={({ pressed }) => [styles.wrap, pressed && { opacity: 0.88 }]}>
      <View style={styles.box}>
        <LinearGradient colors={GRADIENTS[tone] ?? GRADIENTS.red} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.icon}>
          <Ionicons name={icon} size={23} color={COLORS.white} />
        </LinearGradient>
        {badge > 0 ? (
          <View style={styles.dot}>
            <AppText allowFontScaling={false} style={styles.dotText}>
              {badge > 9 ? '9+' : badge}
            </AppText>
          </View>
        ) : null}
      </View>
      <AppText numberOfLines={1} style={styles.label}>
        {label}
      </AppText>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  wrap: { width: '25%', alignItems: 'center', gap: 7, marginBottom: 14 },
  box: { width: 66, height: 66, borderRadius: 22, backgroundColor: COLORS.white, alignItems: 'center', justifyContent: 'center', ...SHADOW, shadowOpacity: 0.07 },
  icon: { width: 44, height: 44, borderRadius: 14, alignItems: 'center', justifyContent: 'center' },
  dot: { position: 'absolute', top: -4, right: -4, minWidth: 20, height: 20, borderRadius: 10, paddingHorizontal: 5, backgroundColor: COLORS.primary, alignItems: 'center', justifyContent: 'center', borderWidth: 2, borderColor: COLORS.canvas },
  dotText: { fontFamily: FONTS.bold, fontSize: 10.5, color: COLORS.white },
  label: { fontFamily: FONTS.medium, fontSize: 12.5, color: COLORS.black },
});
