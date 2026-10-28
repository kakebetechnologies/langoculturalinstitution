// Root layout: loads the fonts (Clarity City for the interface, Inter for Leb Lango letters), keeps the splash screen up until the app is ready,
// provides language and sign-in state to every screen, starts push and the offline-contribution sync, and defines the stack.
import { useEffect, useState } from 'react';
import { View } from 'react-native';
import { Stack } from 'expo-router';
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
import { I18nProvider } from '../lib/i18n';
import { useOfflineSync } from '../lib/outbox';
import { usePushSetup } from '../lib/push';

SplashScreen.preventAutoHideAsync().catch(() => {});

function Navigator({ fontsLoaded }) {
  const { ready, user, refresh } = useAuth();
  usePushSetup(user);
  useOfflineSync(user, () => refresh());

  useEffect(() => {
    if (fontsLoaded && ready) SplashScreen.hideAsync().catch(() => {});
  }, [fontsLoaded, ready]);

  if (!fontsLoaded) return null;

  return (
    <View style={{ flex: 1, backgroundColor: COLORS.canvas }}>
      <StatusBar style="dark" />
      <Stack screenOptions={{ headerShown: false, contentStyle: { backgroundColor: COLORS.canvas } }} />
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
    <I18nProvider>
      <AuthProvider>
        <Navigator fontsLoaded={fontsLoaded} />
      </AuthProvider>
    </I18nProvider>
  );
}
