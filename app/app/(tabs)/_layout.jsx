// Bottom tabs: Home, Explore, Contribute (raised red button), Learn and Me. Open to everyone: signed-out visitors can browse,
// and are asked to sign in only when they contribute or open their profile.
import { Tabs } from 'expo-router';
import Ionicons from '@expo/vector-icons/Ionicons';
import AppTabBar from '../../components/AppTabBar';
import { COLORS } from '../../constants/theme';
import { useT } from '../../lib/i18n';

const icon = (name, focusedName) =>
  function TabIcon({ color, size, focused }) {
    return <Ionicons name={focused ? focusedName : name} size={size} color={color} />;
  };

export default function TabsLayout() {
  const { t } = useT();

  return (
    <Tabs tabBar={(props) => <AppTabBar {...props} />} screenOptions={{ headerShown: false, sceneStyle: { backgroundColor: COLORS.canvas } }}>
      <Tabs.Screen name="index" options={{ title: t('Home'), tabBarIcon: icon('home-outline', 'home') }} />
      <Tabs.Screen name="explore" options={{ title: t('Explore'), tabBarIcon: icon('compass-outline', 'compass') }} />
      <Tabs.Screen name="contribute" options={{ title: t('Contribute'), tabBarIcon: icon('add', 'add') }} />
      <Tabs.Screen name="learn" options={{ title: t('Learn'), tabBarIcon: icon('book-outline', 'book') }} />
      <Tabs.Screen name="me" options={{ title: t('Me'), tabBarIcon: icon('person-outline', 'person') }} />
    </Tabs>
  );
}
