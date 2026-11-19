"""Builds the App Store screenshots of MyCulture.
   1. node capture_ios.js            -> raw/NN-name.png, real screens of the app at 1206 x 2622 (needs the Expo web server on :8095 and the local site)
   2. python make_ios.py             -> ../screenshots/iphone-6.3/01..08.png (1206 x 2622) and ../screenshots/ipad-13/01..08.png (2064 x 2752)
   Edge renders the pages with its command line (puppeteer cannot launch this Edge). Fonts and logo come from ../../briefing-source."""
import os
import subprocess
import sys
import tempfile
import time
from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.normpath(os.path.join(HERE, '..', 'screenshots'))
EDGE = r'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
PROFILE = os.path.join(tempfile.gettempdir(), 'edge-ios-profile-render')
RAW = {  # the real screens, by name
    'home': '01-home', 'explore': '02-explore', 'learn': '03-learn', 'dictionary': '04-dictionary', 'translate': '05-translate', 'clans': '06-clans',
    'clan': '07-clan', 'heritage': '08-heritage', 'people': '09-people', 'quizzes': '10-quizzes', 'quiz': '11-quiz', 'contribute': '12-contribute',
    'history': '13-history', 'events': '14-events', 'culture-lango': '15-culture-lango', 'culture-acholi': '16-culture-acholi', 'phrasebook': '17-phrasebook',
    'proverbs': '18-proverbs', 'form-word': '19-form-word', 'heritage-attire': '20-heritage-attire',
}

STATUS = ('<div class="sb"><span>9:41</span><span class="r">'
          '<svg viewBox="0 0 18 12"><rect x="0" y="8" width="3" height="4" rx=".6"/><rect x="5" y="5.5" width="3" height="6.5" rx=".6"/><rect x="10" y="3" width="3" height="9" rx=".6"/><rect x="15" y="0" width="3" height="12" rx=".6"/></svg>'
          '<svg viewBox="0 0 16 12"><path d="M8 11.4 5.7 9a3.3 3.3 0 0 1 4.6 0zM3.7 6.9a6.3 6.3 0 0 1 8.6 0l-1.3 1.4a4.4 4.4 0 0 0-6 0zM1.2 4.3a9.8 9.8 0 0 1 13.6 0l-1.3 1.4a7.9 7.9 0 0 0-11 0z"/></svg>'
          '<svg viewBox="0 0 27 12"><rect x=".5" y=".5" width="22" height="11" rx="3.2" fill="none" stroke="currentColor" opacity=".45"/><rect x="2" y="2" width="19" height="8" rx="2"/><rect x="24" y="4" width="2" height="4" rx="1" opacity=".45"/></svg>'
          '</span></div>')


def phone(name, width, style=''):
    return ('<div class="phone" style="--w:%dpx;%s"><div class="phone__s"><img src="raw/%s.png" alt="">%s<span class="isl"></span><span class="hi"></span></div></div>'
            % (width, style, RAW[name], STATUS))


CSS = """
@font-face{font-family:'Clarity City';src:url('../../briefing-source/fonts/ClarityCity_400Regular.ttf');font-weight:400}
@font-face{font-family:'Clarity City';src:url('../../briefing-source/fonts/ClarityCity_600SemiBold.ttf');font-weight:600}
@font-face{font-family:'Clarity City';src:url('../../briefing-source/fonts/ClarityCity_700Bold.ttf');font-weight:700}
@font-face{font-family:'Clarity City';src:url('../../briefing-source/fonts/ClarityCity_800ExtraBold.ttf');font-weight:800}
:root{--red:#ee1a26;--yellow:#fddd16;--sky:#00adf1;--ink:#16181d}
*{box-sizing:border-box}html{-webkit-print-color-adjust:exact}
body{margin:0;background:#888;font-family:'Clarity City',system-ui,sans-serif;color:var(--ink)}
.phone{position:absolute;width:var(--w);aspect-ratio:1000/2104;padding:calc(var(--w)*.026);background:linear-gradient(145deg,#2b2e35,#0c0d10 55%,#24262c);border-radius:calc(var(--w)*.155);box-shadow:0 40px 90px rgba(0,0,0,.35),0 8px 20px rgba(0,0,0,.25),inset 0 0 0 2px rgba(255,255,255,.12)}
.phone__s{position:relative;width:100%;height:100%;border-radius:calc(var(--w)*.13);overflow:hidden;background:#fff}
.phone__s>img{display:block;width:100%;height:100%;object-fit:cover}
.sb{position:absolute;left:0;right:0;top:0;height:calc(var(--w)*.12);display:flex;align-items:center;justify-content:space-between;padding:0 calc(var(--w)*.085);font-weight:700;font-size:calc(var(--w)*.036);color:#111}
.sb svg{height:calc(var(--w)*.032);width:auto;fill:currentColor}.sb .r{display:flex;gap:calc(var(--w)*.014);align-items:center}
.isl{position:absolute;left:50%;top:calc(var(--w)*.03);transform:translateX(-50%);width:calc(var(--w)*.27);height:calc(var(--w)*.075);background:#050506;border-radius:99px}
.hi{position:absolute;left:50%;bottom:calc(var(--w)*.022);transform:translateX(-50%);width:calc(var(--w)*.34);height:calc(var(--w)*.012);background:#16181d;border-radius:99px;opacity:.85}
.shot{position:relative;overflow:hidden;margin:0 auto 20px}
.flag{position:absolute;left:0;right:0;top:0;background:linear-gradient(90deg,var(--red) 0 33.4%,var(--yellow) 0 66.7%,var(--sky) 0)}
.shot h2{position:absolute;margin:0;font-weight:800;letter-spacing:-.025em;text-wrap:balance}
.shot p{position:absolute;margin:0;font-weight:700;text-wrap:balance}
"""

# (headline, line under it, background, text, accent, iPhone screen, the two iPad screens)
SHOTS = [
    ("Uganda's cultures,<br>starting with Lango", 'Lango today. Acholi is new and still being filled in.', '#fddd16', '#16181d', '#16181d', 'home', ('home', 'explore')),
    ('Choose<br>your culture', 'Switch between Lango and Acholi at any time.', '#16181d', '#ffffff', '#fddd16', 'culture-acholi', ('culture-lango', 'culture-acholi')),
    ('A dictionary<br>in your pocket', 'Search Leb Lango or English. Save it once to use offline.', '#e0f5fe', '#16181d', '#0786b8', 'dictionary', ('dictionary', 'phrasebook')),
    ('Learn the<br>language', 'Dictionary, translator, proverbs and everyday phrases.', '#e3f5ea', '#16181d', '#146b3c', 'learn', ('learn', 'proverbs')),
    ('Know<br>your clan', 'Totems and customs, clearly marked until they are verified.', '#fde8e9', '#16181d', '#c9111c', 'clan', ('clans', 'clan')),
    ('Explore<br>our heritage', 'Ceremonies, dance, attire, crafts, music and heritage sites.', '#fff3b8', '#16181d', '#7a5c00', 'heritage-attire', ('heritage-attire', 'people')),
    ('Test what<br>you know', 'Quizzes on Lango people, places and language.', '#16181d', '#ffffff', '#fddd16', 'quiz', ('quizzes', 'quiz')),
    ('Add what<br>you know', 'Share a word, a story or a photo. It is reviewed before it is published.', '#fde8e9', '#16181d', '#c9111c', 'contribute', ('contribute', 'form-word')),
]


def page(title, w, h, sections, extra_css):
    return ('<!doctype html><html lang="en"><head><meta charset="utf-8"><title>%s</title><style>%s%s</style></head><body>%s</body></html>'
            % (title, CSS, extra_css, '\n'.join(sections)))


# ---------- iPhone 6.3-inch: 1206 x 2622, one phone per picture
iphone = []
for i, (head, sub, bg, fg, accent, one, _two) in enumerate(SHOTS, 1):
    iphone.append('<section class="shot" id="s%d" style="background:%s;color:%s"><div class="flag"></div><h2>%s</h2><p style="color:%s">%s</p>%s</section>'
                  % (i, bg, fg, head, accent, sub, phone(one, 960, 'left:123px;top:640px')))
open(os.path.join(HERE, 'iphone.html'), 'w', encoding='utf-8').write(page('MyCulture iPhone screenshots', 1206, 2622, iphone, """
.shot{width:1206px;height:2622px}.flag{height:20px}
.shot h2{left:90px;right:90px;top:120px;font-size:98px;line-height:1.04}
.shot p{left:90px;right:110px;top:372px;font-size:48px;line-height:1.3}
"""))

# ---------- iPad 13-inch: 2064 x 2752, two phones side by side (the second a little lower)
ipad = []
for i, (head, sub, bg, fg, accent, _one, two) in enumerate(SHOTS, 1):
    ipad.append('<section class="shot" id="s%d" style="background:%s;color:%s"><div class="flag"></div><h2>%s</h2><p style="color:%s">%s</p>%s%s</section>'
                % (i, bg, fg, head, accent, sub, phone(two[0], 860, 'left:152px;top:770px'), phone(two[1], 860, 'left:1052px;top:880px')))
open(os.path.join(HERE, 'ipad.html'), 'w', encoding='utf-8').write(page('MyCulture iPad screenshots', 2064, 2752, ipad, """
.shot{width:2064px;height:2752px}.flag{height:26px}
.shot h2{left:152px;right:152px;top:130px;font-size:150px;line-height:1.04}
.shot p{left:152px;right:152px;top:470px;font-size:66px;line-height:1.3}
"""))

# ---------- Google Play: 1080 x 1920 phone screenshots and the 1024 x 500 feature graphic (one phone, like the iPhone set)
play = []
for i, (head, sub, bg, fg, accent, one, _two) in enumerate(SHOTS, 1):
    play.append('<section class="shot" id="s%d" style="background:%s;color:%s"><div class="flag"></div><h2>%s</h2><p style="color:%s">%s</p>%s</section>'
                % (i, bg, fg, head, accent, sub, phone(one, 800, 'left:140px;top:520px')))
open(os.path.join(HERE, 'play.html'), 'w', encoding='utf-8').write(page('MyCulture Google Play screenshots', 1080, 1920, play, """
.shot{width:1080px;height:1920px}.flag{height:18px}
.shot h2{left:80px;right:80px;top:104px;font-size:88px;line-height:1.04}
.shot p{left:80px;right:100px;top:336px;font-size:42px;line-height:1.3}
"""))
open(os.path.join(HERE, 'feature.html'), 'w', encoding='utf-8').write(page('MyCulture feature graphic', 1024, 500, [
    '<section class="feature" id="s1"><div class="flag"></div><div class="logo"><img src="../../briefing-source/art/logo-mark.png" alt=""></div>'
    '<div class="t"><b>MyCulture</b><span>Uganda\'s cultures,<br>starting with Lango.</span><i>Language &middot; Clans &middot; Heritage &middot; Community</i></div>'
    + phone('home', 250, 'left:600px;top:70px;transform:rotate(7deg)') + phone('dictionary', 250, 'left:790px;top:110px;transform:rotate(-4deg)') + '</section>'], """
.feature{position:relative;width:1024px;height:500px;overflow:hidden;background:#16181d url('../../briefing-source/art/sunset-lira.jpg') 50% 70%/cover;color:#fff}
.feature::before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(22,24,29,.94) 0%,rgba(22,24,29,.78) 46%,rgba(22,24,29,.25) 100%)}
.feature .flag{height:10px;z-index:3}
.feature .logo{position:absolute;left:56px;top:50px;width:78px;height:88px;border-radius:16px;background:#fff;display:grid;place-items:center;z-index:3}
.feature .logo img{width:54px;height:68px;object-fit:contain}
.feature .t{position:absolute;left:56px;top:170px;width:520px;z-index:3}
.feature .t b{display:block;font-size:80px;line-height:1;font-weight:800;letter-spacing:-.03em}
.feature .t span{display:block;margin-top:14px;font-size:32px;line-height:1.25;font-weight:600;color:#fddd16}
.feature .t i{display:block;margin-top:22px;font-style:normal;font-size:24px;font-weight:600;color:rgba(255,255,255,.82)}
.feature .phone{z-index:2}
"""))
print('written: source/iphone.html, ipad.html, play.html, feature.html')
if '--html-only' in sys.argv:
    sys.exit(0)


def shot(html, png, w, h):
    url = 'file:///' + html.replace(os.sep, '/')
    raw = png + '.raw.png'   # Edge can finish writing its file after the command returns, so it is cropped into the real file only once it has stopped growing
    if os.path.exists(raw):
        os.remove(raw)
    subprocess.run([EDGE, '--headless=new', '--disable-gpu', '--no-sandbox', '--hide-scrollbars', '--user-data-dir=' + PROFILE, '--virtual-time-budget=10000',
                    '--window-size=%d,%d' % (w, h + 140), '--screenshot=' + raw, url], check=True, capture_output=True, timeout=180)
    last = -1
    for _ in range(60):
        size = os.path.getsize(raw) if os.path.exists(raw) else -1
        if size > 0 and size == last:
            break
        last = size
        time.sleep(1)
    Image.open(raw).convert('RGB').crop((0, 0, w, h)).save(png, optimize=True)
    os.remove(raw)


tmp = os.path.join(HERE, '_one.html')
PLAY = os.path.normpath(os.path.join(HERE, '..', '..', 'play-store'))
# which sets to render: python make_ios.py [ios] [play]   (no argument = ios)
want = [a for a in sys.argv[1:] if not a.startswith('--')] or ['ios']
jobs = []
if 'ios' in want:
    jobs += [('iphone', os.path.join(OUT, 'iphone-6.3'), '%02d.png', 1206, 2622, len(SHOTS)), ('ipad', os.path.join(OUT, 'ipad-13'), '%02d.png', 2064, 2752, len(SHOTS))]
if 'play' in want:
    jobs += [('play', os.path.join(PLAY, 'screenshots'), 'phone-%02d.png', 1080, 1920, len(SHOTS))]
if 'play' in want or 'feature' in want:
    jobs += [('feature', PLAY, 'feature-graphic-1024x500.png', 1024, 500, 1)]
for name, folder, pattern, w, h, count in jobs:
    os.makedirs(folder, exist_ok=True)
    src = open(os.path.join(HERE, name + '.html'), encoding='utf-8').read()
    for i in range(1, count + 1):
        open(tmp, 'w', encoding='utf-8').write(src.replace('</style>', '.shot,.feature{margin:0 !important}body{margin:0;background:#fff}.shot:not(#s%d),.feature:not(#s%d){display:none}</style>' % (i, i), 1))
        shot(tmp, os.path.join(folder, pattern % i if '%' in pattern else pattern), w, h)
        print('rendered', name, i)
os.remove(tmp)
