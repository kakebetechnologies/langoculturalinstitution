// "Use my current location" for a contribution: asks permission once ("while using the app") and resolves
// { lat, lng } rounded to six decimals, or { error } with a sentence the member can read.
import * as Location from 'expo-location';

export async function currentPlace() {
  try {
    const { status } = await Location.requestForegroundPermissionsAsync();
    if (status !== 'granted') return { error: 'Allow location for Lango App in your phone settings, or describe the place in words.' };
    const here = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.Balanced });
    return { lat: Number(here.coords.latitude.toFixed(6)), lng: Number(here.coords.longitude.toFixed(6)) };
  } catch {
    return { error: 'We could not find your location. Check that location is switched on, or describe the place in words.' };
  }
}
