<?php
declare(strict_types=1);

/* More about the notable Langos: licensed portraits, further reading, home districts, a fuller story for rows that were seeded short, and one new person.
   Sources: the Wikipedia articles "Lango people", "Milton Obote", "David Oyite-Ojok", "John Akii-Bua", "Jane Aceng", "Betty Amongi", "Denis Obua (politician)",
   "Owiny Akullu" and "Dorcus Acen" (text CC BY-SA 4.0; written here in our own words and shortened), and the pages listed under 'links', each opened and
   read before it was added. Portraits come only from Wikimedia Commons files whose licence allows reuse (public domain, CC0, CC BY); the credit is shown beside the picture.
   Peter Ogwang is left out on purpose: the Lango people article lists him, but his own biography places him in Katakwi (Teso) and does not call him Lango.
   Roles in politics change after every election, so those stories give dates and say "according to Wikipedia". */
return [

/* slug => [file, credit line, file page on Wikimedia Commons] — only set when the person has no portrait yet */
'photos' => [
    'apollo-milton-obote' => ['assets/img/people/apollo-milton-obote.jpg', 'Photo: Warren K. Leffler, U.S. News & World Report, Library of Congress (cropped). Public domain.', 'https://commons.wikimedia.org/wiki/File:President_John_F._Kennedy_with_Prime_Minister_Milton_Obote_(cropped).jpg'],
    'john-akii-bua' => ['assets/img/people/john-akii-bua.jpg', 'Scan of a 1973/74 “Campioni dello Sport” sticker card, via Wikimedia Commons (cropped). Public domain, author unknown.', 'https://commons.wikimedia.org/wiki/File:Akii-Bua_1972.jpg'],
    'jane-ruth-aceng' => ['assets/img/people/jane-ruth-aceng.jpg', 'Photo: Ssemmanda will, via Wikimedia Commons. CC0 (public domain dedication).', 'https://commons.wikimedia.org/wiki/File:Aceng_Jane_Ruth_Ocero.jpg'],
    'betty-amongi' => ['assets/img/people/betty-amongi.jpg', 'Photo: Kyakwera, 2024, via Wikimedia Commons (cropped). CC BY 4.0.', 'https://commons.wikimedia.org/wiki/File:Hon_Betty_Amongi_Ongom_WE4D_Launch_WE4D_Launch_2024-306_4.jpg'],
    'dorcus-acen' => ['assets/img/people/dorcus-acen.jpg', 'Photo: Ssemmanda will, via Wikimedia Commons. CC0 (public domain dedication).', 'https://commons.wikimedia.org/wiki/File:Acan_Dorcas.jpg'],
],

/* slug => district slug — only set when the person has no district yet */
'districts' => [
    'apollo-milton-obote' => 'apac', 'david-oyite-ojok' => 'lira', 'john-akii-bua' => 'lira-city', 'rwot-icaya-ogwangguji' => 'lira', 'owiny-akullu' => 'oyam',
    'jane-ruth-aceng' => 'oyam', 'betty-amongi' => 'oyam', 'denis-hamson-obua' => 'alebtong', 'dorcus-acen' => 'alebtong',
],

/* slug => [kind, title, publisher, address] — kind: wikipedia | article | profile | documentary | record | research. Every address was opened and its page title read. */
'links' => [
    'apollo-milton-obote' => [
        ['wikipedia', 'Milton Obote', 'Wikipedia', 'https://en.wikipedia.org/wiki/Milton_Obote'],
        ['article', 'Former Ugandan leader Obote dies', 'BBC News', 'http://news.bbc.co.uk/2/hi/africa/4328834.stm'],
        ['article', 'Uganda: Obote’s legacy', 'DW', 'https://www.dw.com/en/milton-obotes-lasting-legacy-to-uganda/a-19191275'],
        ['article', 'Today in history: Obote becomes president', 'New Vision', 'https://www.newvision.co.ug/news/1515957/history-obote-president'],
    ],
    'david-oyite-ojok' => [
        ['wikipedia', 'David Oyite-Ojok', 'Wikipedia', 'https://en.wikipedia.org/wiki/David_Oyite-Ojok'],
        ['article', 'Oyite Ojok, one of Uganda’s best soldiers', 'New Vision', 'https://www.newvision.co.ug/news/1299481/oyite-ojok-uganda-soldiers'],
        ['article', 'Revisiting Gen Oyite Ojok 1983 helicopter crash site', 'The Observer', 'https://observer.ug/news/headlines/76178-revisiting-gen-oyite-ojok-1983-helicopter-crash-site'],
    ],
    'john-akii-bua' => [
        ['wikipedia', 'John Akii-Bua', 'Wikipedia', 'https://en.wikipedia.org/wiki/John_Akii-Bua'],
        ['record', 'John Akii-Bua: results and biography', 'Olympedia', 'https://www.olympedia.org/athletes/77062'],
        ['article', 'Uganda to remember Olympic hurdler John Akii Bua', 'World Athletics', 'https://worldathletics.org/news/news/uganda-to-remember-olympic-hurdler-john-akii'],
        ['documentary', 'The John Akii-Bua Story: An African Tragedy', 'BBC Two', 'https://www.bbc.co.uk/programmes/b00d1fjt'],
    ],
    'rwot-icaya-ogwangguji' => [
        ['wikipedia', 'Lango people (section on Rwot Ogwangguji)', 'Wikipedia', 'https://en.wikipedia.org/wiki/Lango_people'],
        ['research', 'Colonial chiefs in a stateless society: a case-study from Northern Uganda', 'The Journal of African History', 'https://www.cambridge.org/core/journals/journal-of-african-history/article/colonial-chiefs-in-a-stateless-society-a-casestudy-from-northern-uganda1/70C66224CDF850FFFD9B866E3EDEF9CA'],
    ],
    'owiny-akullu' => [
        ['wikipedia', 'Owiny Akullu', 'Wikipedia', 'https://en.wikipedia.org/wiki/Owiny_Akullu'],
        ['article', 'Owiny Akullu: Langi warrior who defended Kabalega to the end', 'Daily Monitor', 'https://www.monitor.co.ug/uganda/magazines/people-power/owiny-akullu-langi-warrior-who-defended-kabalega-to-the-end-1838584'],
        ['article', 'Exploring the tail end of Kabalega’s trail', 'Daily Monitor', 'https://www.monitor.co.ug/uganda/lifestyle/travel/exploring-the-tail-end-of-kabalega-s-trail-4377938'],
    ],
    'lango-war-leaders' => [
        ['wikipedia', 'Lango people (section on the military)', 'Wikipedia', 'https://en.wikipedia.org/wiki/Lango_people'],
        ['article', 'Lango', 'Encyclopaedia Britannica', 'https://www.britannica.com/topic/Lango'],
    ],
    'jane-ruth-aceng' => [
        ['wikipedia', 'Jane Aceng', 'Wikipedia', 'https://en.wikipedia.org/wiki/Jane_Aceng'],
        ['profile', 'Biography: a look at Uganda’s Health Minister, Ruth Aceng', 'PML Daily', 'https://www.pmldaily.com/profiles/2021/07/biography-lets-take-a-look-at-ugandas-health-minister-ruth-aceng.html'],
        ['profile', 'Dr Jane Ruth Aceng, member profile', 'Women in Global Health', 'https://womeningh.org/our-member/dr-jane-ruth-aceng/'],
    ],
    'betty-amongi' => [
        ['wikipedia', 'Betty Amongi', 'Wikipedia', 'https://en.wikipedia.org/wiki/Betty_Amongi'],
        ['article', 'Betty Amongi, Obote’s Maruzi country deserves much better', 'New Vision', 'http://www.newvision.co.ug/new_vision/news/1436649/betty-amongi-obote-maruzi-country-deserves'],
    ],
    'denis-hamson-obua' => [
        ['wikipedia', 'Denis Obua (politician)', 'Wikipedia', 'https://en.wikipedia.org/wiki/Denis_Obua_(politician)'],
        ['article', 'Kudos to Hon Hamson Obua: You displayed humility even in defeat', 'The Observer', 'https://observer.ug/?p=66189'],
        ['profile', 'Obua Denis Hamson', 'UGLeader', 'https://ugleader.com/leader/1073/obua-denis-hamson'],
    ],
    'dorcus-acen' => [
        ['wikipedia', 'Dorcus Acen', 'Wikipedia', 'https://en.wikipedia.org/wiki/Dorcus_Acen'],
        ['article', '30 women line up for 9 Lango Woman MP seats', 'Daily Monitor', 'https://www.monitor.co.ug/uganda/news/national/30-women-line-up-for-9-lango-woman-mp-seats-1899912'],
    ],
],

/* A person seeded earlier with a very short story gets this fuller one, once, when the row's story is still shorter than 1,500 characters
   (a story an editor has already written at length is never replaced). slug => fields */
'refresh' => [
'rwot-icaya-ogwangguji' => [
 'title' => 'County chief of Erute, M.B.E.',
 'place' => 'Abedpiny, Lira',
 'summary' => 'A long-serving chief of Erute county, remembered as a bridge between “old Lango” and “new Lango”, and honoured with the M.B.E. in 1956.',
 'body' => <<<'HTML'
<p>Rwot Icaya (Isaya) Ogwangguji was born in <strong>1875</strong> in Abedpiny village, in what is now Lira District. His father, Rwot Olet Apar, led the <strong>Oki clan</strong>.</p>
<h3>A chief in changing times</h3>
<ul>
<li>His first post was <em>jago</em> (sub-chief) of Lira.</li>
<li><strong>1918</strong> &ndash; Erute county elected him its county chief (<em>rwot</em>).</li>
<li><strong>28 February 1951</strong> &ndash; he stopped being county chief of Erute and was promoted to <em>Rwot Adwong</em>, a senior chief.</li>
<li><strong>1956</strong> &ndash; he was awarded the M.B.E. in the New Year Honours, and he retired in December 1957.</li>
</ul>
<p>Lango society had traditionally had no permanent chiefs, so the colonial chief system was a very big change. Because Rwot Ogwangguji worked through so many of the changes of the twentieth century, he is described as a chief who <strong>bridged &ldquo;old Lango&rdquo; and &ldquo;new Lango&rdquo;</strong>.</p>
<p><em>Further reading (not online): M. J. Wright, &ldquo;The early life of Rwot Isaya Ogwangguji, M.B.E.&rdquo;, <em>Uganda Journal</em> 22(2), September 1958, pp. 131&ndash;138, the source cited by Wikipedia.</em></p>
HTML,
],
'owiny-akullu' => [
 'born' => 'c. 1845', 'died' => 'c. 1947', 'title' => 'Lango warrior and ally of Kabalega', 'place' => 'Acutanana, Kamdini, Oyam',
 'summary' => "Remembered as a Lango warrior who fought beside Bunyoro's Omukama Kabalega against the British until Kabalega was captured in 1899; a statue honours him in Kamdini.",
 'body' => <<<'HTML'
<p>Owiny Akullu was born at <strong>Acutanana, in Kamdini sub-county, Oyam District</strong>, to Ogwang Akota and his wife Akullu. Wikipedia gives his birth year as 1845 and his death as 1947, at the age of 102; as with many people of that time the dates are approximate.</p>
<h3>Warrior and ally of Kabalega</h3>
<ul>
<li>He is remembered as a military leader and as a very accurate <strong>javelin thrower</strong>. Wikipedia says he gathered a private force of more than 150 fighters.</li>
<li>In the <strong>Battle of Minakulu</strong> he is said to have defeated the Acholi and taken the land between Kamdini and Bobi, in present-day Gulu District.</li>
<li>He was a commander and ally of <strong>Omukama Kabalega of Bunyoro</strong> and fought beside him against the British until Kabalega was captured in <strong>1899</strong>, a turning point in the colonial conquest of Bunyoro.</li>
</ul>
<h3>Remembered today</h3>
<p>In <strong>2019 a statue</strong> of Owiny Akullu was put up in Kamdini sub-county to honour his part in resisting colonial rule. He is celebrated locally as a hero.</p>
<p>His story rests on few written sources, so the details are best read as part of oral history. Elders and families are invited to add what they know through the contribution form.</p>
HTML,
],
'lango-war-leaders' => [
 'title' => 'Famous war leaders of old Lango',
 'body' => <<<'HTML'
<p>In pre-colonial Lango there was no standing army and no permanent chief. When clans had to defend themselves, they united under one military leader chosen by <strong>consensus</strong>, and everyone agreed to be led by him. His authority <strong>ended when the war ended</strong>: he went back to his clan and his daily work and received no special benefits.</p>
<h3>The best-remembered leaders</h3>
<ul>
<li><strong>Ongora Okubal</strong> &ndash; remembered as the leader who brought the Lango to the land where they live today.</li>
<li><strong>Opyen</strong> &ndash; succeeded Ongora Okubal; he was followed by <strong>Arim Oroba</strong>.</li>
<li><strong>Agoro Abwango</strong> &ndash; led his men against the Banyoro and was killed in Bunyoro.</li>
</ul>
<h3>Helping Kabalega and Mwanga</h3>
<p>Lango oral literature says that when the fighters who had gone to help <strong>Kabalega</strong> of Bunyoro retreated towards the Nile, they helped him and the deposed Kabaka <strong>Mwanga</strong> of Buganda cross the river. They were moved along the northern side of Lake Kwania, where a warrior called <strong>Obol Ario</strong>, of the Okwero Ngec Ayita clan from Apac, had won much of the land. He is believed to have helped bring the two kings towards Dokolo, where they settled at Kangai. Obol Ario later settled at Amac, where he died and was buried.</p>
<p>These stories show how Lango society joined strong clan loyalty with a deep suspicion of permanent power. They come from oral tradition as summarised in Wikipedia&rsquo;s article on the Lango people.</p>
HTML,
],
'jane-ruth-aceng' => [
 'born' => '1968', 'title' => 'Paediatrician and Minister', 'place' => 'Atapara, Oyam',
 'summary' => "A paediatrician from Oyam who led Uganda's Ministry of Health from June 2016 and, according to Wikipedia, was elected Woman MP for Lira City in January 2026.",
 'body' => <<<'HTML'
<p>Dr. Jane Ruth Aceng was born on <strong>11 May 1968</strong> in Atapara village, Oyam District, and grew up partly in Lira. She went to Shimoni Demonstration Primary School and Nabisunsa Girls&rsquo; School in Kampala. At Makerere University she earned a <strong>Bachelor of Medicine and Surgery, a Master of Medicine in Paediatrics and a Master of Public Health</strong>, and she holds a diploma in health systems management from the Galilee International Management Institute in Israel.</p>
<h3>From Lira hospital to the Ministry</h3>
<ul>
<li>After her internship she asked to be posted to <strong>Lira Regional Referral Hospital</strong>, where the young doctors were nicknamed <em>&ldquo;Otin&rdquo;</em> (&ldquo;young&rdquo;) doctors.</li>
<li>She became executive director of Lira Regional Referral Hospital and then <strong>Director General of Medical Services</strong> (June 2011 &ndash; June 2016).</li>
<li>On <strong>6 June 2016</strong> she was appointed <strong>Minister of Health</strong>, a post she held through the COVID-19 years.</li>
<li>Wikipedia reports that she was elected <strong>Woman Member of Parliament for Lira City</strong> in the January 2026 elections and was then given a different ministry (Information, Communications Technology and National Guidance).</li>
</ul>
<p>Political posts change after every election, so check the Parliament of Uganda website for the current position.</p>
HTML,
],
'betty-amongi' => [
 'born' => '1975', 'title' => 'Politician and former Cabinet Minister', 'place' => 'Oyam',
 'summary' => 'A politician from Oyam, first elected to Parliament in 2001, who served in Cabinet from 2016 and was Minister of Gender, Labour and Social Development from 2021 to 2026.',
 'body' => <<<'HTML'
<p>Betty Amongi (born Betty Amongi Ongom) was born in <strong>Oyam District</strong> on 15 November 1975. She studied political science and public administration at Makerere University (a Bachelor of Arts, from 1996) and later earned a Master of Arts in international relations and diplomatic studies (2009).</p>
<h3>In Parliament and Cabinet</h3>
<ul>
<li><strong>2001</strong> &ndash; first elected to Parliament as Woman MP for Apac District; re-elected in 2006.</li>
<li><strong>2011 and 2016</strong> &ndash; elected Woman MP for Oyam South.</li>
<li><strong>2016</strong> &ndash; President Museveni named her Minister of Lands, Housing and Urban Development, a surprise because she belonged to the opposition Uganda People&rsquo;s Congress.</li>
<li><strong>December 2019</strong> &ndash; moved to be Minister for Kampala Capital City.</li>
<li><strong>June 2021</strong> &ndash; appointed Minister of Gender, Labour and Social Development, and she represented Oyam South in the 11th Parliament (2021&ndash;2026).</li>
</ul>
<p>According to Wikipedia she was not in the 2026 cabinet; she stood for the Lira City Woman MP seat in January 2026 and lost to Dr. Jane Ruth Aceng. She is married to Jimmy Akena, president of the UPC and a son of Milton Obote.</p>
HTML,
],
'denis-hamson-obua' => [
 'title' => 'Member of Parliament and former Government Chief Whip', 'place' => 'Alebtong',
 'summary' => 'A legislator from Alebtong, Youth MP from 2006 and MP for Ajuri County from 2011, who served as Minister of State for Sports and then Government Chief Whip.',
 'body' => <<<'HTML'
<p>Denis Hamson Obua comes from <strong>Alebtong District</strong>. He studied at Inomo Primary School and <strong>Lango College</strong> in Lira, and later took a diploma in law at the Law Development Centre, a degree in social work and social administration at Uganda Christian University, Mukono, and a Master of Public Administration at the Uganda Management Institute (2016).</p>
<h3>In public life</h3>
<ul>
<li>Before politics he worked as a contracts clerk, a legal assistant and a legal officer in the Office of the Vice President (2000&ndash;2006).</li>
<li><strong>2006</strong> &ndash; elected Youth Member of Parliament for Northern Uganda.</li>
<li><strong>2011</strong> &ndash; elected for the newly created <strong>Ajuri County</strong> in Alebtong; re-elected in 2016 and 2021.</li>
<li><strong>December 2019</strong> &ndash; named to Cabinet and, in January 2020, sworn in as State Minister for Sports.</li>
<li><strong>August 2022</strong> &ndash; took office as <strong>Government Chief Whip</strong> of Parliament.</li>
</ul>
<p>According to Wikipedia he lost the Ajuri seat in the January 2026 elections and was then appointed a senior presidential adviser and chair of the NRM parliamentary caucus. Posts change after every election, so check the Parliament of Uganda website for the current position.</p>
HTML,
],
],

/* A person added in this version (inserted only when the slug is not there yet). */
'new' => [
[
 'slug' => 'dorcus-acen', 'name' => 'Dorcus Acen', 'short_name' => 'Dorcus Acen', 'category' => 'public-service', 'featured' => 0, 'sort' => 10,
 'born' => null, 'died' => null, 'title' => 'Woman Member of Parliament for Alebtong', 'place' => 'Alebtong',
 'summary' => 'Woman MP for Alebtong District in the 11th Parliament, who worked on preventing violence against women before entering politics.',
 'body' => <<<'HTML'
<p>Dorcus Acen (also written Dorcas Acen) is the <strong>Woman Member of Parliament for Alebtong District</strong> in the 11th Parliament of Uganda (2021&ndash;2026). Wikipedia lists her among the notable Lango people.</p>
<h3>Before and in Parliament</h3>
<ul>
<li>She worked with the <strong>GBV Prevention Network</strong> as the focal person on sexual violence and violence against women in emergencies.</li>
<li>In 2018 she was Gender and Protection Adviser for <strong>CARE International in South Sudan</strong>, leading its gender and protection programmes.</li>
<li>She stood for the Alebtong Woman MP seat in the 10th Parliament (2016) as an independent, and in <strong>2021</strong> won the seat as a candidate of the National Resistance Movement.</li>
<li>During the 2021 campaign, Wikipedia reports, she gave two ambulances to Alebtong to help with patient referrals during the COVID-19 pandemic and food to vulnerable families.</li>
<li>In the 11th Parliament she served on the Committee on Gender, Labour and Social Development.</li>
</ul>
<p>Posts change after every election, so check the Parliament of Uganda website for her current position.</p>
HTML,
],
],

];
