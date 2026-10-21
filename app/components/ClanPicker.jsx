// "Your clan" on every form: the clans LCI has registered so far (search the list), plus "My clan is not listed" (which asks for the
// name, so LCI can add it) and "I am not sure of my clan". The list is kept on the phone, so it also works with no internet.
// value: { clan_id: '' | '12' | 'other' | 'unknown', clan_other: '' }
import { useMemo } from 'react';
import { useFetch } from '../lib/useFetch';
import { useT } from '../lib/i18n';
import Select from './Select';
import TextField from './TextField';

export default function ClanPicker({ value, onChange, error, otherError, label }) {
  const { t } = useT();
  const { data } = useFetch('/clans/options');
  const options = useMemo(
    () => [
      ...(data?.data ?? []).map((c) => ({ value: String(c.id), label: c.name })),
      { value: 'other', label: t('My clan is not listed'), sub: t('Type its name and we will add it') },
      { value: 'unknown', label: t('I am not sure of my clan') },
    ],
    [data, t],
  );

  return (
    <>
      <Select label={label ?? t('Your clan')} value={value.clan_id} options={options} searchable placeholder={t('Choose your clan…')} error={error} onChange={(v) => onChange({ ...value, clan_id: v })} />
      {value.clan_id === 'other' ? (
        <TextField label={t('Your clan name')} value={value.clan_other} onChangeText={(v) => onChange({ ...value, clan_other: v })} error={otherError} placeholder={t('Type your clan name')} maxLength={120} />
      ) : null}
    </>
  );
}
