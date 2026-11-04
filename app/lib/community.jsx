// Which community the app is showing (Lango, Acholi, and the tribes that follow). The list comes from the website (GET /api/v1/communities): the admin switches
// each community on or off, and only the ones that are on are offered when someone signs in. Everything the website sends (clans or chiefdoms, districts,
// history, the dictionary...) is that community's own; the choice is sent with every request (X-App-Community, see lib/api.js), kept on the phone, and a
// signed-in member's own community (from their account) wins. Switching changes the wording of the app too: the names (Lango to Acholi), the clan words
// (clan to chiefdom) and the leader's title. Only the translator and the word comparison work across communities.
import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';
import { getStored, setStored } from './storage';

// What the app knows before it has asked the website: Lango, which is always there.
export const LANGO = { code: 'lango', name: 'Lango', language: 'Leb Lango', people: 'Lango', register: 'Clan', registers: 'Clans', leader: 'Awitong', region: 'mid-northern Uganda', capital: 'Lira City' };

// Every community has the same features. Two parts are Lango's alone because they are pictures and wording built into the app: the photo strip on Home
// and the Leb Lango interface wording (language experts enter another community's wording in the admin when they have it).
export const featuresFor = (code) => ({ bible: true, radio: true, podcast: true, quizzes: true, library: true, daily: true, species: true, greetings: true, moments: code === 'lango', languageSwitch: code === 'lango' });

// the community in use, readable without React (the API client sends it with every request)
let current = 'lango';
export const getCommunity = () => current;

/** Sentences whose facts are Lango's own, for another community: a neutral sentence, or the community's own region and capital. */
function factsFor(info) {
  const region = info.region || 'Uganda';
  const capital = info.capital || 'the main city';
  return [
    ['Lira City, Northern Uganda', `${capital}, ${region}`],
    ['Districts & Lira City', `Districts & ${capital}`],
    [' and the worked examples of the Practical Lango Grammar', ''],
    ['Unmarked entries come from the original Lango dictionary project and are still being reviewed.', 'Unmarked entries have not been checked by a language reviewer yet.'],
  ];
}

/** The wording of an English phrase for a community: Lango names and clan words become that community's. Lango's own text is returned as it is. */
export function communityWords(text, info) {
  if (!info || info.code === 'lango' || typeof text !== 'string' || text.includes('Kakebe')) return text; // legal and about texts name the app's maker: left alone
  const others = text.includes('Lango') && /(Acholi|compare|Compare)/.test(text);
  if (others) return text; // a phrase that names two communities (the comparison) says what it means
  const holes = [];
  let out = text.replace(/\{\w+\}/g, (m) => `\u0001${holes.push(m) - 1}\u0002`); // placeholders such as {clan} are variables, not words: kept as they are
  for (const [from, to] of factsFor(info)) out = out.split(from).join(to);
  return out
    .replace(/\bLeb Lango\b/g, info.language)
    .replace(/\bLangos\b/g, info.people)
    .replace(/\bLango\b/g, info.name)
    .replace(/\bAwitong\b/g, info.leader)
    .replace(/\bclans\b/g, info.registers.toLowerCase())
    .replace(/\bClans\b/g, info.registers)
    .replace(/\bclan\b/g, info.register.toLowerCase())
    .replace(/\bClan\b/g, info.register)
    .replace(/\u0001(\d+)\u0002/g, (_, i) => holes[Number(i)]);
}

const CommunityContext = createContext({ community: 'lango', info: LANGO, features: featuresFor('lango'), list: [LANGO], languages: [{ code: 'en', name: 'English' }], setCommunity: () => {} });

export function CommunityProvider({ children }) {
  const [community, setState] = useState('lango');
  const [list, setList] = useState([LANGO]);
  const listRef = useRef([LANGO]);
  const wanted = useRef(null);
  listRef.current = list;

  // the saved choice and the saved list first (so the app opens at once, even offline), then the website's current list
  useEffect(() => {
    (async () => {
      const savedList = await getStored('communities', null);
      if (Array.isArray(savedList) && savedList.length) setList(savedList);
      const saved = await getStored('community', 'lango');
      if (savedList?.some?.((c) => c.code === saved) || saved === 'lango') {
        current = saved;
        setState(saved);
      }
      try {
        const { default: api } = await import('./api');
        const { data } = await api.get('/communities', { timeout: 8000 });
        if (Array.isArray(data?.data) && data.data.length) {
          setList(data.data);
          setStored('communities', data.data);
          const pick = data.data.find((c) => c.code === wanted.current) ?? data.data.find((c) => c.code === current) ?? data.data[0]; // a member's own, else the saved one, else the first that is on
          wanted.current = null;
          if (pick.code !== current) {
            current = pick.code;
            setState(current);
            setStored('community', current);
          }
        }
      } catch {
        // offline: the saved list is used
      }
    })();
  }, []);

  const setCommunity = useCallback((code) => {
    if (!listRef.current.some((c) => c.code === code)) {
      wanted.current = code; // asked for before the list has arrived: applied as soon as it does
      return;
    }
    wanted.current = null;
    current = code;
    setState(code);
    setStored('community', code);
  }, []);

  const value = useMemo(() => {
    const info = list.find((c) => c.code === community) ?? list[0] ?? LANGO;
    return { community, info, features: featuresFor(info.code), list, languages: [{ code: 'en', name: 'English' }, ...list.map((c) => ({ code: c.code, name: c.language }))], setCommunity };
  }, [community, list, setCommunity]);
  return <CommunityContext.Provider value={value}>{children}</CommunityContext.Provider>;
}

export const useCommunity = () => useContext(CommunityContext);
