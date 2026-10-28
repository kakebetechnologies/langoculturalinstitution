// Your clan group. A member who chose a clan at sign-up can join the group of people of the same clan: see how many there are and who
// (unless they hide), write to each other, and browse the clan's approved contributions (its archive).
//   Signed out → why and how to join · No clan chosen → go to the profile · Not joined yet → a short explanation and a Join button ·
//   Joined → Messages | Members | Archive.
import { useCallback, useState } from 'react';
import { KeyboardAvoidingView, Platform, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router, useFocusEffect } from 'expo-router';
import { setStatusBarStyle } from 'expo-status-bar';
import AppText from '../components/AppText';
import Button from '../components/Button';
import Card from '../components/Card';
import ClanArchive from '../components/clan/ClanArchive';
import ClanMembers from '../components/clan/ClanMembers';
import ClanMessages from '../components/clan/ClanMessages';
import HeroHeader from '../components/HeroHeader';
import Segmented from '../components/Segmented';
import { BackButton } from '../components/ScreenHeader';
import SignInPrompt from '../components/SignInPrompt';
import StateView from '../components/StateView';
import { COLORS, FONTS, SPACING } from '../constants/theme';
import api, { errorMessage } from '../lib/api';
import { useAuth } from '../lib/auth';
import { useT } from '../lib/i18n';
import { useFetch } from '../lib/useFetch';

export default function ClanGroup() {
  const { t } = useT();
  const { user, refresh } = useAuth();
  const clan = user?.clan;
  const group = useFetch(clan ? `/clans/${clan.id}/group` : '/ping', { enabled: !!clan, cache: false });
  const view = group.data?.data;
  const [tab, setTab] = useState('messages');
  const [joining, setJoining] = useState(false);
  const [problem, setProblem] = useState(null);

  useFocusEffect(
    useCallback(() => {
      setStatusBarStyle('light');
      refresh();
      return () => setStatusBarStyle('dark');
    }, [refresh]),
  );

  async function join() {
    setJoining(true);
    setProblem(null);
    try {
      await api.post(`/clans/${clan.id}/join`, {});
      await Promise.all([group.reload(), refresh()]);
    } catch (e) {
      setProblem(errorMessage(e));
    } finally {
      setJoining(false);
    }
  }

  const left = async () => {
    await Promise.all([group.reload(), refresh()]);
  };

  const joined = !!view?.joined;

  return (
    <View style={styles.screen}>
      <HeroHeader style={styles.hero}>
        <View style={styles.top}>
          <BackButton onDark />
          <View style={{ flex: 1 }}>
            <AppText style={styles.eyebrow}>{t('Clan group')}</AppText>
            <AppText style={styles.title} numberOfLines={1}>
              {clan ? `${clan.name} ${t('clan')}` : t('Your clan group')}
            </AppText>
          </View>
          {joined ? (
            <View style={styles.count}>
              <Ionicons name="people" size={15} color={COLORS.white} />
              <AppText style={styles.countText}>{view.members}</AppText>
            </View>
          ) : null}
        </View>
        {joined ? <Segmented dark value={tab} onChange={setTab} options={[{ value: 'messages', label: t('Messages') }, { value: 'members', label: t('Members') }, { value: 'archive', label: t('Archive') }]} style={styles.tabs} /> : null}
      </HeroHeader>

      {!user ? (
        <View style={styles.pad}>
          <SignInPrompt title={t('Join to see your clan group')} text={t('Create a free account and choose your clan. People of the same clan can join their group, see who is in it and talk together.')} next="/clan-group" />
        </View>
      ) : !clan ? (
        <View style={styles.pad}>
          <Card>
            <View style={styles.icon}>
              <Ionicons name="people" size={28} color={COLORS.primaryDark} />
            </View>
            <AppText variant="heading" style={{ marginTop: SPACING.md }}>
              {t('Choose your clan first')}
            </AppText>
            <AppText style={{ marginTop: 6 }}>
              {user.clan_other ? t('You told us your clan is {clan}. It is not in the register yet. When LCI adds it, your group will open here.', { clan: user.clan_other }) : t('The group is for people of the same clan. Choose yours on your profile.')}
            </AppText>
            <Button title={t('Choose my clan')} onPress={() => router.push('/profile')} style={{ marginTop: SPACING.md }} />
          </Card>
        </View>
      ) : (
        <StateView loading={group.loading && !view} error={group.error} onRetry={group.retry}>
          {view && !joined ? (
            <View style={styles.pad}>
              <Card>
                <View style={styles.icon}>
                  <Ionicons name="people" size={28} color={COLORS.primaryDark} />
                </View>
                <AppText variant="heading" style={{ marginTop: SPACING.md }}>
                  {t('Join the {clan} group', { clan: clan.name })}
                </AppText>
                <AppText style={{ marginTop: 6 }}>{view.members === 1 ? t('1 person has joined so far.') : t('{n} people have joined so far.', { n: view.members })}</AppText>
                {['See how many of your clan are here and who they are', 'Write to your clan and read what they share', 'Browse what your clan has added to the archive'].map((line) => (
                  <View key={line} style={styles.point}>
                    <Ionicons name="checkmark-circle" size={20} color={COLORS.success} />
                    <AppText style={{ flex: 1 }}>{t(line)}</AppText>
                  </View>
                ))}
                <AppText variant="caption" style={{ marginTop: SPACING.md }}>
                  {t('When you join, your name is shown to the others in the group. You can hide your name from the list, or leave, whenever you like.')}
                </AppText>
                {problem ? (
                  <AppText variant="label" style={{ color: COLORS.danger, marginTop: SPACING.sm }}>
                    {problem}
                  </AppText>
                ) : null}
                <Button title={t('Join the group')} onPress={join} loading={joining} style={{ marginTop: SPACING.md }} />
              </Card>
            </View>
          ) : view ? (
            <KeyboardAvoidingView style={{ flex: 1 }} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
              {tab === 'messages' ? <ClanMessages clanId={clan.id} onRead={refresh} /> : null}
              {tab === 'members' ? <ClanMembers clanId={clan.id} clanName={clan.name} hidden={view.hidden} onChanged={group.reload} onLeft={left} /> : null}
              {tab === 'archive' ? <ClanArchive clanId={clan.id} clanName={clan.name} /> : null}
            </KeyboardAvoidingView>
          ) : null}
        </StateView>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  hero: { paddingBottom: SPACING.md },
  top: { flexDirection: 'row', alignItems: 'center', gap: 12 },
  eyebrow: { fontFamily: FONTS.bold, fontSize: 11, letterSpacing: 1.2, textTransform: 'uppercase', color: COLORS.yellow },
  title: { fontFamily: FONTS.bold, fontSize: 21, lineHeight: 27, color: COLORS.white },
  count: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: 'rgba(255,255,255,0.16)', paddingHorizontal: 12, paddingVertical: 7, borderRadius: 999 },
  countText: { fontFamily: FONTS.bold, fontSize: 14, color: COLORS.white },
  tabs: { marginTop: SPACING.md, backgroundColor: 'rgba(255,255,255,0.14)' },
  pad: { padding: SPACING.md },
  icon: { width: 64, height: 64, borderRadius: 22, backgroundColor: COLORS.primaryMist, alignItems: 'center', justifyContent: 'center' },
  point: { flexDirection: 'row', gap: 10, alignItems: 'flex-start', marginTop: 10 },
});
