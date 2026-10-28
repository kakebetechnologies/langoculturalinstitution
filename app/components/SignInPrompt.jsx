// Shown instead of a screen that needs an account (contributing, the profile): why, and the two ways in.
import { StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router } from 'expo-router';
import { COLORS, RADIUS, SPACING, SHADOW } from '../constants/theme';
import { useT } from '../lib/i18n';
import AppText from './AppText';
import Button from './Button';

export default function SignInPrompt({ title, text, next }) {
  const { t } = useT();
  const params = next ? { next } : {};

  return (
    <View style={styles.box}>
      <View style={styles.icon}>
        <Ionicons name="person-circle-outline" size={34} color={COLORS.primaryDark} />
      </View>
      <AppText variant="heading" style={{ textAlign: 'center', marginTop: SPACING.md }}>
        {title}
      </AppText>
      <AppText style={{ textAlign: 'center', marginTop: 6, marginBottom: SPACING.lg }}>{text}</AppText>
      <Button title={t('Create an account')} onPress={() => router.push({ pathname: '/(auth)/register', params })} style={{ alignSelf: 'stretch' }} />
      <Button title={t('Sign in')} variant="outline" onPress={() => router.push({ pathname: '/(auth)/login', params })} style={{ alignSelf: 'stretch', marginTop: 10 }} />
    </View>
  );
}

const styles = StyleSheet.create({
  box: { alignItems: 'center', backgroundColor: COLORS.white, borderRadius: RADIUS.xl, padding: SPACING.lg, ...SHADOW },
  icon: { width: 68, height: 68, borderRadius: 34, backgroundColor: COLORS.primaryMist, alignItems: 'center', justifyContent: 'center' },
});
