// White rounded card with a soft shadow (no border, like FaithFellows): lists, settings groups, facts. Pass onPress to make it tappable.
import { Pressable, StyleSheet, View } from 'react-native';
import { COLORS, RADIUS, SHADOW, SPACING } from '../constants/theme';

export default function Card({ style, children, onPress, ...rest }) {
  if (onPress) {
    return (
      <Pressable onPress={onPress} accessibilityRole="button" style={({ pressed }) => [styles.card, style, pressed && { opacity: 0.92, transform: [{ scale: 0.99 }] }]} {...rest}>
        {children}
      </Pressable>
    );
  }
  return (
    <View style={[styles.card, style]} {...rest}>
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg, padding: SPACING.md, ...SHADOW },
});
