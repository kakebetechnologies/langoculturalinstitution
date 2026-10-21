// One line of facts on a detail screen: a round icon, a bold line and a lighter line under it. Pass onPress to make it tappable.
import { Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, FONTS, RADIUS } from '../constants/theme';
import AppText from './AppText';

export default function InfoRow({ icon, title, subtitle, onPress }) {
  const Row = onPress ? Pressable : View;

  return (
    <Row onPress={onPress} style={styles.row} {...(onPress ? { accessibilityRole: 'button' } : {})}>
      <View style={styles.icon}>
        <Ionicons name={icon} size={19} color={COLORS.skyDark} />
      </View>
      <View style={{ flex: 1 }}>
        <AppText style={styles.title}>{title}</AppText>
        {subtitle ? (
          <AppText variant="caption" style={styles.subtitle}>
            {subtitle}
          </AppText>
        ) : null}
      </View>
      {onPress ? <Ionicons name="chevron-forward" size={18} color={COLORS.gray500} /> : null}
    </Row>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingVertical: 8 },
  icon: { width: 42, height: 42, borderRadius: RADIUS.lg, backgroundColor: COLORS.skyMist, alignItems: 'center', justifyContent: 'center' },
  title: { fontFamily: FONTS.semibold, fontSize: 15, lineHeight: 20, color: COLORS.black },
  subtitle: { fontSize: 13, lineHeight: 18, marginTop: 1 },
});
