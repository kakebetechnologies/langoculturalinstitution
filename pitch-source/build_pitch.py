# Builds pitch.html: the seven-page concept paper for partners and donors. Run from this folder: python -X utf8 -I build_pitch.py
import sys
sys.path.insert(0, '.')
from pitch_art import icon, hand_phone, flywheel, hub, RED, RED_D, YELLOW, SKY, GREEN, AMBER, INK

NODE = 'C:/xampp/htdocs/langoculturalinstitution/app/node_modules'
CSS = """
@font-face { font-family: 'Clarity City'; font-weight: 400; src: url('file:///NODE/@expo-google-fonts/clarity-city/400Regular/ClarityCity_400Regular.ttf'); }
@font-face { font-family: 'Clarity City'; font-weight: 600; src: url('file:///NODE/@expo-google-fonts/clarity-city/600SemiBold/ClarityCity_600SemiBold.ttf'); }
@font-face { font-family: 'Clarity City'; font-weight: 700; src: url('file:///NODE/@expo-google-fonts/clarity-city/700Bold/ClarityCity_700Bold.ttf'); }
@font-face { font-family: 'Inter'; font-weight: 400; src: url('file:///NODE/@expo-google-fonts/inter/400Regular/Inter_400Regular.ttf'); }
@font-face { font-family: 'Ionicons'; src: url('file:///NODE/@expo/vector-icons/build/vendor/react-native-vector-icons/Fonts/Ionicons.ttf'); }
:root { --red:#EE1A26; --red-d:#C9111C; --red-mist:#FDE8E9; --yellow:#FDDD16; --yellow-mist:#FFF6C4; --sky:#00ADF1; --sky-mist:#E3F6FE; --green:#1E9E5A; --green-mist:#E3F6EC;
        --amber:#E8A317; --ink:#1B1E25; --body:#3C414B; --muted:#7B8390; --line:#E4E0D6; --canvas:#F7F5F0; --night:#14161B; }
@page { size: A4; margin: 0; }
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; }
body { font-family: 'Clarity City', 'Inter', Arial, sans-serif; font-size: 9.4pt; line-height: 1.5; color: var(--body); -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.ion { font-family: 'Ionicons'; font-style: normal; font-weight: normal; line-height: 1; display: inline-block; }
.page { width: 210mm; height: 297mm; position: relative; overflow: hidden; page-break-after: always; background: #fff; }
.page:last-child { page-break-after: auto; }
.in { position: absolute; inset: 0; padding: 14mm 15mm 12mm; display: flex; flex-direction: column; }
.in > * { flex-shrink: 0; }
h1, h2, h3, p { margin: 0; }
b, strong { color: var(--ink); font-weight: 600; }
.dark b, .dark strong { color: #fff; }
.eyebrow { font-size: 7.6pt; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: var(--red-d); }
.dark .eyebrow, .eyebrow.y { color: var(--yellow); }
h2.big { font-size: 25pt; line-height: 1.08; font-weight: 700; color: var(--ink); letter-spacing: -.01em; margin-top: 2.2mm; }
.dark h2.big { color: #fff; }
.lead { font-size: 10pt; line-height: 1.5; color: var(--body); margin-top: 3mm; }
.dark .lead { color: rgba(255,255,255,.82); }
.top { display: flex; align-items: center; gap: 3mm; font-size: 7.6pt; color: var(--muted); }
.top .r { margin-left: auto; }
.top img { width: 6.4mm; }
.top b { font-size: 8.6pt; color: var(--ink); letter-spacing: .04em; }
.dark .top, .dark .top b { color: rgba(255,255,255,.78); }
.foot { margin-top: auto; padding-top: 2.4mm; border-top: .3mm solid var(--line); display: flex; justify-content: space-between; font-size: 6.9pt; color: var(--muted); }
.dark .foot { border-top-color: rgba(255,255,255,.18); color: rgba(255,255,255,.6); }

/* ---------- cover ---------- */
.p1 .bg { position: absolute; inset: 0; background: url('photos/hero-ridge.jpg') 62% center / cover; }
.p1 .shade { position: absolute; inset: 0; background: linear-gradient(100deg, rgba(10,11,14,.96) 0%, rgba(10,11,14,.82) 38%, rgba(10,11,14,.30) 72%, rgba(10,11,14,.10) 100%), linear-gradient(0deg, rgba(10,11,14,.85) 0%, rgba(10,11,14,0) 30%); }
.p1 .in { color: #fff; }
.p1 .logo { display: flex; align-items: center; gap: 3.4mm; }
.p1 .logo img { width: 12mm; background: #fff; border-radius: 2.4mm; padding: 1mm; }
.p1 .logo b { color: #fff; font-size: 11pt; letter-spacing: .08em; display: block; }
.p1 .logo span { font-size: 7.4pt; color: rgba(255,255,255,.7); letter-spacing: .06em; }
.p1 .logo .r { margin-left: auto; text-align: right; font-size: 7.6pt; color: rgba(255,255,255,.75); letter-spacing: .04em; }
.p1 .head { margin-top: 34mm; width: 118mm; }
.p1 h1 { font-size: 42pt; line-height: 1.0; font-weight: 700; letter-spacing: -.02em; color: #fff; margin: 3.6mm 0 5.6mm; }
.p1 h1 em { font-style: normal; color: var(--yellow); }
.p1 .sub { font-size: 11.2pt; line-height: 1.55; color: rgba(255,255,255,.88); max-width: 100mm; }
.chips { display: flex; flex-wrap: wrap; gap: 2mm; margin-top: 6mm; max-width: 98mm; }
.chip { display: inline-flex; align-items: center; gap: 1.8mm; padding: 1.6mm 3.4mm; border-radius: 6mm; font-size: 7.8pt; font-weight: 600; background: rgba(255,255,255,.14); border: .3mm solid rgba(255,255,255,.3); color: #fff; }
.chip .ion { color: var(--yellow); font-size: 9pt; }
.p1 .handwrap { position: absolute; right: 7mm; bottom: 0; width: 94mm; isolation: isolate; }
.p1 .handwrap::before { content: ''; position: absolute; left: 4%; top: 6%; width: 92%; height: 70%; z-index: -1; background: radial-gradient(circle, rgba(238,26,38,.34), rgba(238,26,38,0) 66%); }
.p1 .covfoot { margin-top: auto; display: flex; align-items: flex-end; gap: 5mm; font-size: 7.6pt; color: rgba(255,255,255,.72); position: relative; z-index: 2; }
.p1 .covfoot b { color: #fff; }
.p1 .badge { border-left: 1.2mm solid var(--red); padding-left: 3mm; max-width: 78mm; line-height: 1.4; }

/* ---------- cards, grids ---------- */
.grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 3.6mm; }
.grid3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3.4mm; }
.card { border: .3mm solid var(--line); border-radius: 3.6mm; padding: 3.6mm 4.2mm; background: var(--canvas); }
.card h3 { font-size: 10pt; color: var(--ink); font-weight: 700; margin: 2.2mm 0 1mm; line-height: 1.2; }
.card p { font-size: 8.3pt; line-height: 1.45; }
.ic { width: 9.2mm; height: 9.2mm; border-radius: 2.8mm; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 14pt; }
.bg-red { background: var(--red); } .bg-amber { background: var(--amber); } .bg-sky { background: var(--sky); } .bg-green { background: var(--green); } .bg-night { background: #3A3F4A; } .bg-yellow { background: var(--yellow); color: var(--ink); }
.dcard { border: .3mm solid rgba(255,255,255,.16); border-radius: 3.6mm; padding: 3.4mm 4mm; background: rgba(255,255,255,.07); }
.dcard h3 { font-size: 10pt; color: #fff; font-weight: 700; margin: 2.2mm 0 1mm; }
.dcard p { font-size: 8.2pt; line-height: 1.45; color: rgba(255,255,255,.78); }

/* ---------- page 2 ---------- */
.p2 .band { margin-top: 4.2mm; border-radius: 4.6mm; overflow: hidden; display: flex; background: var(--night); color: #fff; }
.p2 .band .txt { flex: 1.4; padding: 5mm 6mm; }
.p2 .band .ph { flex: 1; background: url('photos/hero-field.jpg') center / cover; position: relative; }
.p2 .band .ph::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, #14161B 0%, rgba(20,22,27,0) 45%); }
.p2 .band b { color: #fff; }
.p2 .band h3 { font-size: 15pt; line-height: 1.15; color: #fff; margin: 1.6mm 0 2.4mm; }
.p2 .band ul { margin: 3mm 0 0; padding: 0; list-style: none; }
.p2 .band li { display: flex; gap: 2.6mm; margin-bottom: 1.8mm; font-size: 8.6pt; line-height: 1.4; color: rgba(255,255,255,.88); }
.p2 .band li .ion { color: var(--yellow); font-size: 11pt; margin-top: .3mm; }
.stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 2.6mm; margin-top: 4.6mm; }
.stat { border-radius: 3.2mm; padding: 3.2mm 3.2mm 3mm; background: var(--canvas); border: .3mm solid var(--line); text-align: left; }
.stat b { display: block; font-size: 17pt; line-height: 1.05; color: var(--red-d); font-weight: 700; letter-spacing: -.01em; }
.stat span { display: block; margin-top: 1mm; font-size: 7.3pt; line-height: 1.3; color: var(--body); }
.note { font-size: 6.9pt; color: var(--muted); margin-top: 2mm; }

/* ---------- page 3 ---------- */
.p3 .hero { position: relative; height: 70mm; margin: -14mm -15mm 0; background: url('photos/hero-hill.jpg') center 38% / cover; }
.p3 .hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(0deg, rgba(10,11,14,.88) 0%, rgba(10,11,14,.35) 55%, rgba(10,11,14,.55) 100%); }
.p3 .hero .q { position: absolute; left: 15mm; right: 40mm; bottom: 8mm; z-index: 2; color: #fff; }
.p3 .hero .q p { font-size: 16.5pt; line-height: 1.22; font-weight: 700; color: #fff; }
.p3 .hero .q span { display: block; margin-top: 2mm; font-size: 7.8pt; color: rgba(255,255,255,.78); }
.p3 .hero .top { position: absolute; left: 15mm; right: 15mm; top: 14mm; z-index: 2; }
.obj { display: grid; grid-template-columns: 1fr 1fr; gap: 3.2mm; margin-top: 5mm; }
.obj .o { border: .3mm solid var(--line); border-radius: 3.4mm; padding: 3.6mm 4mm 3.4mm; background: #fff; display: flex; gap: 3.2mm; position: relative; }
.obj .o .n { position: absolute; right: 3.4mm; top: 1.8mm; font-size: 17pt; font-weight: 700; color: #EFEBE0; line-height: 1; }
.obj .o h3 { font-size: 9.8pt; color: var(--ink); font-weight: 700; margin: 0 0 .8mm; }
.obj .o p { font-size: 8pt; line-height: 1.42; }
.obj .o .m { margin-top: 1.4mm; font-size: 7pt; color: var(--red-d); font-weight: 600; }
.obj .o.wide { grid-column: 1 / 3; background: var(--canvas); }
.serve { display: flex; gap: 2.4mm; margin-top: 4.6mm; }
.serve div { flex: 1; text-align: center; font-size: 7.6pt; font-weight: 600; color: var(--ink); line-height: 1.25; }
.serve .ion { display: block; margin: 0 auto 1.4mm; width: 10mm; height: 10mm; border-radius: 50%; background: var(--red-mist); color: var(--red-d); font-size: 13pt; line-height: 10mm; text-align: center; }

/* ---------- page 4 ---------- */
.p4 { background: linear-gradient(160deg, #14161B 0%, #2B1015 62%, #7A0F18 140%); }
.fan { display: flex; justify-content: center; align-items: flex-end; gap: 3.4mm; margin: 4mm -4mm 0; height: 96mm; }
.fan .ph { width: 33.5mm; border: .5mm solid #0B0C0F; border-radius: 4.6mm; overflow: hidden; box-shadow: 0 2mm 6mm rgba(0,0,0,.5); background: #F7F5F0; }
.fan .ph img { display: block; width: 100%; }
.fan .ph:nth-child(1), .fan .ph:nth-child(5) { margin-bottom: 0; transform: translateY(0); }
.fan .ph:nth-child(2), .fan .ph:nth-child(4) { margin-bottom: 7mm; }
.fan .ph:nth-child(3) { margin-bottom: 14mm; width: 37mm; }
.fan figure { margin: 0; text-align: center; }
.fan figcaption { margin-top: 2mm; font-size: 7pt; color: rgba(255,255,255,.7); line-height: 1.25; }
.fan figcaption b { display: block; font-size: 7.6pt; color: #fff; }
.pills { display: flex; flex-wrap: wrap; gap: 1.8mm; margin-top: 4mm; }
.pill { display: inline-flex; align-items: center; gap: 1.6mm; padding: 1.5mm 3.2mm; border-radius: 6mm; font-size: 7.6pt; font-weight: 600; color: #fff; background: rgba(255,255,255,.1); border: .3mm solid rgba(255,255,255,.22); }
.pill .ion { color: var(--yellow); font-size: 9pt; }

/* ---------- page 5 ---------- */
.p5 .panel { margin-top: 4mm; border-radius: 4.6mm; background: var(--night); padding: 2.4mm 4mm 2mm; position: relative; overflow: hidden; }
.p5 .panel::before { content: ''; position: absolute; right: -30mm; top: -30mm; width: 90mm; height: 90mm; border-radius: 50%; background: radial-gradient(circle, rgba(238,26,38,.35), rgba(238,26,38,0) 70%); }
.p5 .panel svg { width: 90%; height: auto; position: relative; display: block; margin: 0 auto; }
.two { display: grid; grid-template-columns: 1.08fr 1fr; gap: 4.4mm; margin-top: 4mm; }
.mini { display: grid; grid-template-columns: 1fr 1fr; gap: 2.8mm; }
.mini .card { padding: 3.2mm 3.4mm; }
.mini .card h3 { font-size: 8.8pt; margin-top: 1.6mm; }
.mini .card p { font-size: 7.6pt; line-height: 1.38; }
.mini .ic { width: 7.6mm; height: 7.6mm; font-size: 11.5pt; border-radius: 2.4mm; }
.gov { border-radius: 3.6mm; background: var(--green-mist); border: .3mm solid #BDE5CD; padding: 4mm 4.4mm; }
.gov h3 { font-size: 10pt; color: var(--ink); display: flex; align-items: center; gap: 2mm; margin-bottom: 2mm; }
.gov ul { list-style: none; margin: 0; padding: 0; }
.gov li { display: flex; gap: 2.2mm; font-size: 8pt; line-height: 1.4; margin-bottom: 1.7mm; }
.gov li .ion { color: var(--green); font-size: 10.5pt; margin-top: .2mm; }
.stage { display: flex; margin-top: 3.6mm; border-radius: 3.4mm; overflow: hidden; border: .3mm solid var(--line); }
.stage div { flex: 1; padding: 3mm 4mm; font-size: 8pt; line-height: 1.4; }
.stage div:first-child { background: var(--canvas); }
.stage div:last-child { background: var(--red-mist); }
.stage b { display: block; font-size: 8.4pt; text-transform: uppercase; letter-spacing: .08em; color: var(--red-d); margin-bottom: .6mm; }

/* ---------- page 6 ---------- */
.p6 .split { display: grid; grid-template-columns: 1.06fr 1fr; gap: 4.6mm; margin-top: 4.6mm; align-items: stretch; }
.p6 .hubbox { border-radius: 4.4mm; background: var(--canvas); border: .3mm solid var(--line); padding: 3mm; display: flex; flex-direction: column; justify-content: center; }
.p6 .hubbox svg { width: 100%; height: auto; display: block; }
.p6 .hubbox .cap { text-align: center; font-size: 7.2pt; color: var(--muted); margin-top: 1mm; }
.share { display: flex; flex-direction: column; gap: 3mm; }
.share .box { border-radius: 3.4mm; padding: 3.6mm 4.2mm; border: .3mm solid var(--line); }
.share .box h3 { font-size: 9.4pt; color: var(--ink); margin-bottom: 1.4mm; display: flex; gap: 2mm; align-items: center; }
.share ul { margin: 0; padding: 0; list-style: none; }
.share li { font-size: 7.9pt; line-height: 1.38; padding-left: 4mm; position: relative; margin-bottom: .8mm; }
.share li::before { content: ''; position: absolute; left: .4mm; top: 1.6mm; width: 1.6mm; height: 1.6mm; border-radius: 50%; background: var(--red); }
.share .a { background: var(--sky-mist); border-color: #C4E8F7; } .share .b { background: var(--yellow-mist); border-color: #F1E39A; }
.steps { display: flex; gap: 0; margin-top: 4.6mm; }
.steps div { flex: 1; position: relative; background: var(--night); color: #fff; padding: 3.4mm 5mm 3.4mm 5.4mm; font-size: 7.8pt; line-height: 1.38; clip-path: polygon(0 0, calc(100% - 4mm) 0, 100% 50%, calc(100% - 4mm) 100%, 0 100%, 4mm 50%); margin-right: -1mm; }
.steps div:first-child { clip-path: polygon(0 0, calc(100% - 4mm) 0, 100% 50%, calc(100% - 4mm) 100%, 0 100%); border-radius: 3mm 0 0 3mm; }
.steps div:last-child { clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 4mm 50%); border-radius: 0 3mm 3mm 0; background: var(--red); }
.steps b { display: block; color: var(--yellow); font-size: 8.4pt; margin-bottom: .6mm; }
.steps div:last-child b { color: #fff; }
.road { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2.6mm; margin-top: 3mm; }
.road .ph { border-radius: 3.2mm; padding: 3.4mm 3.6mm; border: .3mm solid var(--line); background: #fff; position: relative; border-top: 1.2mm solid var(--c); }
.road .ph small { display: block; font-size: 6.9pt; letter-spacing: .12em; text-transform: uppercase; color: var(--c); font-weight: 700; }
.road .ph h3 { font-size: 9pt; color: var(--ink); margin: .8mm 0 1mm; line-height: 1.2; }
.road .ph p { font-size: 7.6pt; line-height: 1.38; }

/* ---------- page 7 ---------- */
.p7 { background: var(--night); color: #fff; }
.p7 .hero { position: absolute; left: 0; right: 0; top: 0; height: 84mm; background: url('photos/hero-aerial.jpg') center 30% / cover; }
.p7 .hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(0deg, #14161B 0%, rgba(20,22,27,.72) 42%, rgba(20,22,27,.55) 100%); }
.p7 .head { margin-top: 15mm; width: 118mm; position: relative; z-index: 2; }
.p7 h2.big { font-size: 28pt; }
.p7 .handwrap { position: absolute; right: 6mm; top: 16mm; width: 48mm; z-index: 2; }
.p7 .top .r { display: none; }
.partners { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3mm; margin-top: 11mm; position: relative; z-index: 2; }
.targets { display: grid; grid-template-columns: 1.1fr 1fr; gap: 4mm; margin-top: 4mm; position: relative; z-index: 2; }
.tbox { border-radius: 3.6mm; padding: 3.4mm 4.2mm; background: rgba(255,255,255,.07); border: .3mm solid rgba(255,255,255,.16); }
.tbox h3 { font-size: 10pt; color: #fff; margin-bottom: 2mm; display: flex; align-items: center; gap: 2mm; }
.tbox h3 .ion { color: var(--yellow); }
.tbox ul { list-style: none; margin: 0; padding: 0; }
.tbox li { display: flex; gap: 2.2mm; font-size: 8pt; line-height: 1.4; margin-bottom: 1.6mm; color: rgba(255,255,255,.86); }
.tbox li .ion { color: var(--yellow); font-size: 10pt; margin-top: .2mm; }
.tbox small { display: block; margin-top: 1mm; font-size: 6.9pt; color: rgba(255,255,255,.55); }
.contact { margin-top: 4mm; border-radius: 4.2mm; background: var(--red); padding: 4mm 6mm; display: flex; align-items: center; gap: 6mm; position: relative; z-index: 2; }
.contact h3 { font-size: 13pt; color: #fff; line-height: 1.2; }
.contact p { font-size: 8.4pt; color: rgba(255,255,255,.9); margin-top: 1mm; }
.contact .c { margin-left: auto; text-align: right; font-size: 8.8pt; line-height: 1.6; color: #fff; white-space: nowrap; }
.contact .c b { color: #fff; }
""".replace('NODE', NODE)

LOGO = 'file:///C:/xampp/htdocs/langoculturalinstitution/app/assets/logo-mark.png'
TOTAL = 7


def foot(n, dark=False):
    return f'<div class="foot"><span><b>Kakebe Technologies Limited</b> &middot; Lira City &middot; www.kakebe.tech</span><span>Lango App &middot; concept paper &middot; page {n} of {TOTAL}</span></div>'


def top(label, n):
    return f'<div class="top"><img src="{LOGO}" alt=""><b>LANGO APP</b><span>{label}</span><span class="r">Concept paper &middot; 2026</span></div>'


def phone(img, cap=None, sub=None):
    fig = f'<div class="ph"><img src="shots/{img}.png"></div>'
    if cap:
        fig += f'<figcaption><b>{cap}</b>{sub or ""}</figcaption>'
    return f'<figure>{fig}</figure>'


p1 = f'''
<section class="page p1">
  <div class="bg"></div><div class="shade"></div>
  <div class="in">
    <div class="logo"><img src="{LOGO}" alt=""><div><b>LANGO APP</b><span>Won Nyaci me Lango</span></div><div class="r">CONCEPT PAPER FOR PARTNERS AND DONORS<br>2026</div></div>
    <div class="head">
      <div class="eyebrow y">A platform for Uganda&rsquo;s cultures</div>
      <h1>Every tribe.<br>Every voice.<br><em>One platform.</em></h1>
      <p class="sub">A mobile home for culture. It preserves language and heritage, connects people of the same clan and community, and builds the community-owned data that teaches AI to speak our languages.</p>
      <div class="chips">
        <span class="chip">{icon('checkmark-circle')} Tested first with Lango</span>
        <span class="chip">{icon('earth')} Built for every community in Uganda</span>
        <span class="chip">{icon('cloud-offline')} Works offline</span>
      </div>
    </div>
    <div class="handwrap">{hand_phone('shots/explore.png', -5, 'cov')}</div>
    <div class="covfoot">
      <div class="badge"><b>Testing edition: Lango.</b> Every screen in this paper is the real app, running on real Lango content.</div>
      <div style="margin-left:auto;text-align:right;position:relative;z-index:2"><b>Kakebe Technologies Limited</b><br>Lira City, Uganda &middot; www.kakebe.tech</div>
    </div>
  </div>
</section>'''

p2 = f'''
<section class="page p2">
  <div class="in">
    {top('The challenge and our answer', 2)}
    <div class="eyebrow" style="margin-top:7mm">01 &middot; Why this, why now</div>
    <h2 class="big" style="font-size:22pt">Culture lives in people, and most of it was never written down.</h2>
    <p class="lead">Uganda has more than 50 indigenous communities and around 40 living languages. Their proverbs, clans, histories and songs are carried in memory and spoken from one generation to the next, and that chain is under strain.</p>

    <div class="grid2" style="margin-top:4.2mm">
      <div class="card"><span class="ic bg-red">{icon('hourglass')}</span><h3>Knowledge is oral and fragile</h3><p>Languages, clans and stories are held by elders. When one passes, a library closes, and little of it is recorded or searchable.</p></div>
      <div class="card"><span class="ic bg-sky">{icon('airplane')}</span><h3>Young people and the diaspora are far from home</h3><p>Many have no easy way to learn the language, find their clan or hear the stories their grandparents know.</p></div>
      <div class="card"><span class="ic bg-amber">{icon('chatbubbles')}</span><h3>Our languages are missing from AI</h3><p>Most Ugandan languages have almost no digital text or speech data, so translators, voice assistants and learning tools do not work in them.</p></div>
      <div class="card"><span class="ic bg-green">{icon('business')}</span><h3>Institutions lack the tools</h3><p>Cultural institutions hold the mandate and the trust of their people, but not the software to gather, check and share what the community knows.</p></div>
    </div>

    <div class="band">
      <div class="txt">
        <div class="eyebrow y">Our answer</div>
        <h3>One app that every community can own.</h3>
        <p style="font-size:8.8pt;color:rgba(255,255,255,.82)">Lango App puts a community&rsquo;s dictionary, clans, heritage, history, radio, podcast and Bible in one phone app and lets its people add what they know. Its own language experts review every entry.</p>
        <ul>
          <li>{icon('checkmark-circle')}<span><b>Preserves</b> language, clans, history and oral tradition</span></li>
          <li>{icon('checkmark-circle')}<span><b>Collects</b> words, proverbs, stories, audio and photos from the community</span></li>
          <li>{icon('checkmark-circle')}<span><b>Connects</b> people of the same clan and culture, at home and abroad</span></li>
          <li>{icon('checkmark-circle')}<span><b>Teaches</b> the next generation, a few minutes a day</span></li>
          <li>{icon('checkmark-circle')}<span><b>Feeds AI</b> with consented, expert-checked language data</span></li>
        </ul>
      </div>
      <div class="ph"></div>
    </div>

    <div class="eyebrow" style="margin-top:4.6mm">The Lango testing edition today</div>
    <div class="stats">
      <div class="stat"><b>2,829</b><span>dictionary entries, queued for expert verification</span></div>
      <div class="stat"><b>149</b><span>Lango clans to document (5 verified so far)</span></div>
      <div class="stat"><b>10</b><span>districts mapped, including Lira City</span></div>
      <div class="stat"><b>10</b><span>heritage items: dances, ceremonies, sites</span></div>
      <div class="stat"><b>1,524</b><span>app phrases ready for translation into Leb Lango</span></div>
    </div>
    <p class="note">Figures from the running Lango testing edition. Community figures are approximate.</p>
    {foot(2)}
  </div>
</section>'''

objs = [
    ('library', 'bg-red', 'Preserve', "Document language, clans, history and oral tradition before they are lost, in the community's own words.", 'Measure: entries documented and verified, hours of audio recorded'),
    ('mic', 'bg-amber', 'Collect', 'Turn everyday knowledge into structured, reviewed data: words, proverbs, stories, audio, photos and places.', 'Measure: contributions received, reviewed and published'),
    ('sparkles', 'bg-sky', 'Power AI', 'Build consented, expert-verified text and speech datasets so translation, speech recognition and voice tools can serve Ugandan languages.', 'Measure: hours of verified speech, size of parallel text'),
    ('people', 'bg-green', 'Connect', 'Bring people of the same culture, clan and heritage together, in the village, in town and across the diaspora.', 'Measure: members in clan groups, messages exchanged'),
    ('school', 'bg-red', 'Teach', 'Give young people a daily habit of learning their language: dictionary, quizzes, weekly challenges and streaks.', 'Measure: daily learners, streaks, challenge scores'),
    ('business', 'bg-night', 'Empower institutions', 'Give every cultural institution its own edition, its own admin tools and ownership of its data.', 'Measure: editions launched, editors trained'),
]
obj_html = ''.join(f'<div class="o"><span class="ic {c}" style="flex:none">{icon(i)}</span><div><h3>{t}</h3><p>{d}</p><div class="m">{m}</div></div><span class="n">{k + 1}</span></div>' for k, (i, c, t, d, m) in enumerate(objs))
obj_html += f'<div class="o wide"><span class="ic bg-amber" style="flex:none">{icon("heart")}</span><div><h3>Sustain</h3><p>Create lasting funding and partnerships (gifts, programs, research collaboration) so that the work continues long after the first launch and every edition can look after itself.</p><div class="m">Measure: partners engaged, recurring gifts, editions that fund their own running costs</div></div><span class="n">7</span></div>'

p3 = f'''
<section class="page p3">
  <div class="in">
    <div class="hero">
      {top('Objectives', 3).replace('class="top"', 'class="top" style="color:rgba(255,255,255,.8)"')}
      <div class="q"><p>&ldquo;In Africa, when an old man dies, a library burns.&rdquo;</p><span>Amadou Hamp&acirc;t&eacute; B&acirc;, speaking at UNESCO, 1960</span></div>
    </div>
    <div class="eyebrow" style="margin-top:6mm">02 &middot; Objectives</div>
    <h2 class="big" style="font-size:22pt">Why we are building it: seven objectives</h2>
    <div class="obj">{obj_html}</div>
    <div class="eyebrow" style="margin-top:5mm">Who it serves</div>
    <div class="serve">
      <div>{icon('person')}Elders and<br>custodians</div><div>{icon('school')}Youth and<br>students</div><div>{icon('airplane')}The<br>diaspora</div>
      <div>{icon('people')}Clans and<br>families</div><div>{icon('business')}Cultural<br>institutions</div><div>{icon('flask')}Researchers and<br>AI builders</div>
    </div>
    {foot(3)}
  </div>
</section>'''

pillars = [
    ('language', 'bg-amber', 'Learn', 'A dictionary of 2,829 entries that works offline, an AI translator, proverbs, a phrasebook and a daily word quiz.'),
    ('compass', 'bg-green', 'Explore', 'Clan Explorer, districts and Lira City, heritage, history, notable people, events and news.'),
    ('add-circle', 'bg-red', 'Contribute', 'Add words, proverbs, oral stories, nature records, documents and heritage sites with photo, audio or video. Works offline; every entry is reviewed.'),
    ('people', 'bg-sky', 'Connect', 'Clan groups: see who is in yours, write to each other, browse the clan archive. Streaks, weekly challenges and leaderboards keep people coming back.'),
    ('radio', 'bg-red', 'Listen and read', 'Lango Radio, a podcast of elders&rsquo; stories and the Lango Bible, with a player that keeps going when the screen is off.'),
    ('heart', 'bg-amber', 'Support', 'Give to the institution or to keeping the app running, back programs, and see transparency figures.'),
]
pillar_html = ''.join(f'<div class="dcard"><span class="ic {c}">{icon(i)}</span><h3>{t}</h3><p>{d}</p></div>' for i, c, t, d in pillars)

p4 = f'''
<section class="page p4 dark">
  <div class="in">
    {top('The product', 4)}
    <div class="eyebrow y" style="margin-top:7mm">03 &middot; What has been built</div>
    <h2 class="big">A complete culture app, already working.</h2>
    <p class="lead" style="max-width:165mm">The Lango testing edition is a full Android app. These are real screens, not mock-ups.</p>
    <div class="fan">
      {phone('home', 'Home', 'Word of the day, shortcuts')}
      {phone('dictionary', 'Dictionary', 'Leb Lango and English')}
      {phone('proverbs', 'Proverbs', 'With meanings')}
      {phone('clans', 'Clan Explorer', 'Verified clans')}
      {phone('podcast5', 'Podcast', 'Elders&rsquo; stories')}
    </div>
    <div class="grid3" style="margin-top:4.6mm">{pillar_html}</div>
    <div class="pills">
      <span class="pill">{icon('logo-android')} Android first</span><span class="pill">{icon('cloud-offline')} Offline dictionary and queued contributions</span>
      <span class="pill">{icon('language')} English and local-language interface</span><span class="pill">{icon('shield-checkmark')} Expert review before publication</span>
    </div>
    {foot(4)}
  </div>
</section>'''

ai_cards = [
    ('language', 'bg-amber', 'Machine translation', 'Local language to English and back, from verified word and sentence pairs.'),
    ('mic', 'bg-red', 'Speech recognition', 'Voice to text for languages that today have almost no recordings.'),
    ('volume-high', 'bg-sky', 'Text to speech', 'Natural voices for learning, radio and accessibility.'),
    ('chatbubbles', 'bg-green', 'Assistants and learning tools', 'Chat, search and teaching tools that answer in the community&rsquo;s own language.'),
]
ai_html = ''.join(f'<div class="card"><span class="ic {c}">{icon(i)}</span><h3>{t}</h3><p>{d}</p></div>' for i, c, t, d in ai_cards)

p5 = f'''
<section class="page p5">
  <div class="in">
    {top('Data and AI', 5)}
    <div class="eyebrow" style="margin-top:7mm">04 &middot; Data and AI</div>
    <h2 class="big">From a phone in the village to data that teaches AI.</h2>
    <p class="lead">Every word, proverb and story added to the app can become part of a clean, consented, expert-checked dataset that belongs to the community. That is what low-resource language AI is missing.</p>
    <div class="panel">{flywheel(500)}</div>
    <div class="two">
      <div>
        <div class="eyebrow">What the data can power</div>
        <div class="mini" style="margin-top:2mm">{ai_html}</div>
      </div>
      <div class="gov">
        <h3>{icon('shield-checkmark', '1.2em', GREEN)} Responsible by design</h3>
        <ul>
          <li>{icon('key')}<span><b>Consent first.</b> Each contribution carries the contributor&rsquo;s choice: keep ownership, share openly with credit, or give it to the institution.</span></li>
          <li>{icon('checkmark-circle')}<span><b>Experts in the loop.</b> Nothing is published, or used as data, before the community&rsquo;s language experts review it.</span></li>
          <li>{icon('business')}<span><b>Community ownership.</b> Each institution governs its own data and decides who may use it.</span></li>
          <li>{icon('lock-closed')}<span><b>Privacy.</b> Names show as first name and initial, and members can delete their account.</span></li>
        </ul>
      </div>
    </div>
    <div class="stage">
      <div><b>Today</b>A dictionary-based translator and assistant that answer from the verified archive, plus the contribution and review workflow that produces the data.</div>
      <div><b>Next, with research partners</b>Train and evaluate translation and speech models on the verified data, and return them to the app and to the community.</div>
    </div>
    {foot(5)}
  </div>
</section>'''

p6 = f'''
<section class="page p6">
  <div class="in">
    {top('One platform, every community', 6)}
    <div class="eyebrow" style="margin-top:7mm">05 &middot; Beyond Lango</div>
    <h2 class="big">Built for Lango. Ready for every community in Uganda.</h2>
    <p class="lead">Lango is where we test and prove the idea. The platform is designed so that Buganda, Acholi, Teso, Ankole and every other community can have its own edition: its own name, language, clans and content, on shared technology.</p>
    <div class="split">
      <div class="hubbox">{hub(470)}<div class="cap">Examples of communities the platform is built to serve. A prototype Acholi edition has already been built from the same code base.</div></div>
      <div class="share">
        <div class="box a"><h3>{icon('git-network', '1.15em', SKY)} Shared by every edition</h3><ul><li>The app, admin panel and API</li><li>Contribution, review and verification workflow</li><li>Dictionary, translator, quizzes, clan groups</li><li>Radio, podcast and Bible player</li><li>The AI data pipeline and its safeguards</li></ul></div>
        <div class="box b"><h3>{icon('flag', '1.15em', AMBER)} Owned by each community</h3><ul><li>Name, logo and colours</li><li>Language, and the experts who verify it</li><li>Clan or chiefdom register, districts, history</li><li>All content and all data it collects</li><li>Its administrators and its institution&rsquo;s voice</li></ul></div>
      </div>
    </div>
    <div class="eyebrow" style="margin-top:5mm">How a new edition launches</div>
    <div class="steps">
      <div><b>1. Partner</b>A cultural institution or foundation sponsors its edition</div>
      <div><b>2. Configure</b>Name, colours, language, clans and districts</div>
      <div><b>3. Fill and verify</b>Seed content, train editors, start expert review</div>
      <div><b>4. Launch</b>Release on Android with the community</div>
    </div>
    <div class="eyebrow" style="margin-top:5mm">Proposed roadmap</div>
    <div class="road">
      <div class="ph" style="--c:{RED}"><small>Now</small><h3>Lango testing edition</h3><p>Build 4 in the hands of testers. Field feedback with the Lango institution.</p></div>
      <div class="ph" style="--c:{AMBER}"><small>First 6 months</small><h3>Lango public launch</h3><p>Live website, store release, expert verification, first elders&rsquo; stories.</p></div>
      <div class="ph" style="--c:{SKY}"><small>6 to 18 months</small><h3>More editions</h3><p>Two or three new communities. Shared datasets. First model evaluations.</p></div>
      <div class="ph" style="--c:{GREEN}"><small>Beyond</small><h3>A national network</h3><p>Every community, one platform, with research partners across Uganda.</p></div>
    </div>
    {foot(6)}
  </div>
</section>'''

partners = [
    ('ribbon', 'bg-red', 'Founding partner', 'Fund the Lango launch and the next community editions, hosting and security.'),
    ('flask', 'bg-sky', 'Language and AI research partner', 'Co-design the datasets, train and evaluate models, publish results.'),
    ('megaphone', 'bg-amber', 'Community and media partner', 'Record elders, bring in schools, radio stations and clan associations.'),
    ('server', 'bg-green', 'Technology partner', 'Cloud hosting, mobile data, devices, payments and push messaging.'),
    ('business', 'bg-night', 'Cultural institutions', 'Sponsor your own edition and become part of the national network.'),
    ('earth', 'bg-red', 'Diaspora and advocates', 'Champion the app abroad, give, and connect your clan to home.'),
]
partner_html = ''.join(f'<div class="dcard"><span class="ic {c}">{icon(i)}</span><h3>{t}</h3><p>{d}</p></div>' for i, c, t, d in partners)

p7 = f'''
<section class="page p7 dark">
  <div class="hero"></div>
  <div class="in">
    {top('Partner with us', 7)}
    <div class="head">
      <div class="eyebrow y">06 &middot; Partner with us</div>
      <h2 class="big">Help every Ugandan community keep its voice.</h2>
      <p class="lead">Lango App turns a phone into a place where a culture is kept, shared and taught. We are looking for partners to take it from a tested edition to a national platform.</p>
    </div>
    <div class="handwrap">{hand_phone('shots/podcast5.png', 4, 'end', sleeve=AMBER)}</div>
    <div class="partners">{partner_html}</div>
    <div class="targets">
      <div class="tbox">
        <h3>{icon('flag')} Proposed pilot targets: first 12 months, Lango</h3>
        <ul>
          <li>{icon('checkmark-circle')}<span>Verify all <b>2,829</b> dictionary entries with language experts</span></li>
          <li>{icon('checkmark-circle')}<span>Document and verify the <b>149</b> Lango clans</span></li>
          <li>{icon('checkmark-circle')}<span>Record and publish the first <b>100</b> elders&rsquo; stories as audio</span></li>
          <li>{icon('checkmark-circle')}<span>Translate the whole app (<b>1,524</b> phrases) into Leb Lango</span></li>
          <li>{icon('checkmark-circle')}<span>Reach <b>5,000</b> members across all ten districts</span></li>
          <li>{icon('checkmark-circle')}<span>Launch a <b>second community edition</b></span></li>
        </ul>
        <small>Targets are proposals, to be agreed with partners.</small>
      </div>
      <div class="tbox">
        <h3>{icon('rocket')} What support makes possible</h3>
        <ul>
          <li>{icon('mic')}<span>Recording teams and expert verification in the community</span></li>
          <li>{icon('cloud-upload')}<span>Hosting, security and offline reach for rural users</span></li>
          <li>{icon('copy')}<span>New community editions, launched with their institutions</span></li>
          <li>{icon('sparkles')}<span>Open, consented datasets and AI research in Ugandan languages</span></li>
          <li>{icon('school')}<span>Training for editors, teachers and youth ambassadors</span></li>
        </ul>
      </div>
    </div>
    <div class="contact">
      <div><h3>Let us talk.</h3><p>A detailed work plan and financial proposal are available on request.</p></div>
      <div class="c"><b>Kakebe Technologies Limited</b><br>Lira City, Uganda<br>service@kakebe.tech &middot; +256 777 676 206<br>www.kakebe.tech</div>
    </div>
    {foot(7)}
  </div>
</section>'''

html = f'''<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Lango App: concept paper for partners and donors</title><style>{CSS}</style></head>
<body>{p1}{p2}{p3}{p4}{p5}{p6}{p7}</body></html>'''
open('pitch.html', 'w', encoding='utf-8').write(html)
print('pitch.html written', len(html) // 1024, 'KB')
