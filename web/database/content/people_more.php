<?php
declare(strict_types=1);

/* More notable Langos. Facts for Sam Engola come from his Wikipedia article and from Daily Monitor and the Office of the Prime Minister, which are listed
   as further reading on his page; the roles and dates are as those sources report them. His photograph was supplied for this project. */
$supplied = 'Photograph supplied for this project; the photographer is being confirmed.';
return [
'new' => [
[
 'slug' => 'sam-engola', 'name' => 'Sam Engola', 'short_name' => 'Sam Engola', 'category' => 'public-service', 'featured' => 1, 'sort' => 4,
 'title' => 'Minister for Relief, Disaster Preparedness and Refugees',
 'summary' => 'Businessman and politician from Lira District: Member of Parliament for Erute County South, a former State Minister for Housing, and Minister for Relief, Disaster Preparedness and Refugees.',
 'body' => <<<'HTML'
<p>Sam Engola is a Ugandan businessman and politician from the Lango sub-region. He represents Erute County South, in Lira District, in Parliament and has served in the Cabinet.</p>
<h3>Public life</h3>
<ul>
<li><strong>1995&ndash;1996</strong> &ndash; member of the Constituent Assembly that drafted Uganda&rsquo;s 1995 Constitution.</li>
<li><strong>1995&ndash;2001</strong> &ndash; chairman of Uganda Air Cargo Limited.</li>
<li><strong>From 2001</strong> &ndash; Member of Parliament for Erute County South, Lira District.</li>
<li><strong>2011&ndash;2016</strong> &ndash; State Minister for Housing, appointed on 27 May 2011.</li>
<li><strong>May 2026</strong> &ndash; named Minister for Relief, Disaster Preparedness and Refugees, in the Office of the Prime Minister.</li>
</ul>
<h3>Business</h3>
<p>He has had hotel and tourism business interests since 1990.</p>
<p>This profile is compiled from the public sources listed below. It has not yet been checked with Mr Engola or his office, and the roles and dates are as those sources report them.</p>
HTML,
],
],
'photos' => [
    'sam-engola' => ['assets/img/people/sam-engola.jpg', $supplied, ''],
],
'districts' => ['sam-engola' => 'lira'],
'links' => [
    'sam-engola' => [
        ['wikipedia', 'Sam Engola', 'Wikipedia', 'https://en.wikipedia.org/wiki/Sam_Engola'],
        ['article', 'Minister Engola urges refugees to return home as aid dwindles', 'Daily Monitor', 'https://www.monitor.co.ug/uganda/news/national/minister-engola-urges-refugees-to-return-home-as-aid-dwindles-5556966'],
        ['article', 'Uganda reaffirms support for refugees and host communities', 'Office of the Prime Minister', 'https://opm.go.ug/uganda-reaffirms-support-for-refugees-host-communities/'],
    ],
],
];
