// Connect: events with RSVP, the professionals directory, the AI cultural assistant, the contributor leaderboard and news.
// (A moderated community forum and clan groups are planned for a later release.)
import { router } from 'expo-router';
import AppText from '../components/AppText';
import Row from '../components/Row';
import Screen from '../components/Screen';
import SectionHeader from '../components/SectionHeader';
import { useT } from '../lib/i18n';

export default function Connect() {
  const { t } = useT();

  return (
    <Screen title={t('Connect')}>
      <AppText style={{ marginBottom: 16 }}>{t('Meet the Lango community at home and abroad.')}</AppText>
      <SectionHeader title={t('Community')} />
      <Row icon="calendar-outline" iconTone="red" title={t('Events')} subtitle={t('Festivals, ceremonies and meetings — register and get your ticket')} onPress={() => router.push('/events')} />
      <Row icon="briefcase-outline" iconTone="sky" title={t('Professionals directory')} subtitle={t('Lango professionals by industry and clan, and how to join')} onPress={() => router.push('/professionals')} />
      <Row icon="chatbubble-ellipses-outline" iconTone="gold" title={t('AI cultural assistant')} subtitle={t('Ask about clans, leaders, districts, history and language')} onPress={() => router.push('/assistant')} />
      <Row icon="trophy-outline" iconTone="dark" title={t('Top contributors')} subtitle={t('Who is adding most to the archive')} onPress={() => router.push('/leaderboard')} />
      <Row icon="newspaper-outline" iconTone="dark" title={t('News')} subtitle={t('Announcements from LCI')} onPress={() => router.push('/news')} />
    </Screen>
  );
}
