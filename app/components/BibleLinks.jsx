// Where to read and hear the Lango Bible while its text is not in the app: buttons that open the Bible Society of Uganda's own pages on
// Bible.com (the addresses and the line above them come from the website's Site settings, GET /api/v1/bible). Nothing is copied.
// With no address set it says the Bible is being prepared.
import { Linking, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import AppText from './AppText';
import Button from './Button';
import Card from './Card';
import { COLORS, SPACING } from '../constants/theme';
import { useT } from '../lib/i18n';

export default function BibleLinks({ bible, first = false, style }) {
  const { t } = useT();
  const links = bible?.links ?? [];

  if (!links.length) {
    if (!first) return null;
    return (
      <View style={styles.none}>
        <Ionicons name="book-outline" size={28} color={COLORS.gray500} />
        <AppText variant="subheading">{t('The Lango Bible is being prepared')}</AppText>
        <AppText variant="caption" style={{ textAlign: 'center' }}>
          {t('The text will appear here once it has been added with the permission of those who hold the translation.')}
        </AppText>
      </View>
    );
  }

  return (
    <Card style={style}>
      <AppText variant={first ? 'heading' : 'subheading'}>{first ? t('Read and listen on Bible.com') : t('Also on Bible.com')}</AppText>
      <AppText variant="caption" style={{ marginTop: 4, marginBottom: SPACING.md }}>
        {bible.link_note}
      </AppText>
      <View style={{ gap: 10 }}>
        {links.map((l, i) => (
          <Button
            key={l.key}
            title={l.key === 'nt' ? t('New Testament on Bible.com') : t('Full Bible on Bible.com')}
            icon="open-outline"
            variant={i === 0 ? 'primary' : 'outline'}
            onPress={() => Linking.openURL(l.url).catch(() => {})}
          />
        ))}
      </View>
    </Card>
  );
}

const styles = StyleSheet.create({
  none: { alignItems: 'center', gap: 8, padding: SPACING.xl },
});
