// Notifications. (1) Push: the phone's Expo push token is registered with the website (POST /api/v1/me/device) so reviewers can tell a
// contributor "your contribution was approved". (2) A local daily reminder to open the word of the day, scheduled on the phone itself.
//
// Push needs a real phone with a development or store build: it is switched off on the web and in Expo Go on Android (Expo removed
// push from Expo Go there, and loading expo-notifications at all would stop the app from opening), where every function here
// quietly does nothing. On Android it also needs Firebase and an EAS project id (see README → Push notifications); until then
// getting a token quietly fails and the app simply has no push. The in-app notification list always works.
import { useEffect } from 'react';
import { Platform } from 'react-native';
import Constants, { ExecutionEnvironment } from 'expo-constants';
import api from './api';
import { getStored, setStored } from './storage';

/** Push needs a phone, and not Expo Go on Android. */
export const pushSupported = Platform.OS === 'ios' || (Platform.OS === 'android' && Constants.executionEnvironment !== ExecutionEnvironment.StoreClient);

let Notifications = null;
let configured = false;
/** The expo-notifications module, loaded only where push works (so Expo Go and the browser never touch it). */
function notifications() {
  if (!pushSupported) return null;
  if (!Notifications) {
    try {
      Notifications = require('expo-notifications');
    } catch {
      return null;
    }
  }
  if (!configured) {
    configured = true;
    Notifications.setNotificationHandler({
      handleNotification: async () => ({ shouldShowBanner: true, shouldShowList: true, shouldPlaySound: false, shouldSetBadge: false }),
    });
  }
  return Notifications;
}

async function pushToken(N) {
  const projectId = Constants.expoConfig?.extra?.eas?.projectId ?? Constants.easConfig?.projectId;
  if (!projectId) return null;
  try {
    return (await N.getExpoPushTokenAsync({ projectId })).data;
  } catch {
    return null;
  }
}

/** Asks permission (once) and registers this phone for push. Resolves { ok, message }. */
export async function enablePush() {
  const N = notifications();
  if (!N) return { ok: false, message: 'Push notifications work in the installed Android and iPhone app. The list of notifications inside the app always works.' };
  try {
    if (Platform.OS === 'android') await N.setNotificationChannelAsync('default', { name: 'Lango App', importance: N.AndroidImportance.DEFAULT });
    let { status } = await N.getPermissionsAsync();
    if (status !== 'granted') status = (await N.requestPermissionsAsync()).status;
    if (status !== 'granted') return { ok: false, message: 'Allow notifications for Lango App in your phone settings.' };
    const token = await pushToken(N);
    if (!token) return { ok: false, message: 'Notifications are not set up on this build yet. The in-app list still works.' };
    await api.post('/me/device', { token, platform: Platform.OS });
    await setStored('push_on', true);
    return { ok: true, message: 'Notifications are on.' };
  } catch {
    return { ok: false, message: 'We could not turn notifications on. Please try again.' };
  }
}

export async function disablePush() {
  if (!pushSupported) return;
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
    if (!userId || !pushSupported) return;
    getStored('push_on', false).then((on) => on && enablePush());
  }, [userId]);
}

/** A notification every morning at 7:00 reminding the member to open the word of the day. */
export async function setDailyReminder(on, title, body) {
  const N = notifications();
  if (!N) return false;
  try {
    await N.cancelAllScheduledNotificationsAsync();
    if (on) {
      const { status } = await N.requestPermissionsAsync();
      if (status !== 'granted') return false;
      await N.scheduleNotificationAsync({ content: { title, body }, trigger: { type: N.SchedulableTriggerInputTypes.DAILY, hour: 7, minute: 0 } });
    }
    await setStored('daily_reminder', on);
    return true;
  } catch {
    return false;
  }
}
