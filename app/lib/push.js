// Notifications. (1) Push: the phone's Expo push token is registered with the website (POST /api/v1/me/device) so reviewers can tell a
// contributor "your contribution was approved". (2) A local daily reminder to open the word of the day, scheduled on the phone itself.
//
// Push on Android needs Firebase and an EAS project id (see README → Push notifications); until then getting a token quietly
// fails and the app simply has no push. Nothing here runs on the web version, and every function is safe to call.
import { useEffect } from 'react';
import { Platform } from 'react-native';
import Constants from 'expo-constants';
import * as Notifications from 'expo-notifications';
import api from './api';
import { getStored, setStored } from './storage';

const native = Platform.OS !== 'web';

if (native) {
  Notifications.setNotificationHandler({
    handleNotification: async () => ({ shouldShowBanner: true, shouldShowList: true, shouldPlaySound: false, shouldSetBadge: false }),
  });
}

async function pushToken() {
  const projectId = Constants.expoConfig?.extra?.eas?.projectId ?? Constants.easConfig?.projectId;
  if (!projectId) return null;
  try {
    return (await Notifications.getExpoPushTokenAsync({ projectId })).data;
  } catch {
    return null;
  }
}

/** Asks permission (once) and registers this phone for push. Resolves { ok, message }. */
export async function enablePush() {
  if (!native) return { ok: false, message: 'Push notifications are available in the Android and iPhone apps.' };
  try {
    if (Platform.OS === 'android') await Notifications.setNotificationChannelAsync('default', { name: 'Lango App', importance: Notifications.AndroidImportance.DEFAULT });
    let { status } = await Notifications.getPermissionsAsync();
    if (status !== 'granted') status = (await Notifications.requestPermissionsAsync()).status;
    if (status !== 'granted') return { ok: false, message: 'Allow notifications for Lango App in your phone settings.' };
    const token = await pushToken();
    if (!token) return { ok: false, message: 'Notifications are not set up on this build yet. The in-app list still works.' };
    await api.post('/me/device', { token, platform: Platform.OS });
    await setStored('push_on', true);
    return { ok: true, message: 'Notifications are on.' };
  } catch {
    return { ok: false, message: 'We could not turn notifications on. Please try again.' };
  }
}

export async function disablePush() {
  if (!native) return;
  try {
    await api.delete('/me/device');
  } catch {
    // Not registered, or offline.
  }
  await setStored('push_on', false);
}

/** Registers the phone again after sign-in when the member already allowed notifications. Mount once in the signed-in app. */
export function usePushSetup(user) {
  const userId = user?.id ?? null;
  useEffect(() => {
    if (!userId || !native) return;
    getStored('push_on', false).then((on) => on && enablePush());
  }, [userId]);
}

/** A notification every morning at 8:00 reminding the member to open the word of the day. */
export async function setDailyReminder(on, title, body) {
  if (!native) return false;
  try {
    await Notifications.cancelAllScheduledNotificationsAsync();
    if (on) {
      const { status } = await Notifications.requestPermissionsAsync();
      if (status !== 'granted') return false;
      await Notifications.scheduleNotificationAsync({ content: { title, body }, trigger: { type: Notifications.SchedulableTriggerInputTypes.DAILY, hour: 8, minute: 0 } });
    }
    await setStored('daily_reminder', on);
    return true;
  } catch {
    return false;
  }
}
