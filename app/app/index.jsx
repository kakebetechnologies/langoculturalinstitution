// Entry route ("/"): waits for the saved session to load, then shows the first-run introduction (never seen it) or the tabs.
// Browsing never needs an account: signing in is asked for only when someone contributes or opens their profile.
import { useEffect, useState } from 'react';
import { ActivityIndicator, View } from 'react-native';
import { Redirect } from 'expo-router';
import { COLORS } from '../constants/theme';
import { useAuth } from '../lib/auth';
import { getStored } from '../lib/storage';

export default function Index() {
  const { ready } = useAuth();
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

  return <Redirect href={onboarded ? '/(tabs)' : '/onboarding'} />;
}
