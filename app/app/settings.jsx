// Settings: interface language (English / Leb Lango), the offline dictionary (download, refresh, remove), notifications (push and a
// daily word-of-the-day reminder), clearing saved pages, a connection check and the app version.
import { useEffect, useRef, useState } from 'react';
import { Pressable, StyleSheet, Switch, View } from 'react-native';
import Constants from 'expo-constants';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import CommunitySwitch from '../components/CommunitySwitch';
import Screen from '../components/Screen';
import SectionHeader from '../components/SectionHeader';
import Segmented from '../components/Segmented';
import TextField from '../components/TextField';
import { COLORS, SPACING } from '../constants/theme';
import api, { API_ROOT, errorMessage, ping, setServerAddress } from '../lib/api';
import { useAuth } from '../lib/auth';
import { useCommunity } from '../lib/community';
import { downloadDictionary, removeOffline, useOfflineDictionary } from '../lib/dictionary';
import { fmtDate } from '../lib/format';
import { useT } from '../lib/i18n';
import { removeDir } from '../lib/localFiles';
import { disablePush, enablePush, setDailyReminder } from '../lib/push';
import { getStored } from '../lib/storage';

export default function Settings() {
  const { t, lang, setLang } = useT();
  const { user, setUser } = useAuth();
  const { info, features } = useCommunity();
  const params = useLocalSearchParams();
  const offline = useOfflineDictionary();
  const [busy, setBusy] = useState('');
  const [note, setNote] = useState('');
  const [push, setPush] = useState(false);
  const [daily, setDaily] = useState(false);
  const [showServer, setShowServer] = useState(__DEV__ || params.server === '1');   // the error screen of a test build links here with ?server=1
  const [server, setServer] = useState('');
  const taps = useRef(0);

  useEffect(() => {
    getStored('server_url', null).then((u) => u && setServer(u.replace(/\/api$/, '')));
    getStored('push_on', false).then(setPush);
    getStored('daily_reminder', false).then(setDaily);
  }, []);

  // A member's community is part of their account (their clan or chiefdom is kept for each); a visitor's is kept on the phone.
  async function changeCommunity(code) {
    setNote('');
    if (user) {
      try {
        const { data } = await api.patch('/me', { community: code });
        setUser(data.data);
      } catch (e) {
        setNote(errorMessage(e));
        return;
      }
    }
    router.replace('/(tabs)');
  }

  async function download() {
    setBusy('dl');
    setNote('');
    try {
      const r = await downloadDictionary();
      setNote(r.unchanged ? t('Your offline dictionary is already up to date.') : t('{n} words saved on this phone.', { n: r.count.toLocaleString('en-US') }));
    } catch (e) {
      setNote(errorMessage(e));
    } finally {
      setBusy('');
    }
  }

  async function togglePush(on) {
    setPush(on);
    if (on) {
      const r = await enablePush();
      setNote(r.message);
      if (!r.ok) setPush(false);
    } else await disablePush();
  }

  async function toggleDaily(on) {
    setDaily(on);
    const ok = await setDailyReminder(on, t('Word of the day'), t('Open Lango App to learn today’s Leb Lango word.'));
    if (!ok) {
      setDaily(false);
      setNote(t('Reminders are not available here, or notifications are blocked in your phone settings.'));
    }
  }

  // Tapping the version line five times reveals the server address, so testers can point the installed app at another website.
  function tapVersion() {
    taps.current += 1;
    if (taps.current >= 5) setShowServer(true);
  }

  async function saveServer(value) {
    const root = await setServerAddress(value);
    setServer(String(value).trim() ? root.replace(/\/api$/, '') : '');
    setNote(t('Now using {a}. Pull down on a screen to reload it.', { a: root }));
  }

  async function check() {
    setBusy('ping');
    try {
      const r = await ping();
      setNote(t('Connected to the Lango website ({ms} ms).', { ms: r.ms }));
    } catch (e) {
      setNote(errorMessage(e));
    } finally {
      setBusy('');
    }
  }

  return (
    <Screen title={t('Settings')}>
      <SectionHeader title={t('Community')} />
      <CommunitySwitch onChange={changeCommunity} style={{ alignSelf: 'flex-start' }} />
      <AppText variant="caption" style={{ marginTop: 8 }}>
        {t('Choose whose culture the app shows: Lango or Acholi. Your clan or chiefdom is kept for each.')}
      </AppText>

      {features.languageSwitch ? (
        <>
          <SectionHeader title={t('Language')} style={{ marginTop: SPACING.lg }} />
          <Segmented value={lang} onChange={setLang} options={[{ value: 'en', label: 'English' }, { value: 'lg', label: 'Leb Lango' }]} />
          <AppText variant="caption" style={{ marginTop: 8 }}>
            {t('Leb Lango text appears where language experts have translated it; everything else stays in English.')}
          </AppText>
        </>
      ) : null}

      <SectionHeader title={t('Offline dictionary')} style={{ marginTop: SPACING.lg }} />
      <Card>
        <AppText variant="subheading">{offline.ready ? t('{n} words saved on this phone', { n: offline.count.toLocaleString('en-US') }) : t('Not downloaded yet')}</AppText>
        <AppText variant="caption" style={{ marginTop: 2, marginBottom: 12 }}>
          {offline.ready ? t('Downloaded {d}. Search works with no internet.', { d: fmtDate(new Date(offline.at).toISOString().slice(0, 10)) }) : t('About {n} words, a few hundred KB. Then search works with no internet, in both directions.', { n: info.code === 'lango' ? '2,800' : '4,900' })}
        </AppText>
        <View style={{ flexDirection: 'row', gap: 10 }}>
          <Button title={offline.ready ? t('Update') : t('Download')} icon="cloud-download-outline" small onPress={download} loading={busy === 'dl'} />
          {offline.ready ? <Button title={t('Remove')} variant="outline" small onPress={removeOffline} /> : null}
        </View>
      </Card>

      <SectionHeader title={t('Notifications')} style={{ marginTop: SPACING.lg }} />
      <Card style={{ gap: 14 }}>
        {features.daily ? (
        <View style={styles.sw}>
          <View style={{ flex: 1 }}>
            <AppText variant="subheading">{t('Daily word reminder')}</AppText>
            <AppText variant="caption">{t('A reminder every morning at 7:00')}</AppText>
          </View>
          <Switch value={daily} onValueChange={toggleDaily} trackColor={{ true: COLORS.primary, false: COLORS.gray300 }} />
        </View>
        ) : null}
        {user ? (
          <View style={styles.sw}>
            <View style={{ flex: 1 }}>
              <AppText variant="subheading">{t('Contribution updates')}</AppText>
              <AppText variant="caption">{t('Tell me when a contribution is approved or needs more information')}</AppText>
            </View>
            <Switch value={push} onValueChange={togglePush} trackColor={{ true: COLORS.primary, false: COLORS.gray300 }} />
          </View>
        ) : null}
      </Card>

      <SectionHeader title={t('Storage and connection')} style={{ marginTop: SPACING.lg }} />
      <View style={{ flexDirection: 'row', gap: 10, flexWrap: 'wrap' }}>
        <Button small variant="outline" title={t('Clear saved pages')} onPress={() => { removeDir('cache'); setNote(t('Saved pages cleared.')); }} />
        <Button small variant="outline" title={t('Check connection')} onPress={check} loading={busy === 'ping'} />
      </View>

      {note ? (
        <AppText variant="label" style={{ marginTop: SPACING.md, color: COLORS.skyDark }}>
          {note}
        </AppText>
      ) : null}

      {showServer ? (
        <>
          <SectionHeader title={t('Server address')} style={{ marginTop: SPACING.lg }} />
          <Card>
            <TextField
              value={server}
              onChangeText={setServer}
              placeholder="192.168.0.167/langoculturalinstitution"
              autoCapitalize="none"
              autoCorrect={false}
              keyboardType="url"
              hint={t('For testing: the address of the Lango website this app talks to. Leave empty to use the built-in address.')}
            />
            <View style={{ flexDirection: 'row', gap: 10, marginTop: 4 }}>
              <Button small title={t('Save')} onPress={() => saveServer(server)} />
              <Button small variant="outline" title={t('Use built-in')} onPress={() => { setServer(''); saveServer(''); }} />
            </View>
          </Card>
        </>
      ) : null}

      <Pressable onPress={tapVersion} accessibilityRole="text">
        <AppText variant="caption" style={{ marginTop: SPACING.xl, textAlign: 'center' }}>
          Lango App {Constants.expoConfig?.version ?? ''}
          {showServer ? `\n${API_ROOT}` : ''}
        </AppText>
      </Pressable>
    </Screen>
  );
}

const styles = StyleSheet.create({
  sw: { flexDirection: 'row', alignItems: 'center', gap: 12 },
});
