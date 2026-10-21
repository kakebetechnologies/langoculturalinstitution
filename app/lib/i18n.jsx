// English / Leb Lango for the whole interface. Every phrase in the app is written in English inside t('...'); when the member
// switches to Leb Lango, t() looks the phrase up in the translations that language experts enter in the website's admin
// panel (Admin → Translations) and falls back to English for anything not translated yet. No Leb Lango is invented here.
//
// The app's phrases are listed in lib/strings.json (built by `npm run strings`) and sent to the website once per version, so they
// show up in that admin screen ready to translate.
import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import api from './api';
import { setLangValue } from './lang';
import { readJson, writeJson } from './localFiles';
import STRINGS from './strings.json';
import { getStored, setStored } from './storage';

const I18nContext = createContext({ lang: 'en', setLang: () => {}, t: (s) => s });

const hash = (s) => {
  let h = 5381;
  for (let i = 0; i < s.length; i++) h = ((h << 5) + h + s.charCodeAt(i)) | 0;
  return (h >>> 0).toString(36);
};

export function I18nProvider({ children }) {
  const [lang, setLangState] = useState('en');
  const [dict, setDict] = useState({});

  useEffect(() => {
    (async () => {
      const saved = await getStored('lang', 'en');
      setLangValue(saved);
      setLangState(saved === 'lg' ? 'lg' : 'en');
      const cached = await readJson('i18n.json');
      if (cached) setDict(cached);
      try {
        const { data } = await api.get('/i18n', { timeout: 8000 });
        if (data?.data) {
          setDict(data.data);
          writeJson('i18n.json', data.data);
        }
        // Hand the app's phrases to the website once per version, so they can be translated there.
        const version = hash(STRINGS.join('\n'));
        if ((await getStored('strings_version')) !== version && STRINGS.length) {
          for (let i = 0; i < STRINGS.length; i += 300) await api.post('/i18n/register', { keys: STRINGS.slice(i, i + 300) }, { timeout: 15000 });
          await setStored('strings_version', version);
        }
      } catch {
        // Offline: the cached translations (or English) are used.
      }
    })();
  }, []);

  const setLang = useCallback((next) => {
    const value = next === 'lg' ? 'lg' : 'en';
    setLangValue(value);
    setLangState(value);
    setStored('lang', value);
  }, []);

  const t = useCallback(
    (key, vars) => {
      let out = lang === 'lg' && dict[key] ? dict[key] : key;
      if (vars) for (const [k, v] of Object.entries(vars)) out = out.split(`{${k}}`).join(String(v));
      return out;
    },
    [lang, dict],
  );

  const value = useMemo(() => ({ lang, setLang, t }), [lang, setLang, t]);

  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>;
}

export const useT = () => useContext(I18nContext);
