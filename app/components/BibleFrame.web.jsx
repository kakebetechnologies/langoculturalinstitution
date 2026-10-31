// One chapter of the Lango Bible in the web version of the app. Bible.com cannot be shown inside a frame on another site, so here the chapter opens at the text itself in
// its own tab (no page that offers to download the app); this card is only seen if someone arrives on the reading screen directly. On phones the same chapter is shown
// inside the app (components/BibleFrame.native.jsx).
import { Linking, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import AppText from './AppText';
import Button from './Button';
import { COLORS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';

export default function BibleFrame({ url, title, style }) {
  const { t } = useT();
  return (
    <View style={[styles.box, style]}>
      <Ionicons name="book-outline" size={34} color={COLORS.primary} />
      <AppText variant="subheading">{title}</AppText>
      <AppText variant="caption" style={{ textAlign: 'center' }}>
        {t('On the web the chapter opens in its own tab, straight at the text.')}
      </AppText>
      <Button title={t('Open this chapter')} icon="open-outline" onPress={() => Linking.openURL(url).catch(() => {})} />
    </View>
  );
}

const styles = StyleSheet.create({
  box: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: 10, padding: SPACING.xl, backgroundColor: COLORS.white },
});
