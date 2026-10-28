// A big rounded tile on a vivid gradient with a translucent icon badge, a white title and a line under it: the feature grids of the
// Explore tab (the FaithFellows "Community" look). `tone` picks a gradient from GRADIENTS; `full` makes it span the whole row.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, FONTS, GRADIENTS, RADIUS, SHADOW } from '../constants/theme';
import AppText from './AppText';

export default function GradientTile({ icon, title, text, tone = 'red', onPress, full = false, badge = 0, style }) {
  return (
    <Pressable onPress={onPress} accessibilityRole="button" accessibilityLabel={title} style={({ pressed }) => [styles.wrap, full && styles.full, pressed && { opacity: 0.92, transform: [{ scale: 0.985 }] }, style]}>
      <LinearGradient colors={GRADIENTS[tone] ?? GRADIENTS.red} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.fill}>
        <View style={styles.badge}>
          <Ionicons name={icon} size={22} color={COLORS.white} />
          {badge > 0 ? (
            <View style={styles.dot}>
              <AppText allowFontScaling={false} style={styles.dotText}>
                {badge > 9 ? '9+' : badge}
              </AppText>
            </View>
          ) : null}
        </View>
        <View style={{ marginTop: 'auto' }}>
          <AppText style={styles.title} numberOfLines={2}>
            {title}
          </AppText>
          {text ? (
            <AppText style={styles.text} numberOfLines={2}>
              {text}
            </AppText>
          ) : null}
        </View>
      </LinearGradient>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  wrap: { width: '48%', flexGrow: 1, borderRadius: RADIUS.xl, ...SHADOW },
  full: { width: '100%' },
  fill: { minHeight: 150, borderRadius: RADIUS.xl, padding: 16 },
  badge: { width: 46, height: 46, borderRadius: 15, backgroundColor: 'rgba(255,255,255,0.22)', alignItems: 'center', justifyContent: 'center' },
  dot: { position: 'absolute', top: -5, right: -5, minWidth: 20, height: 20, borderRadius: 10, paddingHorizontal: 5, backgroundColor: COLORS.yellow, alignItems: 'center', justifyContent: 'center' },
  dotText: { fontFamily: FONTS.bold, fontSize: 11, color: COLORS.navy },
  title: { fontFamily: FONTS.bold, fontSize: 17, lineHeight: 22, color: COLORS.white },
  text: { fontFamily: FONTS.medium, fontSize: 12.5, lineHeight: 17, color: 'rgba(255,255,255,0.86)', marginTop: 2 },
});
