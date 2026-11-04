// Root layout: loads the fonts (Clarity City for the interface, Inter for Leb Lango letters), keeps the splash screen up until the app is ready,
// provides language and sign-in state to every screen, starts push and the offline-contribution sync, and defines the stack.
// Everyone must sign in first: until there is a saved session only the sign-in screens, the introduction, the legal pages and Settings open.
import { useEffect, useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { Stack, useRouter, useSegments } from 'expo-router';
import * as SplashScreen from 'expo-splash-screen';
import { StatusBar } from 'expo-status-bar';
// Each weight is imported from its own folder so only the eight fonts the app uses are bundled (not every weight of both families).
import { Inter_400Regular } from '@expo-google-fonts/inter/400Regular';
import { Inter_500Medium } from '@expo-google-fonts/inter/500Medium';
import { Inter_600SemiBold } from '@expo-google-fonts/inter/600SemiBold';
import { Inter_700Bold } from '@expo-google-fonts/inter/700Bold';
import { ClarityCity_400Regular } from '@expo-google-fonts/clarity-city/400Regular';
import { ClarityCity_500Medium } from '@expo-google-fonts/clarity-city/500Medium';
import { ClarityCity_600SemiBold } from '@expo-google-fonts/clarity-city/600SemiBold';
import { ClarityCity_700Bold } from '@expo-google-fonts/clarity-city/700Bold';
import { useFonts } from 'expo-font';
import { COLORS } from '../constants/theme';
import { loadServerAddress } from '../lib/api';
import { AuthProvider, useAuth } from '../lib/auth';
import { CommunityProvider } from '../lib/community';
import { I18nProvider } from '../lib/i18n';
import { useOfflineSync } from '../lib/outbox';
import { usePushSetup } from '../lib/push';

SplashScreen.preventAutoHideAsync().catch(() => {});

// Reachable without an account. The entry route ("/") redirects by itself.
const OPEN_ROUTES = ['(auth)', 'onboarding', 'legal', 'settings'];

function Navigator({ fontsLoaded }) {
  const { ready, token, user, refresh } = useAuth();
  const router = useRouter();
  const first = useSegments()[0] ?? '';
  // Signed in means a saved session (the token), so a member whose phone is offline is not locked out while the profile reloads.
  const locked = ready && !token && first !== '' && !OPEN_ROUTES.includes(first);
  usePushSetup(user);
  useOfflineSync(user, () => refresh());

  useEffect(() => {
    if (fontsLoaded && ready) SplashScreen.hideAsync().catch(() => {});
  }, [fontsLoaded, ready]);

  // Signed out (or never signed in) while on a screen that needs an account: back to the welcome screen.
  useEffect(() => {
    if (locked) router.replace('/(auth)/welcome');
  }, [locked, router]);

  if (!fontsLoaded) return null;

  return (
    <View style={{ flex: 1, backgroundColor: COLORS.canvas }}>
      <StatusBar style="dark" />
      <Stack screenOptions={{ headerShown: false, contentStyle: { backgroundColor: COLORS.canvas } }} />
      {locked ? <View pointerEvents="none" style={[StyleSheet.absoluteFill, { backgroundColor: COLORS.canvas }]} /> : null}
    </View>
  );
}

export default function RootLayout() {
  // A tester's server address (Settings) is read before anything asks the website for data; the splash screen stays up meanwhile.
  const [addressReady, setAddressReady] = useState(false);
  useEffect(() => {
    loadServerAddress().finally(() => setAddressReady(true));
  }, []);
  const [fontsLoaded] = useFonts({ Inter_400Regular, Inter_500Medium, Inter_600SemiBold, Inter_700Bold, ClarityCity_400Regular, ClarityCity_500Medium, ClarityCity_600SemiBold, ClarityCity_700Bold });

  if (!addressReady) return null;

  return (
    <CommunityProvider>
      <I18nProvider>
        <AuthProvider>
          <Navigator fontsLoaded={fontsLoaded} />
        </AuthProvider>
      </I18nProvider>
    </CommunityProvider>
  );
}
