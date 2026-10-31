// Moments from Lango: the photographs of dancers, harvest and heritage with their captions and credits. They are bundled with the app. Credits say who took each
// photograph; for the ones supplied to the project the photographer is still being confirmed, and anyone who recognises their own picture is asked to get in touch.
import { StyleSheet, View } from 'react-native';
import { Image } from 'expo-image';
import AppText from '../components/AppText';
import Screen from '../components/Screen';
import { COLORS, RADIUS, SHADOW_SM, SPACING } from '../constants/theme';
import { MOMENTS } from '../constants/moments';
import { useT } from '../lib/i18n';

export default function Moments() {
  const { t } = useT();
  return (
    <Screen title={t('Moments from Lango')} subtitle={t('Dance, harvest and heritage')}>
      {MOMENTS.map((m) => (
        <View key={m.key} style={styles.card}>
          <Image source={m.source} style={styles.photo} contentFit="cover" accessibilityLabel={t(m.caption)} />
          <View style={{ padding: SPACING.md }}>
            <AppText variant="subheading">{t(m.caption)}</AppText>
            <AppText variant="caption" style={{ marginTop: 2 }}>
              {t(m.credit)}
            </AppText>
          </View>
        </View>
      ))}
      <AppText variant="caption" style={{ marginTop: SPACING.sm }}>
        {t('Is one of these pictures yours? Tell us through the contact form so we can credit you, or take it down if you prefer.')}
      </AppText>
    </Screen>
  );
}

const styles = StyleSheet.create({
  card: { backgroundColor: COLORS.white, borderRadius: RADIUS.xl, overflow: 'hidden', marginBottom: SPACING.md, ...SHADOW_SM },
  photo: { width: '100%', aspectRatio: 1.1 },
});
