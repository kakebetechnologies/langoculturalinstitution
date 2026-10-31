<?php
declare(strict_types=1);

/* Content that fills the pages that were empty (attire, crafts, ceremonies, music, storytelling, symbols, proverbs, clans, biodiversity, gallery).
   Nothing here is invented. The customs, crafts, clan prohibitions, proverbs, flora and fauna are taken from J. H. Driberg, "The Lango: A Nilotic Tribe
   of Uganda" (London: T. Fisher Unwin, 1923), a public-domain book written by a colonial administrator who lived among the Lango; page numbers are
   given in each entry and the proverbs were checked against the scanned pages. The book describes life about a century ago, so each entry says so and
   invites elders to correct and add. Facts about species (names, conservation status) come from Wikipedia and Wikidata (CC BY-SA). The six photographs
   were supplied by the project owner; their photographers are being confirmed (credit lines say so). Nothing is marked verified. */

$src = fn(string $pages) => '<p class="src"><em>Source: J. H. Driberg, <cite>The Lango: A Nilotic Tribe of Uganda</cite> (London, 1923), ' . $pages . '; public domain. It describes life about a hundred years ago as a colonial administrator saw it, so customs have changed and some may be told differently today. Elders and families are invited to correct it and add what they know.</em></p>';
$supplied = 'Photograph supplied for this project; the photographer is being confirmed.';

return [

/* ------------------------------------------------------------------ heritage ------------------------------------------------------------------ */
'heritage' => [

// ---- attire
['category' => 'attire', 'slug' => 'head-dress-tok-and-kono', 'title' => 'Head-dress and hairstyles: the tok and the kono', 'image' => 'assets/img/culture/chief-buchta.jpg',
 'image_credit' => 'A Lango chief in a horned head-dress. Photograph by Richard Buchta (died 1894), published in The Living Races of Mankind (1902). Public domain.',
 'summary' => 'Beaded caps, feather crowns and named haircuts: how Lango men dressed their hair a century ago.',
 'body' => <<<'HTML'
<p>The Lango were known for the care they gave to their hair. The ethnographer J. H. Driberg described two main head-dresses worn by men.</p>
<ul>
<li>The <strong>kono</strong> was a crown of cock&rsquo;s feathers built up on the hair with clay and chalk. It was mostly, but not only, a war head-dress.</li>
<li>The <strong>tok</strong> covered the crown of the head in a close cap of small discs of ostrich eggshell and black seeds. White beads later replaced the seeds and were worked into patterns, sometimes with cowries, and red chalk could be rubbed on for ceremonies. Dressing the hair took several hours, and the barber was paid with a pot of beer and a chicken.</li>
</ul>
<p>In time the tok and the kono were made so that they could be taken off, and a round wicker tok, worked with the wearer&rsquo;s own hair and decorated with rings of beaten brass, came into fashion. A warrior who had killed an enemy cut off his tok and hung it at his outdoor hearth, the <em>otem</em>.</p>
<h3>Named haircuts</h3>
<ul>
<li><strong>Achudi</strong> &ndash; the head shaved except for a top-knot.</li>
<li><strong>Atira</strong> &ndash; a strip three fingers wide shaved from the crown to the forehead, done after killing an enemy.</li>
<li><strong>Ajulu</strong> (&ldquo;the crest&rdquo;) &ndash; the head shaved except for a comb of hair three inches wide.</li>
<li><strong>Kinga</strong> &ndash; reserved for twins: long hair with a fringe shaved round the head.</li>
<li><strong>Apam</strong> &ndash; hair shaved short all over.</li>
</ul>
<p>Women did not cut their hair. They oiled it with croton or sesame oil mixed with ashes or chalk and twisted it into strands. Women dressed each other&rsquo;s hair and charged nothing for it.</p>
HTML . $src('pp. 58&ndash;61')],

['category' => 'attire', 'slug' => 'beads-brass-wire-and-ivory', 'title' => 'Beads, brass wire and ivory', 'image' => 'assets/img/culture/pot-dance.jpg', 'image_credit' => $supplied,
 'summary' => 'Necklaces, coils of wire, ear and lip ornaments, and the ivory bracelet that marked chiefly descent.',
 'body' => <<<'HTML'
<p>Driberg wrote that the Lango, men and women alike, were &ldquo;inordinately fond of ornaments&rdquo;. What a person wore depended mostly on what they could afford, and the two sides did not have to match: if there was only enough wire for one arm, it was worn on one arm.</p>
<ul>
<li><strong>Beads and charms.</strong> Bead necklaces were worn by everyone. Others were made of string, giraffe hair, or little discs chipped from river-mussel shell and ostrich egg, which were heirlooms or came from the north. Charms for love, health and luck in hunting hung on them.</li>
<li><strong>Brass and iron wire.</strong> Coils were worn on the arms and legs and as high collars round the neck. Women valued brass wire even more than beads.</li>
<li><strong>Ears, brow and waist.</strong> The rim of the ear was pierced in many places and carried rings, some set with a blue bead. A band of beads often crossed the forehead, and a small horn could be worn at the brow. Strings of small beads or cowries went round the waist.</li>
<li><strong>Bells</strong> were tied below the knee and at the ankles for ceremonies.</li>
<li><strong>Twins.</strong> Women who had borne twins, and the twins themselves until they were grown, wore necklets and wristlets of cowrie shells.</li>
</ul>
<p>The only mark of high rank was ivory: a bracelet on the left wrist, or an ivory ornament called <em>ogwil</em> hung on the chest, worn by chiefs and men descended from chiefs. Bodies were anointed with plant oils and, among women, curdled milk.</p>
HTML . $src('pp. 61&ndash;65')],

['category' => 'attire', 'slug' => 'girdles-aprons-and-skins', 'title' => 'Girdles, aprons and skins', 'image' => 'assets/img/culture/dancers-group.jpg', 'image_credit' => $supplied,
 'summary' => 'Leather girdles, the ariko apron of metal chains and the skins used in Lango dress.',
 'body' => <<<'HTML'
<p>A century ago most Lango men wore ornaments but little clothing; in the north-east some wore a small goatskin. Women and girls wore leather and fibre.</p>
<ul>
<li>From about the age of five a girl wore strings of hibiscus fibre on a thin leather girdle, with more strings as she grew older.</li>
<li>If her father was well off, an unmarried girl could wear an <strong>ariko</strong>, an apron of small metal chains made by a specialist smith. The wider it was, the more it cost: an apron ten chains wide was worth a goat. She wore it until she had borne two children.</li>
<li>A mother wore a strip of leather, the <em>lau</em>, given to her by the child&rsquo;s father. A broad leather &ldquo;tail&rdquo; studded with metal had once been fashionable.</li>
<li>These leather garments were made from goatskin, except among clans whose women were forbidden goatskin.</li>
</ul>
<p>Skins, beads and shells are still a part of the costume of Lango dancers today.</p>
HTML . $src('pp. 62&ndash;65')],

// ---- crafts
['category' => 'craft', 'slug' => 'smiths-and-metalwork', 'title' => 'Smiths and metalwork', 'image' => null, 'image_credit' => null,
 'summary' => 'Spears, knives, rings and the ariko: the work of the Lango smith, the atet.',
 'body' => <<<'HTML'
<p>In Driberg&rsquo;s time metalwork was the work of men who inherited the skill, usually from their fathers. The smith was called <strong>atet</strong>: the <em>atet tong</em> made spears, knives, finger rings, earrings and the heavy brass bracelet called <em>ogul</em>, and the <em>atet ariko</em> made only the chain apron, the ariko.</p>
<ul>
<li>The Lango smith did not smelt iron ore. A customer brought the metal in the form of iron hoes, and brass came from imported wire.</li>
<li>The fee was never large and depended on the labour: an ariko cost one to three goats. The smith kept the finished piece until he was paid, and the payment was called a &ldquo;ransom&rdquo; for his work.</li>
<li>The smithy, <em>ot tet</em>, stood outside the village: one round hut without walls, with a roof that came almost to the ground to keep out the wind. It held the bellows, the furnace, the moulds, tongs, hammer and charcoal. Nobody stole from it.</li>
<li>An apprentice, often a relation, worked the bellows and was paid by learning the trade.</li>
</ul>
HTML . $src('pp. 86&ndash;87')],

['category' => 'craft', 'slug' => 'pottery', 'title' => 'Pottery', 'image' => null, 'image_credit' => null,
 'summary' => 'How Lango pots were moulded, patterned and fired, and the two-mouthed pot kept for ceremonies.',
 'body' => <<<'HTML'
<p>Driberg recorded how pots were made in the Lango of a century ago.</p>
<ul>
<li>The clay, <em>dagi</em>, was fetched from river beds and used without mixing in anything else.</li>
<li>Small pots were moulded by hand from a lump. A large pot was built up from its base in strips of clay.</li>
<li>Patterns were pressed into the damp clay by rolling a short stick with a spiral cut in it, and the inside was smoothed with a calabash scraper called <em>akwaya</em>.</li>
<li>The pot dried for three or four days, was wrapped thickly in grass, and was fired by lighting the grass from the base upwards.</li>
<li>A slightly cracked pot could be mended by smearing the inside with a gel from a wild root, <em>akure</em>.</li>
</ul>
<p>One pot was different. The <strong>dogaryo</strong> (&ldquo;two-mouth&rdquo;), also called <em>agulu jok</em>, the &ldquo;pot of god&rdquo;, was used for ceremonies, and it was the only pot whose making was left to particular people, because it was hard to make well.</p>
HTML . $src('p. 88')],

['category' => 'craft', 'slug' => 'mats-baskets-and-cords', 'title' => 'Mats, baskets and cords', 'image' => null, 'image_credit' => null,
 'summary' => 'Mats of palm bark and papyrus, grain baskets, and ropes and threads for every use.',
 'body' => <<<'HTML'
<p>Mats, baskets and cords were made in every village, and both men and women took part in basketry and matting.</p>
<ul>
<li><strong>Mats</strong> were made in three ways: from the beaten bark of the wild date palm, from papyrus stalks threaded together, and (a newer fashion copied from neighbours) as a six-foot twilled mat of fibre. The <em>oderu</em>, a winnowing mat about two feet square, was made of wicker like the house door.</li>
<li><strong>Baskets</strong> of wicker and twined work, neat and strong, held grain and vegetables. The <em>kor</em>, a basket for hens, was woven from broad bands of fibre.</li>
<li><strong>Cords.</strong> Women spun cotton into thread by rubbing it on the thigh and made the grass plait <em>akedi</em> for tying firewood. Men made ropes and cords from plant fibres, including the three-ply <em>akongomer</em> used for snares and cattle halters.</li>
<li><strong>Oil and salt.</strong> Oil was pressed from sesame and from wild trees such as the croton. Salt came from deposits along the lake shore, or from the ashes of burnt goats&rsquo; dung filtered with water.</li>
</ul>
HTML . $src('pp. 89&ndash;90')],

// ---- music
['category' => 'music', 'slug' => 'drum-band', 'title' => 'The seven-drum band', 'image' => null, 'image_credit' => null,
 'summary' => 'A tall drum and six squat ones, tuned from high to low: the drums that led Lango dances.',
 'body' => <<<'HTML'
<p>A Lango drum band had seven drums, and only men played them.</p>
<ul>
<li>The <strong>atimu</strong> was a tall, narrow drum with the highest note. The player hung it from his neck and held it between his knees, beating it with the flat of his hands.</li>
<li>Six round drums stood in a row against a log: the <strong>min bul</strong> (&ldquo;mother drum&rdquo;), with the lowest note, the <strong>adadang</strong>, and four <strong>atin bul</strong> (&ldquo;child drums&rdquo;). Two musicians knelt before them and beat them with small sticks, sharing the work between them.</li>
<li>The drums were made from the trees <em>etek</em>, <em>apok</em>, <em>abata</em> or <em>yago</em>, and covered with the skin of a bull calf, waterbuck or elephant. The atimu was covered with monitor-lizard skin. A full set cost one large bull.</li>
<li>They were tuned before use by warming the skins in front of a fire.</li>
</ul>
<p>The notes of the seven drums went down a scale from the atimu to the min bul. Boys learned the patterns by tapping a set of seven sticks stuck in the ground, cut to the same notes.</p>
HTML . $src('pp. 87&ndash;88 and 125&ndash;126')],

['category' => 'music', 'slug' => 'flutes-horns-and-whistles', 'title' => 'Flutes, horns and whistles', 'image' => null, 'image_credit' => null,
 'summary' => 'Flute bands made of antelope horn, war trumpets, and the whistle each man kept for himself.',
 'body' => <<<'HTML'
<p>Besides drums, Lango music used wind instruments.</p>
<ul>
<li>The <strong>otule</strong> was the flute that accompanied dances. It was made from an antelope horn with three finger holes, burnt in with a hot spear-butt. Flutes came in three sizes: the <em>min bilo</em> (&ldquo;mother flute&rdquo;, bass), the <em>adadang</em> (tenor) and the <em>atin bilo</em> (&ldquo;child flute&rdquo;, treble). A flute band had five players: two atin bilo, one adadang and two min bilo.</li>
<li>Earlier dances were played on the <strong>olwet</strong>, a four-hole wooden flute that had almost gone out of use by Driberg&rsquo;s time.</li>
<li>The <strong>apel</strong> (also called arupape) was a trumpet made from a hollowed euphorbia stem or an antelope horn. It gave two notes and was used in war and at dances to make a noise.</li>
<li>The <strong>bilo</strong> proper was a war whistle made from a small horn.</li>
</ul>
<p>Driberg noted that the Lango had borrowed from their neighbours in music more than in anything else, and that the flutes and drum bands had replaced older instruments.</p>
HTML . $src('pp. 123&ndash;126')],

['category' => 'music', 'slug' => 'tom-harp', 'title' => 'The six-string harp (tom)', 'image' => null, 'image_credit' => null,
 'summary' => 'The old Lango harp: a boat-shaped body, six strings and no bow, rarely heard by 1923.',
 'body' => <<<'HTML'
<p>The <strong>tom</strong> was the Lango harp, and it had once been the instrument everyone played. Every man made his own, as men also made their own flutes.</p>
<ul>
<li>The body was hollowed from wood, boat-shaped, and covered with elephant hide with a small round hole in it.</li>
<li>A curved arm with pegs rose from one end, and the six strings ran from it to the other end. The strings were made from the leg tendons of animals.</li>
<li>No bow and no plectrum were used.</li>
</ul>
<p>By 1923 only a few old men still played it, although, Driberg wrote, it had &ldquo;a most attractive tone&rdquo; and always drew a crowd of young men who stopped to listen, even on their way to a hunt.</p>
HTML . $src('pp. 88 and 123')],

// ---- dance
['category' => 'dance', 'slug' => 'myel-me-bul', 'title' => 'Myel me bul (the drum dance)', 'image' => 'assets/img/culture/dancers-group.jpg', 'image_credit' => $supplied,
 'summary' => 'The commonest Lango dance of the early twentieth century: leaping in time to the drums.',
 'body' => <<<'HTML'
<p>Driberg called <em>myel me bul</em> (with drums) and <em>myel me bilo</em> (with flutes) the commonest of the many Lango dances. Men and women both took part.</p>
<ul>
<li>The dancers stood with their legs together and their arms bent forward, holding a stick. They jumped straight up, now and then flinging their legs out sideways with a shout or turning round in the air, and repeated it in perfect time for long stretches.</li>
<li>Women and girls clicked their anklets together at every jump and tossed their heads so that their strands of hair flew.</li>
<li>With the flute band, the men stood in a large circle facing inwards while the musicians moved about inside it. Girls followed the musicians, singing in a high voice, and the dancers joined in the chorus each time it came round.</li>
<li>Older women, girls and children danced in groups around the outside. Dances took place in the late afternoon.</li>
</ul>
<p>Driberg wrote that it was a fine exercise that took a great deal of stamina. See also <a href="{base}/heritage/item/myel-me-bilo">Myel me bilo</a>, Okeme, Abuda and Ikoce.</p>
HTML . $src('pp. 126&ndash;128')],

// ---- storytelling
['category' => 'storytelling', 'slug' => 'fireside-stories', 'title' => 'Fireside stories and the cunning hare', 'image' => 'assets/img/culture/dance-story.jpg', 'image_credit' => $supplied,
 'summary' => 'Evenings round the fire, animal stories and the narrators people invited for a pot of beer.',
 'body' => <<<'HTML'
<p>Telling folk and animal stories was a favourite evening amusement: villagers, men and women, boys and girls, sat round the fire and passed the hours with fables.</p>
<ul>
<li>Good narrators acted as they told: they showed the character of each animal or person with gestures and changes of voice.</li>
<li>The <strong>hare</strong>, famous for its cunning, was given a mumbling voice, because of its cleft lip.</li>
<li>A storyteller with a fine manner and many stories became known beyond his own village, and might be invited to another to entertain the household for an evening, for the price of a pot of beer.</li>
</ul>
<p>Driberg printed a selection of Lango stories, in Lango with English translations, at the end of his book. It is public domain and can be read on the Internet Archive.</p>
HTML . $src('p. 133')],

['category' => 'storytelling', 'slug' => 'bird-voices', 'title' => 'Bird voices: words that sound like birds', 'image' => null, 'image_credit' => null,
 'summary' => 'A favourite pastime: inventing phrases that sound like the calls of birds.',
 'body' => <<<'HTML'
<p>A favourite pastime was inventing words and phrases that resemble the cries of birds and insects. Clever ones became well known and were believed to be the birds&rsquo; own words. Driberg wrote down several, with their meaning:</p>
<ul>
<li>The <strong>red pigeon</strong> is said to sing that it is grieving over the death of its father, with a &ldquo;kung, kung, kung, kung&rdquo;.</li>
<li>The <strong>green pigeon</strong> is a pair talking: the female says, &ldquo;My child is ill,&rdquo; and the male answers, &ldquo;Well, what am I to do?&rdquo;</li>
<li>A <strong>guinea-fowl</strong> caught in a snare cries, &ldquo;They set a snare, they set a snare.&rdquo; The others, who escaped, sing to it from the trees: &ldquo;You went carelessly, you went carelessly.&rdquo;</li>
<li>The small chatty <strong>apenyjulu</strong> calls to its companions that the fruit is ripe and they should wait down below.</li>
</ul>
<p>Elders may know many more, and the words as they are said today. You can add them through the contribution form.</p>
HTML . $src('pp. 133&ndash;134')],

['category' => 'storytelling', 'slug' => 'songs-and-games-of-children', 'title' => 'Songs and games of Lango children', 'image' => null, 'image_credit' => null,
 'summary' => 'Hoops and spears, imitation drums, action songs and dolls made of sweet potato.',
 'body' => <<<'HTML'
<p>Dancing and beer-drinking were the chief pastimes of grown-ups, but children had games of their own, and many trained them for adult life.</p>
<ul>
<li><strong>Spearing the hoop</strong> (<em>choho awala</em>): a boy rolled a hoop about eighteen inches across at speed, and the others threw sticks like spears through it while it was still in flight. A two-sided version used a string and sticks called <em>gwok</em>, &ldquo;dog&rdquo;, because they &ldquo;hunted&rdquo; the hoop.</li>
<li><strong>Sham fights</strong> between two parties of boys, armed with stalks of amomum for spears and pieces of palm sheath for shields.</li>
<li><strong>Hockey</strong> (<em>goyo odilo</em>, &ldquo;striking the ball&rdquo;) with curved sticks and a ball made of a hard wild fruit or of wood.</li>
<li><strong>Imitation drums</strong>: seven sticks bent over and stuck in the ground, cut so that they gave the same notes as the real drums.</li>
<li><strong>Dolls and model villages.</strong> Girls used a long sweet potato for a doll, which they washed, fed and carried on the back, and built whole villages of toy huts.</li>
<li><strong>Action songs.</strong> In one, girls circle with their arms round each other&rsquo;s waists while one kneels in the middle as a barking dog and another prowls outside as the leopard trying to steal it.</li>
</ul>
<p>Driberg added that nobody played for a prize: children played for the game itself. Elders may remember these games and many others.</p>
HTML . $src('pp. 130&ndash;133')],

['category' => 'storytelling', 'slug' => 'proverbs-and-sayings', 'title' => 'Proverbs and sayings', 'image' => null, 'image_credit' => null,
 'summary' => 'Short sayings that carry the wisdom of the elders, as recorded in 1923.',
 'body' => <<<'HTML'
<p>Proverbs show how people think. Driberg wrote down a collection of Lango proverbs with their meaning; the lineage of most of them, he said, is &ldquo;lost in the mists of antiquity&rdquo;. A few of them:</p>
<ul>
<li><em>Mach onywalo buru.</em> &ndash; Fire produces ashes (said of a lazy or ineffectual person).</li>
<li><em>Winyo ochodo tol.</em> &ndash; The bird breaks the snare (a lucky escape).</li>
<li><em>Kal ka otu mam dong olworo cheng.</em> &ndash; Grown grain does not fear the sun.</li>
<li><em>Tong gwen yam obeko duchu ba?</em> &ndash; Do all eggs hatch out?</li>
</ul>
<p>Read all of them on the <a href="{base}/language/proverbs">proverbs page</a>. The spelling is the old one of 1923 and may differ from today&rsquo;s.</p>
HTML . $src('pp. 136&ndash;137')],

// ---- ceremonies
['category' => 'ceremony', 'slug' => 'marriage-customs', 'title' => 'Marriage customs (as recorded in 1923)', 'image' => null, 'image_credit' => null,
 'summary' => 'Courtship, consent, the dowry arranged by elders, and the rule that marriage is never within a clan.',
 'body' => <<<'HTML'
<p>Driberg described the Lango marriage of a century ago. Many of its rules are still known today.</p>
<ul>
<li><strong>Consent.</strong> A girl could not be forced into a marriage she did not want. A man usually got to know a girl he liked first; if the feeling was shared they told their parents, and the dowry was then agreed with the girl&rsquo;s guardian: her father, brother or uncle.</li>
<li><strong>The dowry</strong> was paid in cattle, goats, and in some cases hoes and spears. How much depended on the families and the times: it was very high before the great cattle plague of 1890, fell almost to nothing afterwards, and rose again. Elders of the man&rsquo;s family took the first cattle to the courtyard of the girl&rsquo;s mother, and a bull was killed for the marriage feast.</li>
<li><strong>Whom one may marry.</strong> Marriage was strictly forbidden within the father&rsquo;s clan and the mother&rsquo;s clan, however distant the relationship. This rule is still at the centre of Lango life.</li>
<li><strong>A mother&rsquo;s eye.</strong> A young man&rsquo;s mother took keen interest in his intended wife and tried to find out whether she would be a good housewife, sometimes by setting small tests.</li>
<li><strong>Households.</strong> Marriage then was polygynous: each wife had her own house and courtyard, and her own crops and granary.</li>
</ul>
HTML . $src('pp. 154&ndash;157')],

['category' => 'ceremony', 'slug' => 'etogo-festival', 'title' => 'Etogo: the festival of &ldquo;buying adulthood&rdquo;', 'image' => null, 'image_credit' => null,
 'summary' => 'A coming-of-age festival for boys, with chickens, withies and a stream.',
 'body' => <<<'HTML'
<p><em>Etogo me wilo dongo</em>, &ldquo;the festival of buying adulthood&rdquo;, was held by some clans for boys who had reached puberty. Driberg described it among the Jo Akarawok and neighbouring clans.</p>
<ul>
<li>It took place every alternate year.</li>
<li>The boys killed chickens in the village pasture and gave them to their elders, and whipped each other with withies cut from the tree <em>epobo</em>. The chickens and the lashes were the &ldquo;price&rdquo; paid for manhood, and a test of courage.</li>
<li>When it was over, the withies were broken up and thrown into a nearby stream, where the boys bathed.</li>
<li>Having taken part entitled a boy to dress his hair in the style called <em>tweyo tok</em>, &ldquo;to tie the occiput&rdquo;.</li>
</ul>
<p>Several clans joined together for the festival, among them the Jo Arakit me Oki, Jo Akarawok me Oki, Jo Inomo, Jo Oki and Jo Akidi. Among the Jo Atekit a similar ceremony with feasts of bulls was called <em>epet me wilo dongo</em>.</p>
HTML . $src('pp. 191&ndash;193')],

['category' => 'ceremony', 'slug' => 'burial-and-mourning', 'title' => 'Burial and mourning', 'image' => null, 'image_credit' => null,
 'summary' => 'Where graves were dug, how mourners marked a death, and the feast of the undoing of the string.',
 'body' => <<<'HTML'
<p>Driberg recorded how the Lango buried and mourned their dead a century ago.</p>
<ul>
<li>Men were buried to the right of the door of their house and women to the left. Graves were deep, dug down to red clay, with the head towards the sunrise. A man with several wives was buried in the courtyard in front of his <em>otem</em>, and chiefs and war-leaders just beyond it.</li>
<li>The funeral wail was raised at once by the wife (or sister) of the dead person and taken up by the women of the village, so that relatives in neighbouring villages knew.</li>
<li>The body was laid on its side with the knees drawn up and the hands under the cheek, wrapped in the sleeping hide. A chicken and a sheep were killed over the grave, and leaves of the tree <em>edugu</em> were scattered on it.</li>
<li>The wife and mother wore strings of fibre round the head, neck and waist, and close relatives took off their ornaments. Men shaved their heads on the third day and women on the fourth.</li>
<li>Close relatives, in the first days of grief, were closely watched, and the burial was done by someone from outside the family.</li>
<li>The funeral feast, <em>achuban</em>, was quiet and sad. At the next harvest the relatives gathered for the <em>apuny me gonyo tol</em>, the &ldquo;feast of the undoing of the string&rdquo;.</li>
</ul>
HTML . $src('pp. 165&ndash;169')],

// ---- symbols
['category' => 'symbol', 'slug' => 'clan-taboos-kwer', 'title' => 'Clan taboos (kwer) and totems', 'image' => null, 'image_credit' => null,
 'summary' => 'Every Lango clan keeps its own prohibitions; a few clans are named after the animal they protect.',
 'body' => <<<'HTML'
<p>A Lango clan is an <strong>atekere</strong>, a word that means &ldquo;a state of amity or alliance&rdquo;, and clan names begin with <em>Jo</em>, &ldquo;people of&rdquo;. What defines a clan is its set of <strong>kwer</strong>, prohibitions or taboos, which differ from clan to clan and are strictly kept. They concern animals (the duiker, waterbuck or leopard, for example), plants such as the fig tree <em>ebu</em>, and rules for pregnancy and birth.</p>
<ul>
<li>Three clans are named after the animal they protect: the <strong>Jo Ayom</strong> (the patas monkey), the <strong>Jo Akwaich</strong> (the leopard) and the <strong>Jo Akarawok me Jo Amor</strong> (the duiker). They do not hunt it and mourn it when it dies.</li>
<li>Clans are <strong>exogamous</strong>: marriage is not allowed into the father&rsquo;s or the mother&rsquo;s clan. A woman joins her husband&rsquo;s clan, and children belong to their father&rsquo;s clan.</li>
<li>Clans divide: a branch is named with <em>me tung</em> (&ldquo;of the branch&rdquo;) and the name of its ancestor, and two clans can join to make a new one.</li>
</ul>
<p>Driberg also noted that the people are grouped geographically into four divisions: the <strong>Jo Aber</strong> north of the River Koli, the <strong>Jo Moita</strong> between the Koli and Lake Kwania, the <strong>Jo Burutok</strong> south of Lake Kwania and the Olweny, and the <strong>Jo Kidi</strong> in the east. See the <a href="{base}/clans">clan pages</a> for the prohibitions of individual clans.</p>
HTML . $src('pp. 189&ndash;192')],

['category' => 'symbol', 'slug' => 'whistle-motif', 'title' => 'The personal whistle motif', 'image' => null, 'image_credit' => null,
 'summary' => 'Every man had a tune of his own on the war whistle, and nobody else could play it.',
 'body' => <<<'HTML'
<p>Every Lango man had his own <strong>whistle motif</strong>, called his <em>nying</em> (&ldquo;name&rdquo;), played on the <em>bilo</em>, the small horn whistle. It could be remembered by a few words of a private song, much as bugle calls are remembered by words.</p>
<ul>
<li>Nobody else might play it. Taking another man&rsquo;s motif led to a bitter quarrel and could lead to bloodshed.</li>
<li>A man blew it in war and in the hunt to announce a kill, and to make himself known from far away to his loved one, his family and his friends.</li>
</ul>
HTML . $src('pp. 124&ndash;125')],

['category' => 'symbol', 'slug' => 'jok', 'title' => 'Jok', 'image' => null, 'image_credit' => null,
 'summary' => 'The supreme god of traditional Lango belief, and the places and objects that carry his name.',
 'body' => <<<'HTML'
<p>Although many Lango today are Christians or Muslims, traditional belief still plays a role in religious life. In traditional Lango thought every person has a guardian spirit and a soul, ancestors are honoured, and there is a supreme god, <strong>Jok</strong>.</p>
<p>Driberg met his name in everyday things:</p>
<ul>
<li>the two-mouthed ceremonial pot, the <em>dogaryo</em>, is also called <em>agulu jok</em>, &ldquo;the pot of god&rdquo;;</li>
<li>a bare patch of soil in the grass is a <em>laro jok</em>, &ldquo;god&rsquo;s threshing-floor&rdquo;, and some clans forbid women to walk across one.</li>
</ul>
<p><small>Sources: Wikipedia, &ldquo;Lango people&rdquo; (CC BY-SA 4.0), reworded; J. H. Driberg, <cite>The Lango</cite> (1923), pp. 88&ndash;89 and 194&ndash;199; public domain. Elders are invited to add and correct.</small></p>
HTML],
],

/* ------------------------------------------------------------------ proverbs ------------------------------------------------------------------ */
/* [Lango as printed in 1923, English as printed, meaning where the book gives one] */
'proverbs' => [
 ['Winyo ochodo tol.', 'The bird breaks the snare.', 'Said of a lucky escape.'],
 ['Mam gichob\' anyeri duch.', 'They do not all kill rats.', 'Someone has to go home empty-handed.'],
 ['Gwen obuk i wi duru.', 'Chickens scratch on the midden.', 'Like the English proverb of a cock on his own dunghill.'],
 ['Mach onywalo buru.', 'Fire produces ashes.', 'Said of a lazy or ineffectual person.'],
 ['Lyel acho tye i tim.', 'A man\'s grave is in the bush.', 'To die in battle is a man\'s death.'],
 ['Engato makok mam oneko le.', 'A roaring lion kills no game.', 'All bark and no bite.'],
 ['Oyo oywayo i ngony dero.', 'A rat pulls at the bottom of a granary.', 'Said of an unexpected loss.'],
 ['Kal ka otu mam dong olworo cheng.', 'Grown grain does not fear the sun.', null],
 ['Tong gwen yam obeko duchu ba?', 'Do all eggs hatch out?', null],
 ['Ogwang omone gwene awelo.', 'The mongoose is at feud with the chicken who is a stranger to the place.', null],
 ['Kupa olunyo nge gweno.', 'The termite escapes behind the chicken\'s back.', null],
 ['Chulo twol i apoko.', 'To imprison a snake in a drinking cup.', 'Said of a malicious and dangerous act.'],
 ['Aweno yam olaro ki won tol.', 'A guinea-fowl once struggled with its snarer.', null],
 ['Acho yam oto ki tong achel keken bo?', 'Did a man ever die of one spear only?', null],
 ['Ibolo apuk i pi.', 'You throw a tortoise into the water.', null],
 ['Ikwoyo abeno nono bala?', 'Do you sew a baby\'s hammock without cause?', 'That is, before the child is born.'],
 ['Igeno buo ngor ata.', 'You put your trust fruitlessly in the froth of cooked beans.', 'You neglect the substance for the shadow.'],
],

/* ------------------------------------------------------------------ clans ------------------------------------------------------------------ */
/* name => summary (prohibitions as Driberg reported them in 1923). The note below is added to every one. */
'clan_note' => 'Recorded as "Jo %s" in J. H. Driberg, The Lango (1923), pp. 189-203. The prohibitions (kwer) are as reported a century ago; the clan\'s own leaders can confirm or correct them.',
'clans_new' => [
 'Ayom' => ['The patas monkey (ayom) is the totem of this clan. Members do not hunt or injure it, and they mourn for it as for one of their own when one dies. No other prohibitions are recorded.', 'Patas monkey (ayom)'],
 'Akwaich' => ['The leopard (kwaich) is the totem of this clan. Members do not hunt or injure it and mourn when one dies. Tradition said that in the old days mothers of the clan could place their babies in a leopard\'s mouth without harm.', 'Leopard (kwaich)'],
 'Obua' => ['Women do not eat the flesh of the leopard or sleep on its skin, may not use the fig tree enanga, and may not step over a tree trunk lying across the path. In a branch, Jo Obua me tung Olonga, a husband stays away from the house and courtyard for eight days after a birth.', null],
 'Olwa' => ['Women do not eat the flesh of the duiker; neither men nor women keep the hide of the duiker, kob or waterbuck. Women do not eat the fruit of, touch or use the tree awelo, and no one uses the fig tree ebu. Women may not step over an ant-hill of the kind called ogwe.', null],
 'Okori' => ['Women do not touch or use goatskin, and the hide of the waterbuck is forbidden to both sexes. At the funeral of a man many chickens are killed in the village and the people lash each other with withies of the tree epobo or with whips of buffalo and hippopotamus hide.', null],
 'Opali' => ['Women do not eat the flesh or use the hide of the duiker, may not use sheepskin to carry a baby or cover the head in the rain, and may not enter an otogo (the young men\'s hut). Children may not lie on the hide of a bushbuck, and pregnant women keep away from the entrance of a cattle kraal.', null],
 'Etoko' => ['A woman of this clan may not step over a young calf lying on the ground, and a man wearing a calfskin may not enter the village.', null],
 'Eling' => ['Women do not eat the flesh or use the hide of the duiker. A pregnant woman may not step over a tree trunk lying across the path, and women do not use the plant adyebepar.', null],
 'Ayer' => ['The flesh of the duiker is forbidden to women and its hide to everyone.', null],
 'Atule' => ['Women do not use the hide of the waterbuck, may not step over a tree trunk lying across the path, and may not walk across a laro jok, a bare patch of soil in the grass called "god\'s threshing-floor".', null],
 'Ato' => ['Women may not touch or use the fig tree ebu, and may not walk over the kind of ant-hill called ogwe.', null],
 'Arom' => ['Women do not eat the flesh or use the hide of the duiker, and may not touch or use the fig tree ebu.', null],
 'Apach' => ['Neither men nor women eat a kind of mongoose called ayita.', null],
 'Apelo' => ['Women may not touch or use goatskin or the hide of the waterbuck.', null],
 'Alipa' => ['The flesh and hide of the duiker are forbidden to women, and the hide of the waterbuck to everyone. Only girls may wear the ariko, the apron of metal chains.', null],
 'Along' => ['The flesh of the waterbuck is forbidden to women and its hide to everyone.', null],
 'Amolo' => ['Women do not eat the flesh of the duiker and no one keeps its hide; nobody wearing or carrying such a hide may enter the courtyard of a woman of the clan. No one may point out a stag-beetle to a child.', null],
 'Apeto' => ['Neither men nor women may touch or use the plants ocholi and aywe.', null],
 'Apany' => ['Women do not eat the flesh or use the hide of the waterbuck, may not touch, use or pass under the shade of the fig tree ebu, and do not use the plant adyebepar.', null],
 'Ngwiyo' => ['Pregnant women do not eat the flesh or use the hide of the bushbuck and waterbuck, and a man who sees a pregnant woman does not mention or comment on her condition.', null],
],
/* the clans that were already listed get a summary (only when theirs is empty) */
'clans_summary' => [
 'Atek' => 'Recorded by Driberg as Jo Atekit, a large clan with many branches. Women do not eat the flesh of the duiker, and no one keeps its hide; women do not use the hide of the waterbuck or eat honey; no one uses or touches the fig tree ebu. Boys at puberty take part in a ceremony called epet me wilo dongo, "buying adulthood", with a feast of bulls.',
 'Okarowok' => 'Recorded by Driberg as Jo Akarawok, a clan with many branches. Boys before puberty and women do not eat the flesh of the duiker, and no one keeps its hide. Boys at puberty take part in the etogo me wilo dongo festival, held every alternate year.',
 'Arak' => 'Recorded by Driberg as Jo Arakit. Women do not eat the flesh of the kob, waterbuck or oribi, and no one keeps those hides; giraffe are neither hunted nor eaten; no one plays with or throws bits of broken pot. Pregnant women keep away from the entrance of a cattle kraal.',
 'Oki' => 'Recorded by Driberg as Jo Oki. Boys before puberty and women do not eat the flesh of the duiker, and no one keeps its hide. In the etogo me wilo dongo festival, held every alternate year, the boys who have killed a chicken eat it together with their elders.',
],

/* ------------------------------------------------------------------ species ------------------------------------------------------------------ */
/* slug => [name, scientific name, kind, conservation (IUCN status from Wikidata), medicinal (0/1), habitat, body]; photo and credit: species_images.php */
'species' => [
 'patas-monkey' => ['Patas monkey', 'Erythrocebus patas', 'animal', 'near_threatened', 0, 'Open grassland and savannah.',
   '<p>The patas monkey is a long-legged monkey of open country and the fastest-running of the primates. Driberg listed the &ldquo;red patas monkey&rdquo; among the animals of Lango, and its Lango name, <em>ayom</em>, is also the name of a clan: the <strong>Jo Ayom</strong> treat it as one of their own and mourn when one dies.</p>'],
 'leopard' => ['Leopard', 'Panthera pardus', 'animal', 'vulnerable', 0, 'Bush, savannah and rocky country.',
   '<p>The leopard is a large spotted cat. In Lango it is called <em>kwaich</em>, and it is the totem of the <strong>Jo Akwaich</strong> clan, who do not hunt it and mourn it as a clansman. Driberg listed it, with the lion, serval and hyaena, among the animals that killed livestock.</p>'],
 'duiker' => ['Common duiker', 'Sylvicapra grimmia', 'animal', 'least_concern', 0, 'Bush and woodland edges across Africa.',
   '<p>Duikers are small antelope that dive into cover when disturbed. Several Lango clans have rules about the duiker, called <em>amor</em>: in many clans women do not eat its flesh and no one keeps its hide, and the <strong>Jo Akarawok me Jo Amor</strong> may not hunt it at all, and bury one that is killed by accident under leaves. Driberg listed the duiker among the animals found everywhere in Lango.</p>'],
 'uganda-kob' => ['Uganda kob', 'Kobus kob thomasi', 'animal', 'least_concern', 0, 'Grassland near water.',
   '<p>The Uganda kob is a reddish antelope of grassy plains near water, and is on Uganda&rsquo;s coat of arms. Driberg wrote of the &ldquo;Uganda cob&rdquo; among the antelope found everywhere in Lango, and several clans, such as the Jo Arakit, do not eat its flesh or keep its hide.</p>'],
 'waterbuck' => ['Waterbuck', 'Kobus ellipsiprymnus', 'animal', 'least_concern', 0, 'Grassland and woodland close to water.',
   '<p>The waterbuck is a large antelope that stays near water. Its hide is forbidden to women, or to everyone, in many Lango clans, among them the Jo Along, Jo Apelo, Jo Atule and Jo Apany, and in the Jo Atekit a child may not sleep on it. Driberg listed it among the animals found everywhere in Lango; its skin covered the Lango drums.</p>'],
 'hippopotamus' => ['Hippopotamus', 'Hippopotamus amphibius', 'animal', 'vulnerable', 0, 'Rivers and lakes with grazing land close by: the Nile and Lake Kyoga.',
   '<p>Driberg wrote that the hippopotamus lived along the Nile and was occasionally seen in Lake Kyoga, and that one had even been found as far inland as the River Koli. Lango whips were made of hippopotamus and buffalo hide.</p>'],
 'grey-crowned-crane' => ['Grey crowned crane', 'Balearica regulorum', 'bird', 'endangered', 0, 'Wetlands and grassland.',
   '<p>The grey crowned crane is Uganda&rsquo;s national bird and is on the national flag. Driberg wrote that &ldquo;the beautiful golden-crested crane frequently flies overhead with its rancorous cry&rdquo; in Lango.</p>'],
 'african-jacana' => ['African jacana', 'Actophilornis africanus', 'bird', 'least_concern', 0, 'Lakes and marshes with floating plants.',
   '<p>The jacana walks across floating water-lily leaves on its long toes, and is sometimes called the lily-trotter. Driberg saw it on Lake Kwania, &ldquo;the favoured haunt of numerous and diverse aquatic birds&rdquo;, where it &ldquo;trips lightly from lily to lily&rdquo;.</p>'],
 'pennant-winged-nightjar' => ['Pennant-winged nightjar', 'Caprimulgus vexillarius', 'bird', 'least_concern', 0, 'Open woodland and savannah.',
   '<p>In the breeding season the male of this nightjar grows long streamer-like wing feathers. Driberg noted the place called Bululu as &ldquo;remarkable for a great number&rdquo; of them, although they were not confined to it.</p>'],
 'shea-tree' => ['Shea tree', 'Vitellaria paradoxa', 'plant', 'vulnerable', 0, 'Savanna north and east of Lango, around the River Moroto and the rivers that feed Lake Kwania.',
   '<p>The shea tree gives the nuts from which shea butter is made. Driberg found shea trees only in part of Lango: north and east of Ngetta Hill, more and more numerous towards the River Moroto, abundant on the watershed of the Moroto and the rivers feeding Lake Kwania, and not at all elsewhere.</p>'],
 'candelabra-euphorbia' => ['Candelabra euphorbia', 'Euphorbia candelabrum', 'plant', 'least_concern', 0, 'Dry bush and rocky ground.',
   '<p>The candelabra euphorbia is a tall tree-like succulent. Driberg wrote that in Lango the hollowed stem of this tree, called <em>opopong</em>, was made into the long war trumpet, the <em>apel</em>. A thinner, pipe-stem euphorbia, <em>oligo</em>, was more common among the Lango, &ldquo;as it has a ritualistic significance&rdquo;.</p>'],
 'sesame' => ['Sesame (semsem)', 'Sesamum indicum', 'plant', 'unknown', 1, 'A crop of cultivated fields.',
   '<p>Sesame, called <em>semsem</em> by the Lango, was one of the crops and an important source of oil. Driberg recorded that sesame oil was mixed with food, and with ashes or red chalk to dress women&rsquo;s hair. A woman&rsquo;s tests as a housewife could include roasting sesame for her future mother-in-law.</p>'],
],

/* ------------------------------------------------------------------ library: reading list (links to the publisher's or archive's own page) ------------------------------------------------------------------ */
/* [title, category, year, description, address] */
'documents' => [
 ['The Lango: A Nilotic Tribe of Uganda (J. H. Driberg)', 'historical', 1923, 'The classic study of Lango life, customs, clans, language and stories, written by a colonial administrator who lived among the Lango. In the public domain: read it free on the Internet Archive.', 'https://archive.org/details/in.ernet.dli.2015.218025'],
 ['Colonial chiefs in a stateless society: a case-study from Northern Uganda', 'research', null, 'An article in The Journal of African History on how the colonial chief system met Lango society, which had no permanent chiefs. Opens on Cambridge Core.', 'https://www.cambridge.org/core/journals/journal-of-african-history/article/colonial-chiefs-in-a-stateless-society-a-casestudy-from-northern-uganda1/70C66224CDF850FFFD9B866E3EDEF9CA'],
 ['Lango people (Wikipedia)', 'research', null, 'An encyclopedia article on the Lango: land, language, clans, culture, military leaders and notable people, with references. Text available under CC BY-SA 4.0.', 'https://en.wikipedia.org/wiki/Lango_people'],
 ['Lango (Encyclopaedia Britannica)', 'research', null, 'A short entry on the Lango people from Encyclopaedia Britannica.', 'https://www.britannica.com/topic/Lango'],
],

/* ------------------------------------------------------------------ gallery ------------------------------------------------------------------ */
'media' => [
 ['dancers-group', 'Dancers in traditional dress', 'Dancers in costumes of skins, beads and feathers.', $supplied],
 ['harvest', 'Gathering the harvest', 'A woman gathers the harvest, with a hoe beside her.', $supplied],
 ['pot-dance', 'A dancer balancing a pot', 'A dancer in a cowrie-shell headband balances a pot on her head in front of a crowd.', $supplied],
 ['warrior-rock', 'Spear, shield and feathered headdress', 'A man in a feathered headdress with a spear and a shield stands on a rock above the plain.', $supplied],
 ['dance-story', 'Performers with a handmade prop', 'Performers in traditional dress with a large handmade prop.', $supplied],
 ['chief-buchta', 'A Lango chief in a horned head-dress', 'A historical photograph of a seated Lango chief in an elaborate head-dress.', 'Photograph by Richard Buchta (died 1894), published in The Living Races of Mankind (1902). Public domain.'],
],

];
