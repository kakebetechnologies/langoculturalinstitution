// The kinds of contribution (the Community Contribution Engine): a title, a short description, an icon and a colour tone.
// `type` is what the website stores; `kind` separates a word from a proverb inside the Language type.
// i18n:all (the phrases in this file are listed for translation by `npm run strings`)
export const CONTRIBUTION_TYPES = [
  { type: 'cultural', icon: 'color-palette-outline', tone: 'red', title: 'Cultural observation', text: 'A ceremony, ritual, dance, craft or custom — a wedding, a clan ritual, a traditional dance.' },
  { type: 'natural', icon: 'leaf-outline', tone: 'sky', title: 'Natural heritage', text: 'A plant, animal, bird or habitat — a rare bird, an endangered tree, a traditional herb.' },
  { type: 'language', kind: 'word', icon: 'chatbubbles-outline', tone: 'gold', title: 'Suggest a word', text: 'Names of animals, places, foods, everyday words — with the English meaning.' },
  { type: 'language', kind: 'proverb', icon: 'ribbon-outline', tone: 'gold', title: 'Add a proverb', text: 'A saying as the elders say it, with its translation and when it is used.' },
  { type: 'oral_history', icon: 'mic-outline', tone: 'red', title: 'Oral history', text: 'A recording of an elder: the history of a clan, a legend, an account of the past.' },
  { type: 'document', icon: 'document-text-outline', tone: 'dark', title: 'Historical document', text: 'A photo of an old letter, photograph or artefact — a colonial-era letter, a traditional tool.' },
  { type: 'heritage_site', icon: 'location-outline', tone: 'sky', title: 'Heritage site', text: 'Photos and the place of a sacred grove, monument, palace site or landmark.' },
];

export const STATUS_TONE = { pending: 'gold', under_review: 'sky', needs_info: 'gold', approved: 'green', published: 'green', rejected: 'red' };

export const WORD_CATS = {
  animal: 'Animals', bird: 'Birds', plant: 'Plants & trees', place: 'Places', name: 'Personal names', food: 'Food & drink', body: 'Body & health', family: 'Family & kinship',
  nature: 'Nature & weather', object: 'Tools & household', culture: 'Culture & ceremony', verb: 'Actions', other: 'Other',
};

export const WORD_POS = { noun: 'Noun', verb: 'Verb', adjective: 'Adjective', adverb: 'Adverb', phrase: 'Phrase / saying', other: 'Other' };
