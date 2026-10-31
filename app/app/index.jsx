// Entry route ("/"): waits for the saved session to load, then goes to the tabs when someone is signed in, to the first-run introduction
// when they have never seen it, and otherwise to the welcome screen: the app needs an account for everything else.
import { useEffect, useState } from 'react';
import { ActivityIndicator, View } from 'react-native';
import { Redirect } from 'expo-router';
import { COLORS } from '../constants/theme';
import { useAuth } from '../lib/auth';
import { getStored } from '../lib/storage';

export default function Index() {
  const { ready, token } = useAuth();
  const [onboarded, setOnboarded] = useState(null);

  useEffect(() => {
    getStored('onboarded_v1', false).then(setOnboarded);
  }, []);

  if (!ready || onboarded === null) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: COLORS.canvas }}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  return <Redirect href={token ? '/(tabs)' : onboarded ? '/(auth)/welcome' : '/onboarding'} />;
}
