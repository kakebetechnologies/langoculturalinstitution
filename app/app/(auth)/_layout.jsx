// Layout for the signed-out screens (welcome, sign in, create account): redirects into the app when already signed in.
import { Redirect, Stack } from 'expo-router';
import { COLORS } from '../../constants/theme';
import { useAuth } from '../../lib/auth';

export default function AuthLayout() {
  const { ready, token } = useAuth();

  if (ready && token) return <Redirect href="/(tabs)" />;

  return <Stack screenOptions={{ headerShown: false, contentStyle: { backgroundColor: COLORS.canvas } }} />;
}
