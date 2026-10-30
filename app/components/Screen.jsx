// The frame every screen sits in: a header (back button + title, or a big title on the tab screens), the waiting-contributions
// banner, and either a scrolling body (with pull-to-refresh) or a fixed one. Lists that scroll themselves pass `scroll={false}`.
import { RefreshControl, ScrollView, StyleSheet, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS, FONTS, SPACING } from '../constants/theme';
import AppText from './AppText';
import ScreenHeader from './ScreenHeader';
import SyncBanner from './SyncBanner';

export default function Screen({ title, subtitle, right, big = false, back = true, scroll = true, onRefresh, refreshing = false, footer, children, contentStyle, padded = true }) {
  const insets = useSafeAreaInsets();
  const body = padded ? [styles.body, contentStyle] : contentStyle;

  return (
    <View style={styles.screen}>
      {big ? (
        <View style={[styles.big, { paddingTop: insets.top + 12 }]}>
          <View style={styles.side} />
          <View style={{ flex: 1, alignItems: 'center' }}>
            <AppText style={styles.bigTitle} numberOfLines={1}>
              {title}
            </AppText>
            {subtitle ? (
              <AppText variant="caption" numberOfLines={2} style={{ textAlign: 'center' }}>
                {subtitle}
              </AppText>
            ) : null}
          </View>
          <View style={[styles.side, { alignItems: 'flex-end' }]}>{right}</View>
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
  big: { flexDirection: 'row', alignItems: 'center', gap: 8, paddingHorizontal: SPACING.md, paddingBottom: 12 },
  side: { width: 44 },
  bigTitle: { fontFamily: FONTS.bold, fontSize: 21, lineHeight: 27, color: COLORS.black, textAlign: 'center' },
  body: { padding: SPACING.md, paddingBottom: SPACING.xxl },
  fixed: { paddingHorizontal: SPACING.md },
});
