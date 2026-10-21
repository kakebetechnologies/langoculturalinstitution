// Shared loading / error / empty states. Every screen wraps its content in <StateView> so
// the three states look and behave the same everywhere.
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { COLORS, RADIUS, SPACING } from '../constants/theme';
import { isConnectionMessage } from '../lib/errors';
import { useT } from '../lib/i18n';
import AppText from './AppText';
import Button from './Button';

export default function StateView({
  loading = false,
  error = null,
  empty = false,
  onRetry,
  emptyIcon = 'file-tray-outline',
  emptyTitle = 'Nothing here yet',
  emptyMessage = '',
  emptyAction,
  children,
}) {
  const { t } = useT();

  if (loading) {
    return (
      <View style={styles.center} accessibilityLabel={t('Loading')}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  if (error) {
    const offline = isConnectionMessage(error);

    return (
      <View style={styles.center}>
        <View style={styles.icon}>
          <Ionicons name="cloud-offline-outline" size={30} color={COLORS.primaryDark} />
        </View>
        <AppText variant="heading" style={styles.title}>
          {offline ? t('No internet connection') : t('Something went wrong')}
        </AppText>
        <AppText style={styles.message}>{error}</AppText>
        {onRetry ? <Button title={t('Try again')} variant="outline" small onPress={onRetry} style={styles.action} /> : null}
      </View>
    );
  }

  if (empty) {
    return (
      <View style={styles.center}>
        <View style={styles.icon}>
          <Ionicons name={emptyIcon} size={30} color={COLORS.primaryDark} />
        </View>
        <AppText variant="heading" style={styles.title}>
          {t(emptyTitle)}
        </AppText>
        {emptyMessage ? <AppText style={styles.message}>{t(emptyMessage)}</AppText> : null}
        {emptyAction ? <Button title={t(emptyAction.title)} variant="outline" small onPress={emptyAction.onPress} style={styles.action} /> : null}
      </View>
    );
  }

  return children;
}

const styles = StyleSheet.create({
  center: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: SPACING.xl, minHeight: 240 },
  icon: { width: 64, height: 64, borderRadius: RADIUS.full, backgroundColor: COLORS.primaryMist, alignItems: 'center', justifyContent: 'center', marginBottom: SPACING.md },
  title: { textAlign: 'center', marginBottom: 4 },
  message: { textAlign: 'center', color: COLORS.gray700 },
  action: { marginTop: SPACING.md },
});
