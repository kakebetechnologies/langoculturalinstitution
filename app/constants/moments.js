// The photographs shown in "Moments from Lango" on Home and on the Moments screen. The captions say only what can be seen. The first five were supplied for the
// project and their photographers are being confirmed (the credit line says so); the historical photograph is by Richard Buchta (died 1894) and is in the public domain.
const SUPPLIED = 'Photograph supplied for this project; the photographer is being confirmed.';

export const MOMENTS = [
  { key: 'dancers-group', caption: 'Dancers in traditional dress', credit: SUPPLIED, source: require('../assets/culture/dancers-group.jpg') },
  { key: 'harvest', caption: 'Gathering the harvest', credit: SUPPLIED, source: require('../assets/culture/harvest.jpg') },
  { key: 'pot-dance', caption: 'A dancer balancing a pot', credit: SUPPLIED, source: require('../assets/culture/pot-dance.jpg') },
  { key: 'warrior-rock', caption: 'Spear, shield and feathered headdress', credit: SUPPLIED, source: require('../assets/culture/warrior-rock.jpg') },
  { key: 'dance-story', caption: 'Performers with a handmade prop', credit: SUPPLIED, source: require('../assets/culture/dance-story.jpg') },
  { key: 'chief-buchta', caption: 'A Lango chief in a horned head-dress', credit: 'Photograph by Richard Buchta (died 1894), published in The Living Races of Mankind (1902). Public domain.', source: require('../assets/culture/chief-buchta.jpg'), historical: true },
];
