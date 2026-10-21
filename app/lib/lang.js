// The language the app is showing right now ('en' or 'lg' for Leb Lango). A plain module so the API client can send it
// with every request (the server then answers the assistant in the same language) without importing React.
let current = 'en';

export const getLang = () => current;
export const setLangValue = (lang) => {
  current = lang === 'lg' ? 'lg' : 'en';
};
