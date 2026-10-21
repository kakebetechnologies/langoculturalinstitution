// The frame every screen sits in: a header (back button + title, or a big title on the tab screens), the waiting-contributions
// banner, and either a scrolling body (with pull-to-refresh) or a fixed one. Lists that scroll themselves pass `scroll={false}`.
import { RefreshControl, ScrollView, StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS, SPACING } from '../constants/theme';
import AppText from './AppText';
import ScreenHeader from './ScreenHeader';
import SyncBanner from './SyncBanner';

export default function Screen({ title, subtitle, right, big = false, back = true, scroll = true, onRefresh, refreshing = false, footer, children, contentStyle, padded = true }) {
  const insets = useSafeAreaInsets();
  const body = padded ? [styles.body, contentStyle] : contentStyle;

  return (
    <View style={styles.screen}>
      {big ? (
        <View style={[styles.big, { paddingTop: insets.top + 14 }]}>
          <View style={{ flex: 1 }}>
            <AppText variant="title">{title}</AppText>
            {subtitle ? <AppText variant="caption">{subtitle}</AppText> : null}
          </View>
          {right}
        </View>
      ) : back ? (
        <ScreenHeader title={title} subtitle={subtitle} right={right} />
      ) : (
        <View style={{ height: insets.top }} />
      )}
      <SyncBanner />
      {scroll ? (
        <ScrollView
          style={{ flex: 1 }}
          contentContainerStyle={body}
          keyboardShouldPersistTaps="handled"
          refreshControl={onRefresh ? <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={COLORS.primary} colors={[COLORS.primary]} /> : undefined}
        >
          {children}
        </ScrollView>
      ) : (
        <View style={[{ flex: 1 }, padded && styles.fixed]}>{children}</View>
      )}
      {footer}
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  big: { flexDirection: 'row', alignItems: 'center', gap: 12, paddingHorizontal: SPACING.md, paddingBottom: 10 },
  body: { padding: SPACING.md, paddingBottom: SPACING.xxl },
  fixed: { paddingHorizontal: SPACING.md },
});
