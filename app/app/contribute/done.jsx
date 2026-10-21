// Confirmation after sending a contribution: the tracking code (or "kept on your phone" when there is no internet), a share
// button, and the next steps (track it, contribute another, go home).
import { Share, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router, useLocalSearchParams } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useT } from '../../lib/i18n';

export default function Done() {
  const { t } = useT();
  const insets = useSafeAreaInsets();
  const { code, queued, word } = useLocalSearchParams();
  const isQueued = queued === '1';

  return (
    <View style={[styles.screen, { paddingTop: insets.top + 40, paddingBottom: insets.bottom + 20 }]}>
      <View style={[styles.mark, isQueued && { backgroundColor: COLORS.yellowMist }]}>
        <Ionicons name={isQueued ? 'cloud-upload-outline' : 'checkmark'} size={38} color={isQueued ? COLORS.yellowInk : COLORS.skyDark} />
      </View>
      <AppText variant="title" style={{ textAlign: 'center' }}>
        {isQueued ? t('Saved on your phone') : t('Thank you for contributing!')}
      </AppText>
      <AppText style={{ textAlign: 'center', marginTop: 8 }}>
        {isQueued
          ? t('There is no internet right now. Your contribution will be sent automatically when you are back online.')
          : word
            ? t('Our language team has been notified. Once a reviewer approves it, it joins the dictionary and the translator.')
            : t('Clan leaders and reviewers have been notified. Follow its progress with this code:')}
      </AppText>
      {!isQueued && code ? (
        <View style={styles.code}>
          <AppText style={styles.codeText} selectable>
            {code}
          </AppText>
        </View>
      ) : null}
      <View style={{ alignSelf: 'stretch', gap: 10, marginTop: SPACING.lg }}>
        {!isQueued && code ? <Button title={t('Track status')} onPress={() => router.replace({ pathname: '/contribute/track', params: { code } })} /> : null}
        {!isQueued && code ? (
          <Button
            title={t('Share')}
            variant="outline"
            icon="share-outline"
            onPress={() => Share.share({ message: t('I just contributed to the Lango archive with the Lango App. Tracking code: {code}', { code }) }).catch(() => {})}
          />
        ) : null}
        <Button title={t('Contribute another')} variant={isQueued ? 'primary' : 'outline'} onPress={() => router.replace('/(tabs)/contribute')} />
        <Button title={t('Back home')} variant="ghost" onPress={() => router.replace('/(tabs)')} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas, alignItems: 'center', paddingHorizontal: SPACING.lg },
  mark: { width: 84, height: 84, borderRadius: 42, backgroundColor: COLORS.skyMist, alignItems: 'center', justifyContent: 'center', marginBottom: SPACING.lg },
  code: { marginTop: SPACING.lg, paddingVertical: 14, paddingHorizontal: 26, borderRadius: RADIUS.lg, backgroundColor: COLORS.white, borderWidth: 2, borderColor: COLORS.yellow, borderStyle: 'dashed' },
  codeText: { fontFamily: FONTS.serif, fontSize: 34, letterSpacing: 6, color: COLORS.primaryDark },
});
