<?php
declare(strict_types=1);

/* Clans, leaders, programmes, heritage and tourism entries. */
return [

/* Clan names confirmed in published sources (Boston University study of Lango origins; Wikipedia, Lango people),
   plus Otengoro Olang supplied by LCI. NOT a complete register: LCI has 149 registered clans.
   Add the rest from Admin → Clans → Quick add. */
'clans' => [
 ['Otengoro Olang', 'Supplied by the Lango Cultural Institution.'],
 ['Atek', 'Named as a Lango clan in published research on Lango origins; also found among Ateker peoples.'],
 ['Okarowok', 'Named as a Lango clan in published research on Lango origins; also found among Ateker peoples.'],
 ['Arak', 'Named as a Lango clan in published research; also found among Ateker peoples.'],
 ['Oki', 'Named as a Lango clan in published biographical sources.'],
],

/* Cabinet / council slots with silhouette portraits. Names are placeholders to be replaced from the admin panel. */
'leaders' => [
 ['grp' => 'cabinet', 'name' => 'Name to be added', 'title' => 'Prime Minister', 'photo' => 'assets/img/people/avatar-1.jpg', 'sort' => 1,
  'bio' => 'Chief administrative officer overseeing daily operations, coordinating ministries and implementing the institution\'s programmes.'],
 ['grp' => 'cabinet', 'name' => 'Name to be added', 'title' => 'Minister of Culture and Heritage', 'photo' => 'assets/img/people/avatar-2.jpg', 'sort' => 2,
  'bio' => 'Preserving and promoting Lango traditions, cultural events, traditional dances and heritage conservation.'],
 ['grp' => 'cabinet', 'name' => 'Name to be added', 'title' => 'Minister of Education and Youth', 'photo' => 'assets/img/people/avatar-3.jpg', 'sort' => 3,
  'bio' => 'Coordinating scholarship programmes, educational partnerships and youth development initiatives across Lango.'],
 ['grp' => 'elders', 'name' => 'Name to be added', 'title' => 'Chair, Council of Elders', 'photo' => 'assets/img/people/avatar-4.jpg', 'sort' => 1,
  'bio' => 'Guardian of custom and adviser to the Won Nyaci on matters of culture, clan relations and conflict resolution.'],
],

'programs' => [
 ['Youth Mentorship Program', 'youth', 'Skill-building, leadership training and scholarships that help young people achieve their potential while staying connected to their culture.', 55, 0],
 ['Community Health & Livelihood', 'development', 'Health, livelihood and infrastructure projects that promote unity and growth in every Lango district.', 40, 0],
],

/* Heritage: dances and tourism sites (facts from published sources; descriptions are deliberately general). */
'heritage' => [
 // updates existing Okeme entry
 'okeme' => [
  'summary' => 'A Lango folk dance and thumb-piano tradition, performed at weddings, introduction ceremonies and graduations.',
  'body' => "Okeme is a Lango folk dance, usually performed at weddings, cultural introduction ceremonies and graduation ceremonies. Dancers leap and stamp while singing to the crowd, and guests are invited to join in. The music is built around the okeme, a thumb piano.\n\nAccounts of Lango music also describe okeme competitions between villages, held at certain seasons of the year. Okeme is one of the dances that the Lango Cultural Institution safeguards through its cultural preservation programme.\n\nElders and performers: help us document the songs, instruments and costume of Okeme by contributing recordings and photographs.",
 ],
 'new' => [
  ['category' => 'dance', 'title' => 'Ikoce', 'slug' => 'ikoce', 'image' => 'assets/img/photos/hero-field.jpg', 'summary' => 'A leaping men\'s dance with unison movement and octave singing, led by a drum-chime player.',
   'body' => "Ikoce is a leaping dance performed by a team of men. Dancers move in precise unison and sing in octaves, led by a player of a drum-chime. Its stamping and leaping resemble dances of the related cattle-keeping peoples of eastern Uganda.\n\nIt is reported to be danced at particular seasons in competitions between villages."],
  ['category' => 'dance', 'title' => 'Myel me bilo', 'slug' => 'myel-me-bilo', 'image' => 'assets/img/photos/hero-site.jpg', 'summary' => 'A flute-accompanied dance recorded by the early ethnographer Driberg in 1923.',
   'body' => "Myel me bilo was documented by the ethnographer J. H. Driberg in his 1923 study of the Lango. Dancers stand with their legs together, hold sticks and leap in unison to the music of flutes, and both men and women took part.\n\nPhotographs from Driberg's work are held by the Museum of Archaeology and Anthropology at Cambridge."],
  ['category' => 'site', 'title' => 'Ngetta Rock (Got Ngetta)', 'slug' => 'ngetta-rock', 'image' => 'assets/img/photos/hero-ridge.jpg', 'district' => 'lira-city', 'summary' => 'A massive rock formation rising above the landscape at Lira, one of the city\'s best-known landmarks.',
   'body' => "Ngetta Rock, known locally as Got Ngetta, is a large rock outcrop rising above the surrounding landscape in Lira. It is one of the city's most recognisable natural landmarks and a popular place for walks and views over the town.\n\nVisitors usually combine it with the Lango Cultural Centre, Obote Avenue and the city's markets."],
  ['category' => 'site', 'title' => 'Lango Cultural Centre, Lira', 'slug' => 'lango-cultural-centre', 'image' => 'assets/img/photos/hero-aerial.jpg', 'district' => 'lira-city', 'summary' => 'A meeting place of the Lango traditional institution, used for cultural events and gatherings.',
   'body' => "The Lango Cultural Centre in Lira is a gathering place linked to the Lango traditional leadership, where meetings, ceremonies and cultural events take place.\n\nCheck with LCI for the dates of festivals and public events before you visit."],
  ['category' => 'site', 'title' => 'Obote ancestral home and monument, Akokoro', 'slug' => 'obote-ancestral-home', 'image' => 'assets/img/photos/hero-hill.jpg', 'district' => 'apac', 'summary' => 'The birthplace of Dr. Milton Obote at Akokoro in Apac, a national heritage site.',
   'body' => "Dr. Apollo Milton Obote was born at Akokoro village in Apac in 1925. His ancestral home and monument there are recognised as a national heritage site and are managed in partnership with the Uganda Tourism Board.\n\nThe site is a place to reflect on the history of independence and on the Lango contribution to the story of Uganda."],
  ['category' => 'site', 'title' => 'Amolatar Monument – the centre of Uganda', 'slug' => 'amolatar-monument', 'image' => 'assets/img/photos/hero-road.jpg', 'district' => 'amolatar', 'summary' => 'A monument marking the geographic centre of Uganda.',
   'body' => "The Amolatar Monument marks the geographic centre of Uganda. It is the best-known visitor attraction in Amolatar District, which lies on the shores of Lake Kyoga and Lake Kwania.\n\nCombine a visit with the lakeshore landing sites, where fishers land and sell their catch."],
  ['category' => 'site', 'title' => 'Lake Kwania and the Lake Kyoga wetlands', 'slug' => 'lake-kwania-kyoga', 'image' => 'assets/img/photos/hero-aerial.jpg', 'district' => 'kwania', 'summary' => 'The lakes and papyrus wetlands on the southern edge of Lango: fishing, birdlife and quiet scenery.',
   'body' => "The southern edge of Lango meets the Lake Kyoga basin. Lake Kwania, which touches Kwania, Dokolo, Amolatar and Apac districts, is a shallow lake fringed with papyrus wetlands. Fishing communities live along its shores, and the wetlands are home to many water birds.\n\nVisit landing sites early in the morning when the boats come in."],
  ['category' => 'site', 'title' => 'Akii Bua Stadium, Lira', 'slug' => 'akii-bua-stadium', 'image' => 'assets/img/photos/hero-site.jpg', 'district' => 'lira-city', 'summary' => 'Lira\'s stadium, named after Olympic champion John Akii-Bua.',
   'body' => "Akii Bua Stadium in Lira is named after John Akii-Bua, who won the 400 m hurdles at the 1972 Munich Olympics in a world-record time of 47.82 seconds. It hosts sports and public events for the city."],
 ],
],
];
