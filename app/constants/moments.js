// The photographs shown in "Moments from Lango" on Home and on the Moments screen. The captions say only what can be seen. The first five were supplied for the
// project and their photographers are being confirmed (the credit line says so); the historical photograph is by Richard Buchta (died 1894) and is in the public domain.
const SUPPLIED = 'Photograph supplied for this project; the photographer is being confirmed.';

export const MOMENTS = [
  { key: 'dancers-group', caption: 'Dancers in traditional dress', credit: SUPPLIED, source: require('../assets/culture/dancers-group.jpg') },
  { key: 'harvest', caption: 'Gathering the harvest', credit: SUPPLIED, source: require('../assets/culture/harvest.jpg') },
  { key: 'pot-dance', caption: 'A dancer balancing a pot', credit: SUPPLIED, source: require('../assets/culture/pot-dance.jpg') },
  { key: 'warrior-rock', caption: 'Spear, shield and feathered headdress', credit: SUPPLIED, source: require('../assets/culture/warrior-rock.jpg') },
  { key: 'dance-story', caption: 'Performers with a handmade prop', credit: SUPPLIED, source: require('../assets/culture/dance-story.jpg') },
  { key: 'hunter', caption: 'Costume of a hunter', credit: 'Photo: Ngumenawe, via Wikimedia Commons (resized). CC BY-SA 4.0.', source: require('../assets/culture/hunter.jpg') },
  { key: 'first-generation', caption: 'Costumes of the first generation', credit: 'Photo: Ngumenawe, via Wikimedia Commons (resized). CC BY-SA 4.0.', source: require('../assets/culture/first-generation.jpg') },
  { key: 'lira-dance', caption: 'Traditional dance at a cultural gala', credit: 'Photo: Mr HM MOSES, via Wikimedia Commons (resized). CC BY-SA 4.0.', source: require('../assets/culture/lira-dance.jpg') },
  { key: 'abuda-dancers', caption: 'Abuda dancers', credit: 'Photo: Emma Oruk, via Wikimedia Commons (resized). CC BY-SA 4.0.', source: require('../assets/culture/abuda-dancers.jpg') },
  { key: 'chief-buchta', caption: 'A Lango chief in a horned head-dress', credit: 'Photograph by Richard Buchta (died 1894), published in The Living Races of Mankind (1902). Public domain.', source: require('../assets/culture/chief-buchta.jpg'), historical: true },
];

// The pictures of the opening screen (the photograph above the sign-in buttons), in the order they appear. The tall ones come first because the photograph fills the
// top of a phone. `focus` is the part of the picture to keep when it is cropped to the frame, as [across, down] (so a head-dress or a face is not cut off).
const FOCUS = { 'warrior-rock': ['50%', '4%'], hunter: ['50%', '22%'], 'lira-dance': ['34%', '50%'], 'first-generation': ['50%', '25%'], harvest: ['58%', '60%'] };
export const OPENING = ['warrior-rock', 'hunter', 'lira-dance', 'first-generation', 'harvest'].map((k) => ({ ...MOMENTS.find((m) => m.key === k), focus: FOCUS[k] ?? ['50%', '45%'] }));
