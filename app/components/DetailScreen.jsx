// Frame for detail pages (a clan, a district, a person, an event…): the cover hero on top, then a scrolling body with
// loading / error states for the request that feeds it.
import { ScrollView, StyleSheet, View } from 'react-native';
import { COLORS, SPACING } from '../constants/theme';
import CoverHero from './CoverHero';
import ScreenHeader from './ScreenHeader';
import StateView from './StateView';

export default function DetailScreen({ loading, error, onRetry, hero, children, footer }) {
  return (
    <View style={styles.screen}>
      {loading || error ? <ScreenHeader title="" /> : null}
      <StateView loading={loading} error={error} onRetry={onRetry}>
        <ScrollView style={{ flex: 1 }} contentContainerStyle={{ paddingBottom: SPACING.xxl }}>
          {hero ? <CoverHero {...hero} /> : null}
          <View style={styles.body}>{children}</View>
        </ScrollView>
      </StateView>
      {footer}
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  body: { padding: SPACING.md, gap: 4 },
});
