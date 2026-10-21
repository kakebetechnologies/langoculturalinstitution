// White flat card with a hairline border and a very soft shadow: lists, settings groups, facts. Pass onPress to make it tappable.
import { Pressable, StyleSheet, View } from 'react-native';
import { COLORS, RADIUS, SHADOW, SPACING } from '../constants/theme';

export default function Card({ style, children, onPress, ...rest }) {
  if (onPress) {
    return (
      <Pressable onPress={onPress} accessibilityRole="button" style={({ pressed }) => [styles.card, style, pressed && { opacity: 0.9 }]} {...rest}>
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
  card: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1, borderColor: COLORS.line, padding: SPACING.md, ...SHADOW },
});
