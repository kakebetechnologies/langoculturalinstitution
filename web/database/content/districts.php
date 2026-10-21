<?php
declare(strict_types=1);

/* District and city profiles for the Lango sub-region.
   Sources: UBOS 2024 census figures (as compiled by citypopulation.de), UBOS 2014 census, Wikipedia district pages,
   Uganda Investment Authority Lango profile. Figures are approximate and should be re-checked against UBOS before
   publication; boundaries have changed as new districts were created. */
return [
    'lira-city' => [
        'name' => 'Lira City', 'kind' => 'city', 'sort' => 0, 'hq' => 'Lira City', 'lat' => 2.2499, 'lng' => 32.8998,
        'population' => 245132, 'pop_year' => 2024, 'area_km2' => null, 'created_year' => 2020, 'parent' => 'Lira District',
        'neighbours' => 'Surrounded by Lira District', 'distance' => 'About 337 km by road north of Kampala; about 100 km from Gulu',
        'known_for' => "The commercial and industrial capital of the Lango sub-region\nHome of Lira University and the Lira Regional Referral Hospital\nNgetta Rock, a landmark granite outcrop on the edge of town\nThe city of Obote, Oyite-Ojok and Akii-Bua\nSeat of the Roman Catholic Diocese of Lira",
        'subdivisions' => 'Lira City West and Lira City East divisions',
        'overview' => <<<'HTML'
<p>Lira is the beating heart of Lango. It began life as an administrative post in the early colonial period (the settlement dates from 1919), became a town council in 1962 and a municipality in 1985, and on <strong>1 July 2020</strong> it was upgraded to a city. Today it is the main commercial, educational, health and transport centre for the whole sub-region, and the place where most Lango families do their banking, trading, studying and worshipping.</p>
<p>The city sits at roughly 1,060 metres above sea level on the northern plains, about 100 km south-east of Gulu and 337 km north of Kampala. A railway station and an airport connect it to the rest of the country, and a 132 kV power line from the Karuma hydropower station to a Lira substation has been built to strengthen its electricity supply.</p>
HTML,
        'demography' => <<<'HTML'
<p>Lira City recorded <strong>245,132 people in the 2024 census</strong>, making it the largest urban centre in the sub-region. That is far above the 99,059 counted in the 2014 census, a jump that reflects both rapid growth and the boundary changes that came with city status. Planners estimate that the daytime population is far larger than the night-time one because thousands of people travel in daily from surrounding districts to trade, study and work.</p>
<p>The population is overwhelmingly Lango and Leb Lango is the main language of the market and the home, with English and Swahili used in offices and business. Like most Ugandan towns, the city is young: a large share of residents are children and young adults.</p>
HTML,
        'economy' => <<<'HTML'
<p>Lira is described as the region's "commercial and industrial city". It hosts large employers such as <strong>Mukwano Industries</strong> (consumer goods) and <strong>Meru Millers</strong> (grain milling), and the <strong>Lira Central Market</strong> is among the busiest in northern Uganda. Farmers from across Lango bring cassava, maize, sorghum, groundnuts and sesame to town, and traders carry goods on to Gulu, Soroti and Kampala.</p>
<p>Education and health are also major industries: the city is home to Lira University, a campus of Uganda Martyrs University, All Saints University and the Lira Regional Referral Hospital, which serves patients from several districts.</p>
HTML,
        'attractions' => <<<'HTML'
<ul>
<li><strong>Ngetta Rock (Got Ngetta)</strong> – a large rock formation rising above the surrounding landscape and one of Lira's best-known landmarks.</li>
<li><strong>Lango Cultural Centre</strong> – a meeting place linked to the Lango traditional institution, where cultural events and gatherings are held.</li>
<li><strong>Akii Bua Stadium</strong> – named after Lira's Olympic champion John Akii-Bua.</li>
<li><strong>Obote Avenue</strong> and sites associated with Dr. Apollo Milton Obote.</li>
<li><strong>The Lira War Memorial.</strong></li>
</ul>
HTML,
        'history' => <<<'HTML'
<p>Lira was founded in 1919 and grew around the colonial district headquarters. On 15 May 1979 it was the scene of the <strong>Battle of Lira</strong>, the last major battle of the Uganda–Tanzania War that ended Idi Amin's rule. In 2020 it became one of Uganda's new cities, a sign of how far the town has grown from a small administrative post.</p>
HTML,
    ],
    'lira' => [
        'name' => 'Lira', 'kind' => 'district', 'sort' => 1, 'hq' => 'Lira', 'lat' => 2.31, 'lng' => 32.96,
        'population' => 242216, 'pop_year' => 2024, 'area_km2' => 1368.9, 'created_year' => 1974, 'parent' => 'Lango District (split in 1974)',
        'neighbours' => 'Pader, Otuke, Alebtong, Dokolo, Apac and Kole districts; surrounds Lira City', 'distance' => 'About 337 km by road north of Kampala',
        'known_for' => "The mother district of Lango: Dokolo, Otuke and Alebtong were all carved out of it\nGrain milling, brick and pottery making, embroidery and trade\nHome of John Akii-Bua and Maj. Gen. Oyite-Ojok",
        'subdivisions' => 'Historically Erute North and Erute South counties (192 parishes and 2,247 villages in older records)',
        'overview' => <<<'HTML'
<p>Lira District wraps around Lira City and for decades was the largest and most influential district in Lango. It was formed in <strong>1974</strong> when the old Lango District was divided into Lira and Apac. It then lost territory as new districts were created: Dokolo in 2005, Otuke in 2009 and Alebtong in 2010. After those splits it was left with the Erute counties and the municipality that is now Lira City.</p>
HTML,
        'demography' => <<<'HTML'
<p>Lira District recorded <strong>242,216 people in the 2024 census</strong> (Lira City is counted separately). In the 2014 census, when the municipality was still part of the district, it recorded 408,043 people and was the largest in the sub-region. Almost everyone is Lango and Leb Lango is the language of daily life.</p>
HTML,
        'economy' => <<<'HTML'
<p>The district economy is varied: <strong>grain milling, retail and wholesale trade, brick and pottery making, carpentry, construction, fishing, embroidery and crop processing</strong>, with boda-boda transport everywhere. Households keep cattle, goats, sheep, pigs, chickens and guinea fowl.</p>
HTML,
        'attractions' => <<<'HTML'
<p>Most visitors base themselves in Lira City and make day trips to villages, markets and cultural events in the surrounding countryside. Cultural groups perform Okeme and other dances at weddings, introduction ceremonies and festivals throughout the district.</p>
HTML,
        'history' => <<<'HTML'
<p>Lira District was largely spared by the northern Uganda insurgency compared with its neighbours, although violence in 2002 displaced many people. Between 2006 and 2007 more than 310,000 of the roughly 350,000 displaced residents returned home.</p>
HTML,
    ],
    'apac' => [
        'name' => 'Apac', 'kind' => 'district', 'sort' => 2, 'hq' => 'Apac', 'lat' => 1.9758, 'lng' => 32.5386,
        'population' => 221962, 'pop_year' => 2024, 'area_km2' => null, 'created_year' => 1974, 'parent' => 'Lango District (split in 1974)',
        'neighbours' => 'Oyam, Kole, Lira, Dokolo, Kwania, Amolatar, Nakasongola and Kiryandongo districts', 'distance' => 'About 62 km south-west of Lira; roughly 230 km north of Kampala',
        'known_for' => "The birthplace of Dr. Apollo Milton Obote at Akokoro\nThe Obote ancestral home and monument, a national heritage site\nTobacco, cotton and sesame farming\nFishing communities on Lake Kwania",
        'subdivisions' => 'Teboke, Chegere, Ibuje, Apac and Akokoro sub-counties, and Apac Municipality',
        'overview' => <<<'HTML'
<p>Apac is a district of farmers, fishers and history. It was one of the two districts formed in 1974 and has since given birth to three more: <strong>Oyam (2006), Kole (2010) and Kwania (2018)</strong>. Its main town, Apac, lies about 62 km south-west of Lira.</p>
<p>Apac is best known across Uganda as the home district of <strong>Dr. Apollo Milton Obote</strong>, who was born at Akokoro in 1925. His ancestral home and monument are recognised as a national heritage site.</p>
HTML,
        'demography' => <<<'HTML'
<p>The 2024 census counted <strong>221,962 people</strong> in Apac District; Apac Municipality itself had about 49,600. The figures are lower than in the past because several large areas were taken out to form Kwania and Kole districts. Reports on the district describe a population that is overwhelmingly rural and Lango-speaking.</p>
HTML,
        'economy' => <<<'HTML'
<p>Subsistence farming supports roughly <strong>80% of households</strong>, and women do much of the work. The main crops are <strong>tobacco, cotton, sesame, maize, beans, sunflower, potatoes, cassava and groundnuts</strong>. In the south, near Lake Kwania (part of the Lake Kyoga system), fishing is an important livelihood and fish farming is emerging. Cattle and goats are common. Trade dominates the small formal business sector.</p>
HTML,
        'attractions' => <<<'HTML'
<ul>
<li><strong>Obote ancestral home and monument, Akokoro</strong> – a national heritage site managed with the Uganda Tourism Board.</li>
<li><strong>Lake Kwania shoreline</strong> – fishing landings, wetlands and birdlife in the south of the district.</li>
<li>Village cultural events and traditional dances.</li>
</ul>
HTML,
        'history' => <<<'HTML'
<p>Akokoro and the country around it are closely tied to the political rise of Dr. Obote, and Apac was one of the two districts created when the colonial-era Lango District was divided in 1974. Since then its territory has been divided repeatedly as new districts were formed.</p>
HTML,
    ],
    'oyam' => [
        'name' => 'Oyam', 'kind' => 'district', 'sort' => 3, 'hq' => 'Oyam', 'lat' => 2.2333, 'lng' => 32.3833,
        'population' => 477464, 'pop_year' => 2024, 'area_km2' => 2190.8, 'created_year' => 2006, 'parent' => 'Apac District',
        'neighbours' => 'Gulu, Pader, Kole, Apac, Kiryandongo and Nwoya districts', 'distance' => 'About 78 km west of Lira by road',
        'known_for' => "The most populous district in the sub-region\nCotton, bananas (matooke), millet, pineapples and soya beans\nThe western gateway of Lango towards the Nile",
        'subdivisions' => 'Rural district served from Oyam town',
        'overview' => <<<'HTML'
<p>Oyam District was created by Parliament in <strong>2006</strong> out of Apac District. It lies on the western edge of Lango, bordering Acholi and the Nile corridor, and its headquarters at Oyam town is about 78 km west of Lira.</p>
HTML,
        'demography' => <<<'HTML'
<p>Oyam is the <strong>largest district in Lango by population: 477,464 people in the 2024 census</strong>, about 19% of the sub-region. It was already growing at around 3.5% a year in the early 2000s. It is a predominantly rural district and the population is mostly Lango.</p>
HTML,
        'economy' => <<<'HTML'
<p>Oyam is a district of smallholder farmers and herders. Typical crops are <strong>cotton, bananas and matooke, beans, millet, pineapples, cassava, sweet potatoes and soya beans</strong>. Families keep cattle, goats, sheep, pigs, chickens, rabbits, ducks and turkeys.</p>
HTML,
        'attractions' => <<<'HTML'
<p>Oyam's appeal is its rural landscape, its markets and its position at the junction of Lango and Acholi. Travellers heading north from Kampala to Gulu pass close to its western edge.</p>
HTML,
        'history' => <<<'HTML'
<p>As a new district, Oyam is still building its identity, but its people share the history of the wider Lango sub-region, including the years of displacement and recovery that followed the northern Uganda conflict.</p>
HTML,
    ],
    'kole' => [
        'name' => 'Kole', 'kind' => 'district', 'sort' => 4, 'hq' => 'Kole', 'lat' => 2.4, 'lng' => 32.8,
        'population' => 294301, 'pop_year' => 2024, 'area_km2' => null, 'created_year' => 2010, 'parent' => 'Apac District',
        'neighbours' => 'Lira (east), Apac (south), Oyam (west and north)', 'distance' => 'About 28 km north-west of Lira',
        'known_for' => "A young, fast-growing district just north-west of Lira\nSub-counties of Aboke, Akalo, Alito, Ayer and Bala\nFarming, printing and construction businesses",
        'subdivisions' => 'Aboke, Akalo, Alito, Ayer and Bala sub-counties',
        'overview' => <<<'HTML'
<p>Kole District became operational on <strong>1 July 2010</strong>, carved out of Apac District by an Act of Parliament. Its chief town, Kole, lies about 28 km north-west of Lira, so the district benefits from Lira's markets and services while keeping a strongly rural character.</p>
HTML,
        'demography' => <<<'HTML'
<p>Kole recorded <strong>294,301 people in the 2024 census</strong> (up from about 239,000 in 2014), making it the second most populous district in the sub-region after Oyam. The population is Lango and predominantly rural.</p>
HTML,
        'economy' => <<<'HTML'
<p>Most households farm and keep chickens, cattle and goats. Small businesses in Kole town include construction, printing services and bookshops, reflecting the district's growing school population.</p>
HTML,
        'attractions' => <<<'HTML'
<p>Kole offers village life, market days and cultural events within an easy drive of Lira.</p>
HTML,
        'history' => <<<'HTML'
<p>Kole is one of the younger districts of the sub-region and is still building its services and institutions. Its history is bound up with that of Apac, from which it was separated in 2010.</p>
HTML,
    ],
    'dokolo' => [
        'name' => 'Dokolo', 'kind' => 'district', 'sort' => 5, 'hq' => 'Dokolo', 'lat' => 1.9167, 'lng' => 33.1667,
        'population' => 215625, 'pop_year' => 2024, 'area_km2' => 1072.8, 'created_year' => 2005, 'parent' => 'Lira District',
        'neighbours' => 'Lira, Alebtong, Kaberamaido, Amolatar and Kwania districts', 'distance' => 'About 60 km south-east of Lira by road',
        'known_for' => "A district on the shores of Lake Kwania\nTen sub-counties and two constituencies (Dokolo North and South)\nCrop farming, poultry and transport",
        'subdivisions' => '10 sub-counties, 60 parishes and 474 villages; Dokolo North and Dokolo South constituencies',
        'overview' => <<<'HTML'
<p>Dokolo was created by Parliament in <strong>2005</strong> and became operational on 1 July 2006. It had been a county of Lira District. Lake Kwania stretches along part of the district, from the south into the north-west.</p>
HTML,
        'demography' => <<<'HTML'
<p>Dokolo had <strong>215,625 people in the 2024 census</strong>, up from about 129,000 in 2002. It is a predominantly rural district of Lango farmers, with about 170 people per square kilometre in 2012.</p>
HTML,
        'economy' => <<<'HTML'
<p>Households grow food and cash crops, keep poultry, cattle and goats, and run small shops and transport businesses. The lake and wetlands support fishing and provide water for dry-season farming.</p>
HTML,
        'attractions' => <<<'HTML'
<p>The <strong>Lake Kwania shoreline</strong> is the district's natural highlight, with fishing communities, papyrus wetlands and birdlife.</p>
HTML,
        'history' => <<<'HTML'
<p>Dokolo was the first of the new districts carved from Lira in the 2000s, followed by Otuke in 2009 and Alebtong in 2010.</p>
HTML,
    ],
    'amolatar' => [
        'name' => 'Amolatar', 'kind' => 'district', 'sort' => 6, 'hq' => 'Amolatar', 'lat' => 1.6333, 'lng' => 32.8167,
        'population' => 188715, 'pop_year' => 2024, 'area_km2' => 1758, 'created_year' => 2005, 'parent' => null,
        'neighbours' => 'Apac, Dokolo, Kaberamaido, Buyende, Kayunga and Nakasongola districts', 'distance' => 'About 85 km south of Lira',
        'known_for' => "The Amolatar Monument marking the geographic centre of Uganda\nCommercial fishing on lakes Kyoga and Kwania\nCotton, rice, cassava and soybeans",
        'subdivisions' => 'Amolatar Town Council, Muntu, Awelo, Namasale and Aputi sub-counties; Kioga North and Kioga South constituencies',
        'overview' => <<<'HTML'
<p>Amolatar is a lakeside district on the southern edge of Lango, formed in <strong>2005</strong>. It is best known nationally for the <strong>Amolatar Monument, which marks the geographic centre of Uganda</strong>. The district headquarters at Amolatar town lie about 85 km south of Lira.</p>
HTML,
        'demography' => <<<'HTML'
<p>Amolatar counted <strong>188,715 people in the 2024 census</strong>, up from 147,166 in 2014. It has 33 parishes and 346 villages in two constituencies, Kioga South and Kioga North.</p>
HTML,
        'economy' => <<<'HTML'
<p>Livelihoods combine <strong>subsistence farming, livestock keeping and commercial fishing</strong> on the surrounding lakes. Crops include cotton, cassava, sunflower, soybeans, rice, maize, millet and bananas, and families keep cattle, goats, chickens, turkeys and pigs. Construction and trade employ many in the towns.</p>
HTML,
        'attractions' => <<<'HTML'
<ul>
<li><strong>Amolatar Monument</strong> – marking the centre of Uganda.</li>
<li><strong>Lakeshore landing sites</strong> on Lake Kyoga and Lake Kwania, where fish is landed and traded.</li>
</ul>
HTML,
        'history' => <<<'HTML'
<p>Amolatar's position on Lake Kyoga and Lake Kwania places it at the meeting point of Lango and its southern and eastern neighbours, Kaberamaido, Buyende, Kayunga and Nakasongola.</p>
HTML,
    ],
    'alebtong' => [
        'name' => 'Alebtong', 'kind' => 'district', 'sort' => 7, 'hq' => 'Alebtong', 'lat' => 2.25, 'lng' => 33.25,
        'population' => 283509, 'pop_year' => 2024, 'area_km2' => 1527.5, 'created_year' => 2010, 'parent' => 'Lira District',
        'neighbours' => 'Otuke (north), Amuria (east), Dokolo (south), Lira (west)', 'distance' => 'About 48 km east of Lira by road',
        'known_for' => "Sweet potatoes, maize and beans\nTwo counties, Ajuri and Moroto\nA bridge between Lango and the Teso and Karamoja regions",
        'subdivisions' => 'Ajuri and Moroto counties',
        'overview' => <<<'HTML'
<p>Alebtong District was created in <strong>2010</strong> from Lira District. It lies about 48 km east of Lira at an elevation of about 1,100 metres and borders Amuria in Teso to the east.</p>
HTML,
        'demography' => <<<'HTML'
<p>Alebtong counted <strong>283,509 people in the 2024 census</strong> (225,327 in 2014 and 163,047 in 2002), making it the third most populous district in Lango. It is organised into the Ajuri and Moroto counties and is mostly rural.</p>
HTML,
        'economy' => <<<'HTML'
<p>The district's staple crops are <strong>sweet potatoes, maize and beans</strong>, supported by livestock farming (cattle, goats, pigs and poultry), fishing and local market trade.</p>
HTML,
        'attractions' => <<<'HTML'
<p>Alebtong offers village markets, rural landscapes and traditional dance performances, and is a gateway for travellers moving between Lango and the Teso region.</p>
HTML,
        'history' => <<<'HTML'
<p>As the eastern neighbour of Lira, Alebtong has strong cultural and trading ties to both Lango and Teso.</p>
HTML,
    ],
    'otuke' => [
        'name' => 'Otuke', 'kind' => 'district', 'sort' => 8, 'hq' => 'Otuke', 'lat' => 2.5, 'lng' => 33.48,
        'population' => 161069, 'pop_year' => 2024, 'area_km2' => 1549.8, 'created_year' => 2009, 'parent' => 'Lira District',
        'neighbours' => 'Agago, Abim, Napak, Amuria, Alebtong, Lira and Pader districts', 'distance' => 'About 66 km east of Lira by road',
        'known_for' => "The north-eastern corner of Lango, at about 1,200 metres elevation\nMaize, simsim (sesame), beans and cassava\nA meeting point of Lango and Karamoja cultures",
        'subdivisions' => 'Otuke County',
        'overview' => <<<'HTML'
<p>Otuke is the north-eastern gateway of Lango. The district was created on <strong>1 July 2009</strong> out of Lira District. Otuke town lies about 66 km east of Lira, at an elevation of roughly 1,200 metres. The district borders Agago, Abim, Napak, Amuria, Alebtong, Lira and Pader, so Lango meets Acholi, Karamoja and Teso here.</p>
HTML,
        'demography' => <<<'HTML'
<p>Otuke counted <strong>161,069 people in the 2024 census</strong>, up from 104,254 in 2014 when it was the smallest district in the sub-region. It is mostly rural, with a population density far lower than the sub-region average.</p>
HTML,
        'economy' => <<<'HTML'
<p>Livelihoods are built on <strong>maize, simsim (sesame), beans and cassava</strong>, together with livestock (cattle, goats, sheep, pigs and poultry), fishing and trade.</p>
HTML,
        'attractions' => <<<'HTML'
<p>The upland scenery, open grazing land and market towns of Otuke give a taste of the border country between Lango and Karamoja.</p>
HTML,
        'history' => <<<'HTML'
<p>Researchers have noted that several Lango clan names, including Atek, Okarowok and Otengor, also appear among the Ateker peoples of Karamoja and Teso, a reminder of the old links that run through this part of Lango.</p>
HTML,
    ],
    'kwania' => [
        'name' => 'Kwania', 'kind' => 'district', 'sort' => 9, 'hq' => 'Aduku', 'lat' => 1.75, 'lng' => 32.7,
        'population' => 216125, 'pop_year' => 2024, 'area_km2' => 1108, 'created_year' => 2018, 'parent' => 'Apac District',
        'neighbours' => 'Kole (north), Dokolo (east), Apac (west), Amolatar (south, across Lake Kwania)', 'distance' => 'Headquarters at Aduku Town Council',
        'known_for' => "The newest district in Lango, created in 2018\nLake Kwania, a large part of the district is open water\nAbout 15% of the district is forest",
        'subdivisions' => 'Kwania and Kwania North counties with 11 sub-counties and town councils',
        'overview' => <<<'HTML'
<p>Kwania is the newest district in the sub-region. It was carved out of Apac District and became effective on <strong>1 July 2018</strong> to bring services closer to people. Its headquarters are at Aduku Town Council, and the lake that gives the district its name lies along its southern edge.</p>
HTML,
        'demography' => <<<'HTML'
<p>Kwania recorded <strong>216,125 people and 48,861 households in the 2024 census</strong>, a density of about 195 people per km². Earlier figures describe about 96% of residents as rural. The district has two counties, Kwania and Kwania North, containing 11 sub-counties and town councils.</p>
HTML,
        'economy' => <<<'HTML'
<p>With about 11% of the district covered by open water and 15% by forest, Kwania's economy combines fishing on the lake with farming. The road network totals about 1,321 km and many of the roads need repair, a major priority for local government.</p>
HTML,
        'attractions' => <<<'HTML'
<p><strong>Lake Kwania</strong> and its papyrus wetlands, island fishing communities and birdlife are the district's main natural attractions.</p>
HTML,
        'history' => <<<'HTML'
<p>Kwania's creation in 2018 was part of a wave of district splits that changed the administrative map of Lango over fifteen years.</p>
HTML,
    ],
];
