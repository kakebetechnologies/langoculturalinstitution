// The guided contribution form (Community Contribution Engine), three steps:
//   1. Details    — what you saw or know (title + description, or the word/proverb and its meaning) and your clan
//   2. Media      — where and when (GPS, district, place, date) and one photo, recording, video or document
//   3. Review     — check it, agree to the terms, choose how it may be used, and send
// With no internet the contribution is kept on the phone (lib/outbox.js) and sent automatically later.
import { useMemo, useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, StyleSheet, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { router, useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import Chip from '../../components/Chip';
import ClanPicker from '../../components/ClanPicker';
import KeyboardScroll from '../../components/KeyboardScroll';
import MediaField from '../../components/MediaField';
import ScreenHeader from '../../components/ScreenHeader';
import Select from '../../components/Select';
import SignInPrompt from '../../components/SignInPrompt';
import TextField from '../../components/TextField';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { CONTRIBUTION_TYPES, WORD_CATS, WORD_POS } from '../../constants/contribute';
import { errorMessage, fieldErrors } from '../../lib/api';
import { useAuth } from '../../lib/auth';
import { useT } from '../../lib/i18n';
import { currentPlace } from '../../lib/location';
import { submitContribution } from '../../lib/outbox';
import { useFetch } from '../../lib/useFetch';

const iso = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
const daysAgo = (n) => iso(new Date(Date.now() - n * 86400000));

export default function NewContribution() {
  const { t } = useT();
  const { user } = useAuth();
  const { type: rawType, kind: rawKind, word: rawWord } = useLocalSearchParams();
  const def = CONTRIBUTION_TYPES.find((c) => c.type === rawType && (c.kind ?? '') === (rawKind ?? '')) ?? CONTRIBUTION_TYPES.find((c) => c.type === rawType) ?? CONTRIBUTION_TYPES[0];
  const isWord = def.type === 'language';
  const isProv = def.kind === 'proverb';
  const districts = useFetch('/districts');

  const [step, setStep] = useState(0);
  const [f, setF] = useState({
    title: '', description: '', word: typeof rawWord === 'string' ? rawWord : '', meaning: '', note: '', word_cat: '', word_pos: '', example_lg: '', district_id: '', location_text: '', observed_on: '', lat: null, lng: null,
    rights: 'retain', credit: true,
  });
  const [clan, setClan] = useState({ clan_id: user?.clan ? String(user.clan.id) : user?.clan_other ? 'other' : '', clan_other: user?.clan_other ?? '' });
  const [media, setMedia] = useState(null);
  const [agree, setAgree] = useState(false);
  const [errors, setErrors] = useState({});
  const [gps, setGps] = useState('');
  const [busy, setBusy] = useState(false);
  const [banner, setBanner] = useState('');
  const set = (k) => (v) => setF((x) => ({ ...x, [k]: v }));
  const districtOptions = useMemo(() => [{ value: '', label: t('Not sure / not applicable') }, ...(districts.data?.data ?? []).map((d) => ({ value: String(d.id), label: d.name }))], [districts.data, t]);

  if (!user) {
    return (
      <View style={styles.screen}>
        <ScreenHeader title={t(def.title)} />
        <View style={{ padding: SPACING.md }}>
          <SignInPrompt title={t('Sign in to contribute')} text={t('Contributions are tied to your account so you can follow their review and earn points.')} next="/(tabs)/contribute" />
        </View>
      </View>
    );
  }

  function validate(s) {
    const e = {};
    if (s === 0) {
      if (isWord) {
        if (f.word.trim().length < 2) e.word = isProv ? t('Please write the proverb.') : t('Please write the Leb Lango word.');
        if (!f.meaning.trim()) e.meaning = isProv ? t('Please add the English translation.') : t('Please add the English meaning.');
      } else {
        if (!f.title.trim()) e.title = t('Please add a title.');
        if (f.description.trim().length < 10) e.description = t('Please describe it in at least 10 characters.');
      }
      if (!clan.clan_id) e.clan_id = t('Please choose your clan.');
      if (clan.clan_id === 'other' && !clan.clan_other.trim()) e.clan_other = t('Please type the name of your clan.');
    }
    if (s === 1 && f.observed_on && !/^\d{4}-\d{2}-\d{2}$/.test(f.observed_on)) e.observed_on = t('Use the format YYYY-MM-DD, or choose Today.');
    return e;
  }

  function next() {
    const e = validate(step);
    setErrors(e);
    if (!Object.keys(e).length) setStep((s) => s + 1);
  }

  async function useLocation() {
    setGps(t('Finding your location…'));
    const p = await currentPlace();
    if (p.error) setGps(p.error);
    else {
      setF((x) => ({ ...x, lat: p.lat, lng: p.lng }));
      setGps(t('Location added: {lat}, {lng}', { lat: p.lat.toFixed(4), lng: p.lng.toFixed(4) }));
    }
  }

  async function send() {
    if (!agree) {
      setErrors({ agree: t('Please agree to the terms to send your contribution.') });
      return;
    }
    setBusy(true);
    setBanner('');
    const fields = {
      type: def.type,
      kind: isProv ? 'proverb' : isWord ? 'word' : undefined,
      clan_id: clan.clan_id,
      clan_other: clan.clan_id === 'other' ? clan.clan_other.trim() : undefined,
      ...(isWord
        ? { word: f.word.trim(), meaning: f.meaning.trim(), note: f.note.trim(), word_cat: f.word_cat, word_pos: f.word_pos, example_lg: f.example_lg.trim(), credit: f.credit ? 1 : 0 }
        : { title: f.title.trim(), description: f.description.trim(), district_id: f.district_id, location_text: f.location_text.trim(), observed_on: f.observed_on, lat: f.lat ?? undefined, lng: f.lng ?? undefined, rights: f.rights }),
    };
    try {
      const res = await submitContribution(fields, media);
      router.replace({ pathname: '/contribute/done', params: res.queued ? { queued: '1', word: isWord ? '1' : '' } : { code: res.code, word: isWord ? '1' : '' } });
    } catch (e) {
      const fe = fieldErrors(e);
      if (Object.keys(fe).length) {
        setErrors(fe);
        setStep(fe.word || fe.meaning || fe.title || fe.description || fe.clan_id || fe.clan_other ? 0 : 1);
      }
      setBanner(errorMessage(e));
    } finally {
      setBusy(false);
    }
  }

  const mediaKinds = isWord ? ['audio'] : def.type === 'oral_history' ? ['audio', 'video', 'photo', 'library'] : def.type === 'document' ? ['photo', 'library', 'document'] : ['photo', 'library', 'video', 'audio'];
  const clanName = clan.clan_id === 'other' ? clan.clan_other : clan.clan_id === 'unknown' ? t('Not sure') : user.clan && String(user.clan.id) === clan.clan_id ? user.clan.name : t('Chosen');

  return (
    <KeyboardAvoidingView style={styles.screen} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScreenHeader title={t(def.title)} subtitle={t('Step {n} of 3', { n: step + 1 })} />
      <View style={styles.progress}>
        {[0, 1, 2].map((i) => (
          <View key={i} style={[styles.seg, i <= step && styles.segOn]} />
        ))}
      </View>
      <KeyboardScroll contentContainerStyle={styles.body} keyboardShouldPersistTaps="handled">
        {banner ? (
          <View style={styles.banner} accessibilityRole="alert">
            <AppText variant="label" style={{ color: COLORS.danger }}>
              {banner}
            </AppText>
          </View>
        ) : null}

        {step === 0 ? (
          <>
            <AppText variant="heading" style={{ marginBottom: SPACING.md }}>
              {isProv ? t('The proverb') : isWord ? t('The word') : t('Tell us about it')}
            </AppText>
            {isWord ? (
              <>
                <TextField label={isProv ? t('Proverb in Leb Lango') : t('Leb Lango word')} value={f.word} onChangeText={set('word')} error={errors.word} multiline={isProv} autoCapitalize="none" placeholder={isProv ? '' : t('e.g. the name of an animal, a place, a food')} maxLength={500} />
                <TextField label={isProv ? t('English translation') : t('English meaning')} value={f.meaning} onChangeText={set('meaning')} error={errors.meaning} multiline={isProv} placeholder={isProv ? '' : t('e.g. wild cat')} maxLength={500} />
                {isProv ? (
                  <TextField label={t('What does it mean, and when is it used?')} value={f.note} onChangeText={set('note')} multiline maxLength={500} />
                ) : (
                  <>
                    <Select label={t('What kind of word is it?')} value={f.word_cat} options={[{ value: '', label: t('Not sure') }, ...Object.entries(WORD_CATS).map(([v, l]) => ({ value: v, label: t(l) }))]} onChange={set('word_cat')} />
                    <Select label={t('Part of speech')} value={f.word_pos} options={[{ value: '', label: t('Not sure') }, ...Object.entries(WORD_POS).map(([v, l]) => ({ value: v, label: t(l) }))]} onChange={set('word_pos')} />
                    <TextField label={t('A sentence using the word (optional)')} value={f.example_lg} onChangeText={set('example_lg')} maxLength={500} />
                  </>
                )}
              </>
            ) : (
              <>
                <TextField label={t('Title')} value={f.title} onChangeText={set('title')} error={errors.title} placeholder={t(def.type === 'natural' ? 'e.g. Grey crowned crane at the wetland near Lira' : 'Give it a short, clear title')} maxLength={200} />
                <TextField label={t('What did you see or experience? Who, what, how?')} value={f.description} onChangeText={set('description')} error={errors.description} multiline hint={t('The more detail, the easier it is to verify.')} maxLength={8000} />
              </>
            )}
            <ClanPicker value={clan} onChange={setClan} error={errors.clan_id} otherError={errors.clan_other} label={t('Which clan or community is it connected to?')} />
          </>
        ) : null}

        {step === 1 ? (
          <>
            <AppText variant="heading" style={{ marginBottom: SPACING.md }}>
              {isWord ? t('Add a pronunciation (optional)') : t('Where, when and a photo or recording')}
            </AppText>
            {!isWord ? (
              <>
                <Select label={t('District')} value={f.district_id} options={districtOptions} onChange={set('district_id')} searchable />
                <TextField label={t('Village, parish or place')} value={f.location_text} onChangeText={set('location_text')} maxLength={190} />
                <Button small variant="outline" icon="location-outline" title={f.lat ? t('Update my location') : t('Use my current location (GPS)')} onPress={useLocation} style={{ alignSelf: 'flex-start', marginBottom: 6 }} />
                {gps ? (
                  <AppText variant="caption" style={{ marginBottom: SPACING.md }}>
                    {gps}
                  </AppText>
                ) : (
                  <View style={{ height: SPACING.md }} />
                )}
                <AppText variant="label" style={{ marginBottom: 6 }}>
                  {t('When was it?')}
                </AppText>
                <View style={{ flexDirection: 'row', gap: 8, marginBottom: 8 }}>
                  <Chip label={t('Today')} on={f.observed_on === daysAgo(0)} onPress={() => set('observed_on')(daysAgo(0))} />
                  <Chip label={t('Yesterday')} on={f.observed_on === daysAgo(1)} onPress={() => set('observed_on')(daysAgo(1))} />
                  <Chip label={t('Not sure')} on={f.observed_on === ''} onPress={() => set('observed_on')('')} />
                </View>
                <TextField value={f.observed_on} onChangeText={set('observed_on')} error={errors.observed_on} placeholder="YYYY-MM-DD" keyboardType="numbers-and-punctuation" maxLength={10} />
              </>
            ) : null}
            <AppText variant="label" style={{ marginBottom: 8 }}>
              {isWord ? t('Recording') : t('Photo, recording, video or document')}
            </AppText>
            <MediaField value={media} onChange={setMedia} kinds={mediaKinds} />
          </>
        ) : null}

        {step === 2 ? (
          <>
            <AppText variant="heading" style={{ marginBottom: SPACING.md }}>
              {t('Review and send')}
            </AppText>
            <View style={styles.summary}>
              <AppText variant="eyebrow">{t(def.title)}</AppText>
              <AppText variant="heading" style={{ marginTop: 2 }}>
                {isWord ? f.word : f.title}
              </AppText>
              <AppText style={{ marginTop: 4 }}>{isWord ? f.meaning : f.description}</AppText>
              <View style={styles.meta}>
                <Ionicons name="people-outline" size={16} color={COLORS.gray700} />
                <AppText variant="caption">{clanName}</AppText>
              </View>
              {!isWord && (f.location_text || f.district_id || f.lat) ? (
                <View style={styles.meta}>
                  <Ionicons name="location-outline" size={16} color={COLORS.gray700} />
                  <AppText variant="caption">{[f.location_text, districtOptions.find((o) => o.value === f.district_id)?.label, f.lat ? `${f.lat.toFixed(3)}, ${f.lng.toFixed(3)}` : ''].filter(Boolean).join(' · ')}</AppText>
                </View>
              ) : null}
              {media ? (
                <View style={styles.meta}>
                  <Ionicons name="attach-outline" size={16} color={COLORS.gray700} />
                  <AppText variant="caption">{media.name}</AppText>
                </View>
              ) : null}
            </View>

            {isWord ? (
              <Pressable onPress={() => set('credit')(!f.credit)} style={styles.agree} accessibilityRole="checkbox" accessibilityState={{ checked: f.credit }}>
                <View style={[styles.box, f.credit && styles.boxOn]}>{f.credit ? <AppText style={styles.tick}>✓</AppText> : null}</View>
                <AppText style={{ flex: 1 }}>{t('Show my first name and clan beside it so I get the credit.')}</AppText>
              </Pressable>
            ) : (
              <Select
                label={t('How may we use your contribution?')}
                value={f.rights}
                options={[
                  { value: 'retain', label: t('I keep all rights — LCI may display it in the archive') },
                  { value: 'cc_by', label: t('Share openly with credit (CC BY)') },
                  { value: 'lci', label: t('Give LCI full rights to use it') },
                ]}
                onChange={set('rights')}
              />
            )}

            <Pressable onPress={() => setAgree((a) => !a)} style={styles.agree} accessibilityRole="checkbox" accessibilityState={{ checked: agree }}>
              <View style={[styles.box, agree && styles.boxOn]}>{agree ? <AppText style={styles.tick}>✓</AppText> : null}</View>
              <AppText style={{ flex: 1 }}>
                {t('I confirm this is my own knowledge or work, or I have permission to share it, and I agree to the')}{' '}
                <AppText style={{ color: COLORS.primaryDark, fontFamily: FONTS.bold }} onPress={() => router.push('/legal/terms')}>
                  {t('terms')}
                </AppText>
                .
              </AppText>
            </Pressable>
            {errors.agree ? (
              <AppText variant="caption" style={{ color: COLORS.danger }}>
                {errors.agree}
              </AppText>
            ) : null}
            <AppText variant="caption" style={{ marginTop: SPACING.md }}>
              {t('Reviewers are notified straight away. No internet? It is kept on your phone and sent when you are back online.')}
            </AppText>
          </>
        ) : null}
      </KeyboardScroll>

      <View style={styles.footer}>
        {step > 0 ? <Button title={t('Back')} variant="outline" onPress={() => setStep((s) => s - 1)} style={{ flex: 1 }} /> : null}
        {step < 2 ? <Button title={t('Next')} onPress={next} style={{ flex: 1.4 }} /> : <Button title={t('Send contribution')} icon="paper-plane-outline" onPress={send} loading={busy} style={{ flex: 1.4 }} />}
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1, backgroundColor: COLORS.canvas },
  progress: { flexDirection: 'row', gap: 6, paddingHorizontal: SPACING.md, paddingBottom: 8 },
  seg: { flex: 1, height: 5, borderRadius: 3, backgroundColor: COLORS.gray300 },
  segOn: { backgroundColor: COLORS.primary },
  body: { padding: SPACING.md, paddingBottom: SPACING.xl },
  banner: { backgroundColor: COLORS.primaryMist, borderRadius: RADIUS.md, padding: SPACING.md, marginBottom: SPACING.md },
  summary: { backgroundColor: COLORS.white, borderRadius: RADIUS.lg, borderWidth: 1, borderColor: COLORS.line, padding: SPACING.md, marginBottom: SPACING.md, gap: 4 },
  meta: { flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 6 },
  agree: { flexDirection: 'row', gap: 10, alignItems: 'flex-start', marginBottom: 12 },
  box: { width: 22, height: 22, borderRadius: 6, borderWidth: 2, borderColor: COLORS.gray300, alignItems: 'center', justifyContent: 'center', marginTop: 1, backgroundColor: COLORS.white },
  boxOn: { backgroundColor: COLORS.primary, borderColor: COLORS.primary },
  tick: { color: COLORS.white, fontSize: 13, lineHeight: 15 },
  footer: { flexDirection: 'row', gap: 10, padding: SPACING.md, paddingBottom: SPACING.lg, backgroundColor: COLORS.white, borderTopWidth: 1, borderTopColor: COLORS.line },
});
