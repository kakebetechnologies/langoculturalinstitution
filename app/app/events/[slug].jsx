// One event (GET /api/v1/events/{slug}): when and where, the description, and "Register to attend" which returns a ticket code
// (POST /api/v1/events/{slug}/register). Tickets are saved on the phone so the code can be shown at the door with no internet.
import { useEffect, useState } from 'react';
import { StyleSheet, View } from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import AppText from '../../components/AppText';
import Button from '../../components/Button';
import Card from '../../components/Card';
import ClanPicker from '../../components/ClanPicker';
import DetailScreen from '../../components/DetailScreen';
import InfoRow from '../../components/InfoRow';
import RichText from '../../components/RichText';
import TextField from '../../components/TextField';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import api, { errorMessage, fieldErrors } from '../../lib/api';
import { useAuth } from '../../lib/auth';
import { fmtLongDate, fmtTime } from '../../lib/format';
import { useT } from '../../lib/i18n';
import { saveTicket, ticketsFor } from '../../lib/tickets';
import { useFetch } from '../../lib/useFetch';

export default function EventPage() {
  const { t } = useT();
  const { user } = useAuth();
  const { slug } = useLocalSearchParams();
  const { data, loading, error, retry } = useFetch(`/events/${slug}`);
  const e = data?.data;
  const [tickets, setTickets] = useState([]);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({ name: user?.name ?? '', email: user?.email ?? '', phone: user?.phone ?? '', tickets: '1' });
  const [clan, setClan] = useState({ clan_id: user?.clan ? String(user.clan.id) : '', clan_other: user?.clan_other ?? '' });
  const [busy, setBusy] = useState(false);
  const [errors, setErrors] = useState({});
  const [banner, setBanner] = useState('');

  useEffect(() => {
    ticketsFor(slug).then(setTickets);
  }, [slug]);

  async function register() {
    const err = {};
    if (!form.name.trim()) err.name = t('Please enter your name.');
    if (!/^\S+@\S+\.\S+$/.test(form.email.trim())) err.email = t('Please enter a valid email address.');
    if (!clan.clan_id) err.clan_id = t('Please choose your clan.');
    setErrors(err);
    if (Object.keys(err).length) return;
    setBusy(true);
    setBanner('');
    try {
      const { data: res } = await api.post(`/events/${slug}/register`, { name: form.name.trim(), email: form.email.trim(), phone: form.phone.trim() || undefined, tickets: Number(form.tickets) || 1, clan_id: clan.clan_id, clan_other: clan.clan_other });
      await saveTicket({ slug, code: res.data.code, tickets: res.data.tickets, title: e.title, starts_at: e.starts_at, location: e.location, name: form.name.trim() });
      setTickets(await ticketsFor(slug));
      setOpen(false);
    } catch (x) {
      setErrors(fieldErrors(x));
      setBanner(errorMessage(x));
    } finally {
      setBusy(false);
    }
  }

  return (
    <DetailScreen loading={loading} error={error} onRetry={retry} hero={e ? { image: e.image, eyebrow: t('Event'), title: e.title, subtitle: fmtLongDate(e.starts_at), fallback: require('../../assets/welcome/aerial.jpg') } : null}>
      {e ? (
        <>
          <InfoRow icon="calendar-outline" title={fmtLongDate(e.starts_at)} subtitle={`${fmtTime(e.starts_at)}${e.ends_at ? ' – ' + fmtTime(e.ends_at) : ''}`} />
          {e.location ? <InfoRow icon="location-outline" title={e.location} /> : null}
          {e.places_left !== null ? <InfoRow icon="people-outline" title={t('{n} places left', { n: e.places_left })} /> : null}

          {tickets.map((k) => (
            <Card key={k.code} style={styles.ticket}>
              <AppText variant="eyebrow">{t('Your ticket')}</AppText>
              <AppText style={styles.code} selectable>
                {k.code}
              </AppText>
              <AppText variant="caption">
                {k.name} · {t('{n} ticket(s)', { n: k.tickets })}
              </AppText>
              <AppText variant="caption">{t('Show this code at the entrance. It is saved on your phone.')}</AppText>
            </Card>
          ))}

          <View style={{ marginVertical: SPACING.md }}>
            {open ? (
              <Card>
                <AppText variant="heading" style={{ marginBottom: 10 }}>
                  {t('Register to attend')}
                </AppText>
                {banner ? (
                  <AppText variant="label" style={{ color: COLORS.danger, marginBottom: 10 }}>
                    {banner}
                  </AppText>
                ) : null}
                <TextField label={t('Full name')} value={form.name} onChangeText={(v) => setForm((f) => ({ ...f, name: v }))} error={errors.name} />
                <TextField label={t('Email')} value={form.email} onChangeText={(v) => setForm((f) => ({ ...f, email: v }))} error={errors.email} autoCapitalize="none" keyboardType="email-address" />
                <TextField label={t('Phone (optional)')} value={form.phone} onChangeText={(v) => setForm((f) => ({ ...f, phone: v }))} keyboardType="phone-pad" />
                <ClanPicker value={clan} onChange={setClan} error={errors.clan_id} />
                <TextField label={t('Tickets')} value={form.tickets} onChangeText={(v) => setForm((f) => ({ ...f, tickets: v.replace(/\D/g, '').slice(0, 2) }))} keyboardType="number-pad" style={{ width: 120 }} />
                <Button title={t('Get my ticket')} onPress={register} loading={busy} />
              </Card>
            ) : e.registration_open ? (
              <Button title={tickets.length ? t('Register more tickets') : t('Register to attend')} icon="ticket-outline" onPress={() => setOpen(true)} />
            ) : (
              <AppText variant="caption">{e.past ? t('This event has ended.') : t('Registration is closed.')}</AppText>
            )}
          </View>

          <RichText html={e.body_html} />
        </>
      ) : null}
    </DetailScreen>
  );
}

const styles = StyleSheet.create({
  ticket: { marginTop: SPACING.md, borderColor: COLORS.yellow, borderWidth: 2, borderStyle: 'dashed', alignItems: 'center', backgroundColor: COLORS.paper },
  code: { fontFamily: FONTS.serif, fontSize: 34, letterSpacing: 6, color: COLORS.primaryDark, marginVertical: 6 },
});
