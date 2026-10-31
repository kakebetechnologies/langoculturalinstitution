<?php
declare(strict_types=1);

/* Knowledge quizzes about the Lango people. Every question rests on a fact that is stated in the Wikipedia articles "Lango people", "John Akii-Bua", "Jane Aceng",
   "Betty Amongi", "Denis Obua (politician)", "Dorcus Acen" and "Owiny Akullu" (CC BY-SA 4.0), or in this site's own history and district pages; the source is
   shown to the player after each answer. Wrong answers are real but incorrect; nothing here is invented. Contested points (the origin of the Lango, the Luo and
   Ateker links) are left out of the questions on purpose.
   Each question: [question, right answer, [three wrong answers], what to tell the player afterwards, source, source address (or null)]. */
$W = 'Wikipedia: Lango people (CC BY-SA 4.0)';
$WU = 'https://en.wikipedia.org/wiki/Lango_people';
$APP = 'This app: History and Districts pages';

return [
[
 'slug' => 'who-are-the-lango', 'title' => 'Who are the Lango?', 'icon' => 'users', 'sort' => 1,
 'summary' => 'Where the Lango live, the districts of the region and the language.',
 'questions' => [
  ['In which part of Uganda do the Lango people live?', 'North-central Uganda', ['Western Uganda', 'Eastern Uganda', 'South-western Uganda'], 'The Lango are an ethnic group of north-central Uganda.', $W, $WU],
  ['Until 1974 the Lango region was a single district. Into which two districts was it split that year?', 'Apac and Lira', ['Gulu and Kitgum', 'Soroti and Kumi', 'Arua and Moyo'], 'In 1974 the old Lango District was split into Apac and Lira districts; more districts were carved out later.', $W, $WU],
  ['Which of these districts is part of the Lango region?', 'Oyam', ['Gulu', 'Soroti', 'Masaka'], 'The Lango region includes Amolatar, Alebtong, Apac, Dokolo, Kole, Lira, Oyam, Otuke and Kwania districts.', $W, $WU],
  ['What is the Lango language called in Lango?', 'Leb Lango', ['Luganda', 'Ateso', 'Runyoro'], 'Leb Lango means “the Lango language”. It is an Eastern Sudanic language.', $W, $WU],
  ['In November 2024 the Lango took part in a reunion hosted by the Government of Uganda. Of which group of peoples?', 'The Ateker peoples', ['The Bantu kingdoms of Uganda', 'The Luo peoples of Kenya and Tanzania', 'The peoples of the Rwenzori mountains'], 'The reunion of 27–29 November 2024 brought the Lango together with other Ateker peoples.', $W, $WU],
  ['In which year did Lira become a city?', '2020', ['2006', '2012', '2018'], 'Lira Municipality became Lira City on 1 July 2020.', $APP, null],
  ['Which district was created out of Apac in 2018?', 'Kwania', ['Alebtong', 'Otuke', 'Amolatar'], 'Kwania was created from Apac district and began on 1 July 2018.', $APP, null],
  ['Roughly how many people did the 2024 census count in the Lango sub-region?', 'About 2.5 million', ['About 500,000', 'About 1 million', 'About 5 million'], 'The 2024 census counted 2,546,118 people in the sub-region, up from 2,061,694 in 2014.', $APP, null],
 ],
],
[
 'slug' => 'culture-and-belief', 'title' => 'Clans, culture and belief', 'icon' => 'heart', 'sort' => 2,
 'summary' => 'Leadership, clans, dance, belief, work and land.',
 'questions' => [
  ['The Lango Kingdom is headed by a leader with which title?', 'Won Nyaci', ['Kabaka', 'Omukama', 'Emorimor'], 'The clans are united in the Lango Kingdom, headed by the Won Nyaci of Lango. (Kabaka is the title in Buganda, Omukama in Bunyoro and Emorimor among the Iteso.)', $W, $WU],
  ['Roughly how many clans do the Lango have registered?', 'More than 149', ['About 15', 'About 50', 'Over 1,000'], 'The Lango have over 149 registered clans.', $W, $WU],
  ['Wikipedia names two very popular Lango dances. Which pair?', 'Okeme and Abuda', ['Bwola and Larakaraka', 'Kadodi and Baakisimba', 'Okeme and Bwola'], 'Okeme and Abuda are performed at cultural functions, weddings and other ceremonies. Bwola and Larakaraka belong to the Acholi, Kadodi to the Bagisu and Baakisimba to the Baganda.', $W, $WU],
  ['In traditional Lango belief, what is the name of the supreme god?', 'Jok', ['Katonda', 'Kibuka', 'Mulungu'], 'Ancestral cults and belief in the supreme god, Jok, played a large role in traditional Lango religion.', $W, $WU],
  ['In traditional Lango myth, every person is said to have which two things?', 'A guardian spirit and a soul', ['A clan drum and a personal drum', 'A river spirit and a mountain spirit', 'A royal name and a clan name'], 'Each individual has a guardian spirit and a metaphysical soul.', $W, $WU],
  ['What are the main occupations of the Lango people?', 'Hunting, farming and raising livestock', ['Mining and fishing on Lake Victoria', 'Making bark cloth and brewing banana beer', 'Trading salt across the Sahara'], 'Hunting, farming and raising livestock are the primary occupations.', $W, $WU],
  ['Under traditional Lango land tenure, to whom did land that nobody had farmed before belong?', 'The first person or family to till it', ['The Won Nyaci', 'The clan leader (Awitong)', 'Whoever was the strongest'], 'Land was common land: once a family had tilled a piece of land, it belonged to that family and passed to the eldest son.', $W, $WU],
 ],
],
[
 'slug' => 'warriors-and-chiefs', 'title' => 'Warriors and chiefs', 'icon' => 'shield', 'sort' => 3,
 'summary' => 'War leaders, Kabalega and Mwanga, Owiny Akullu and Rwot Ogwangguji.',
 'questions' => [
  ['How were Lango war leaders chosen?', 'By agreement: everyone had to accept him as leader', ['They inherited the post from their fathers', 'The Kabaka appointed them', 'Only one clan could choose them'], 'The Lango army was united under one military leader chosen from the available men, and all had to agree to be led by him.', $W, $WU],
  ['How long did a Lango war leader’s authority last?', 'Until the war was over', ['For life', 'For ten years', 'Until the next harvest'], 'When the war ended the leader and his men went back to their clans and daily work, and he received no special benefits.', $W, $WU],
  ['Which famous military leader is said to have brought the Lango to the land where they live today?', 'Ongora Okubal', ['Agoro Abwango', 'Obol Ario', 'Rwot Ogwangguji'], 'Ongora Okubal brought the Lango to their present land. Opyen succeeded him and was followed by Arim Oroba.', $W, $WU],
  ['Who succeeded Ongora Okubal as military leader?', 'Opyen', ['Arim Oroba', 'Agoro Abwango', 'Owiny Akullu'], 'Opyen succeeded Ongora Okubal; he was followed by Arim Oroba.', $W, $WU],
  ['Which Lango leader led his men against the Banyoro and was killed in Bunyoro?', 'Agoro Abwango', ['Ongora Okubal', 'Opyen', 'Arim Oroba'], 'Agoro Abwango fought the Banyoro and died in Bunyoro.', $W, $WU],
  ['Lango oral literature says the Lango helped two deposed kings cross the River Nile. Which kings?', 'Kabalega of Bunyoro and Mwanga of Buganda', ['Mutesa I of Buganda and Kamurasi of Bunyoro', 'Kabalega of Bunyoro and Kasagama of Toro', 'Mwanga of Buganda and Ntare of Ankole'], 'The soldiers who went to help Kabalega helped him and Mwanga cross the Nile as they retreated.', $W, $WU],
  ['In 2019 a statue was put up in Kamdini sub-county, Oyam, to honour which Lango warrior who resisted colonial rule?', 'Owiny Akullu', ['Agoro Abwango', 'Ongora Okubal', 'Obol Ario'], 'Owiny Akullu fought beside Omukama Kabalega of Bunyoro until Kabalega was captured in 1899.', 'Wikipedia: Owiny Akullu (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/Owiny_Akullu'],
  ['Rwot Icaya Ogwangguji was awarded which honour in 1956?', 'The M.B.E.', ['The Victoria Cross', 'A knighthood', 'The Silver Olympic Order'], 'He received the M.B.E. in the 1956 New Year Honours and retired in December 1957.', $W, $WU],
  ['In which year did Erute county elect Ogwangguji as its county chief?', '1918', ['1875', '1951', '1962'], 'He was born in 1875, became county chief of Erute in 1918 and served until 1951, when he was promoted to Rwot Adwong (senior chief).', $W, $WU],
  ['Rwot Ogwangguji is described as a chief who bridged which two worlds?', '“Old Lango” and “new Lango”', ['Uganda and Kenya', 'The church and the mosque', 'The town and the village'], 'He worked through many changes in twentieth-century Uganda, so he is known as the chief who bridged old Lango and new Lango.', $W, $WU],
 ],
],
[
 'slug' => 'famous-langos', 'title' => 'Famous Langos', 'icon' => 'award', 'sort' => 4,
 'summary' => 'Obote, Akii-Bua, Oyite-Ojok and the public servants of today.',
 'questions' => [
  ['Who led Uganda to independence in 1962 as its Prime Minister?', 'Milton Obote', ['Idi Amin', 'Yoweri Museveni', 'Tito Okello'], 'Apollo Milton Obote was Prime Minister from 1962 to 1966 and President from 1966 to 1971 and from 1980 to 1985.', 'Wikipedia: Milton Obote (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/Milton_Obote'],
  ['In which district was Milton Obote born, at Akokoro village?', 'Apac', ['Gulu', 'Soroti', 'Mbale'], 'Obote was born on 28 December 1925 at Akokoro in Apac.', 'Wikipedia: Milton Obote (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/Milton_Obote'],
  ['What did John Akii-Bua win at the 1972 Olympic Games in Munich?', 'Gold in the 400 metres hurdles, in a world record', ['Silver in the 800 metres', 'Bronze in the 400 metres', 'Gold in the 1500 metres'], 'He became the first Olympic champion from Uganda, running 47.82 seconds.', 'Wikipedia: John Akii-Bua (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/John_Akii-Bua'],
  ['In which lane did Akii-Bua run his world-record 400 metres hurdles final?', 'Lane 1', ['Lane 4', 'Lane 6', 'Lane 8'], 'He won the final from lane 1 in 47.82 seconds, the first man to run the event in under 48 seconds.', 'Wikipedia: John Akii-Bua (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/John_Akii-Bua'],
  ['Which stadium in Lira is named after the Olympic champion John Akii-Bua?', 'Akii Bua Stadium', ['Kiprotich Stadium', 'Cheptegei Arena', 'Inzikuru Grounds'], 'Akii Bua Stadium in Lira bears his name.', 'Wikipedia: John Akii-Bua (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/John_Akii-Bua'],
  ['Which Lango army officer became Chief of Staff of the national army and died in a helicopter crash in 1983?', 'David Oyite-Ojok', ['Idi Amin', 'Tito Okello', 'Bazilio Olara-Okello'], 'Major General David Oyite-Ojok helped lead the 1979 war that removed Idi Amin, and died on 2 December 1983.', 'Wikipedia: David Oyite-Ojok (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/David_Oyite-Ojok'],
  ['Dr. Jane Ruth Aceng trained as which kind of doctor?', 'A paediatrician (children’s doctor)', ['A surgeon', 'An eye specialist', 'A dentist'], 'She holds a Master of Medicine in Paediatrics from Makerere University.', 'Wikipedia: Jane Aceng (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/Jane_Aceng'],
  ['In which year was Dr. Aceng appointed Minister of Health?', '2016', ['2011', '2019', '2021'], 'She was appointed on 6 June 2016, after five years as Director General of Medical Services.', 'Wikipedia: Jane Aceng (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/Jane_Aceng'],
  ['Betty Amongi was first elected to Parliament in 2001 as Woman MP for which district?', 'Apac', ['Lira', 'Kole', 'Gulu'], 'She was first elected for Apac in 2001, re-elected in 2006, and later represented Oyam South.', 'Wikipedia: Betty Amongi (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/Betty_Amongi'],
  ['Dorcus Acen was Woman Member of Parliament for which district in the 11th Parliament?', 'Alebtong', ['Dokolo', 'Otuke', 'Kwania'], 'She won the Alebtong Woman MP seat in the 2021 election.', 'Wikipedia: Dorcus Acen (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/Dorcus_Acen'],
  ['In which year did Denis Hamson Obua take office as Government Chief Whip?', '2022', ['2016', '2019', '2011'], 'He was appointed on 21 July 2022 and took over the office on 16 August 2022.', 'Wikipedia: Denis Obua (politician) (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/Denis_Obua_(politician)'],
  ['Which Lira school did Denis Obua attend for his O-Level and A-Level studies?', 'Lango College', ['King’s College Budo', 'St. Mary’s College Kisubi', 'Ntare School'], 'He studied at Lango College in Lira, passing his Uganda Certificate of Education in 1997 and his Advanced Certificate in 1999.', 'Wikipedia: Denis Obua (politician) (CC BY-SA 4.0)', 'https://en.wikipedia.org/wiki/Denis_Obua_(politician)'],
 ],
],
];
