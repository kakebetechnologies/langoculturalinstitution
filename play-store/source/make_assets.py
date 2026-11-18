"""Builds the Google Play listing graphics: the 512 px icon, the 1024x500 feature graphic and eight 1080x1920 phone screenshots.
   python make_assets.py   (writes source/screens.html + source/feature.html and the icon, then node render_assets.js makes the PNGs)
   The screens come from ../../briefing-source/shots (real screens of the app); fonts and photos from ../../briefing-source."""
import os
from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.normpath(os.path.join(HERE, '..'))
APP_ICON = os.path.normpath(os.path.join(HERE, '..', '..', 'app', 'assets', 'icon.png'))

# the 512x512 icon (full square, no rounded corners: Google Play rounds it)
Image.open(APP_ICON).convert('RGBA').resize((512, 512), Image.LANCZOS).save(os.path.join(OUT, 'icon-512.png'), optimize=True)

STATUS = ('<div class="sb"><span>9:41</span><span class="r">'
          '<svg viewBox="0 0 18 12"><rect x="0" y="8" width="3" height="4" rx=".6"/><rect x="5" y="5.5" width="3" height="6.5" rx=".6"/><rect x="10" y="3" width="3" height="9" rx=".6"/><rect x="15" y="0" width="3" height="12" rx=".6"/></svg>'
          '<svg viewBox="0 0 16 12"><path d="M8 11.4 5.7 9a3.3 3.3 0 0 1 4.6 0zM3.7 6.9a6.3 6.3 0 0 1 8.6 0l-1.3 1.4a4.4 4.4 0 0 0-6 0zM1.2 4.3a9.8 9.8 0 0 1 13.6 0l-1.3 1.4a7.9 7.9 0 0 0-11 0z"/></svg>'
          '<svg viewBox="0 0 27 12"><rect x=".5" y=".5" width="22" height="11" rx="3.2" fill="none" stroke="currentColor" opacity=".45"/><rect x="2" y="2" width="19" height="8" rx="2"/><rect x="24" y="4" width="2" height="4" rx="1" opacity=".45"/></svg>'
          '</span></div>')


def phone(name, width, dark=False):
    folder = '../../showcase-source/shots/app' if name == 'translate-result' else '../../briefing-source/shots'   # real screens of the app
    return ('<div class="phone%s" style="--w:%dpx"><div class="phone__s"><img src="%s/%s.jpg" alt="">%s<span class="isl"></span><span class="hi"></span></div></div>'
            % (' dark' if dark else '', width, folder, name, STATUS))


CSS = """
@font-face{font-family:'Clarity City';src:url('../../briefing-source/fonts/ClarityCity_400Regular.ttf');font-weight:400}
@font-face{font-family:'Clarity City';src:url('../../briefing-source/fonts/ClarityCity_600SemiBold.ttf');font-weight:600}
@font-face{font-family:'Clarity City';src:url('../../briefing-source/fonts/ClarityCity_700Bold.ttf');font-weight:700}
@font-face{font-family:'Clarity City';src:url('../../briefing-source/fonts/ClarityCity_800ExtraBold.ttf');font-weight:800}
:root{--red:#ee1a26;--yellow:#fddd16;--sky:#00adf1;--ink:#16181d;--cream:#f6f3ec}
*{box-sizing:border-box}html{-webkit-print-color-adjust:exact}
body{margin:0;background:#888;font-family:'Clarity City',system-ui,sans-serif;color:var(--ink)}
.phone{position:relative;width:var(--w);aspect-ratio:1000/2104;padding:calc(var(--w)*.026);background:linear-gradient(145deg,#2b2e35,#0c0d10 55%,#24262c);border-radius:calc(var(--w)*.155);box-shadow:0 40px 90px rgba(0,0,0,.35),0 8px 20px rgba(0,0,0,.25),inset 0 0 0 2px rgba(255,255,255,.12)}
.phone__s{position:relative;width:100%;height:100%;border-radius:calc(var(--w)*.13);overflow:hidden;background:#fff}
.phone__s>img{display:block;width:100%;height:100%;object-fit:cover}
.sb{position:absolute;left:0;right:0;top:0;height:calc(var(--w)*.12);display:flex;align-items:center;justify-content:space-between;padding:0 calc(var(--w)*.085);font-weight:700;font-size:calc(var(--w)*.036);color:#111}
.sb svg{height:calc(var(--w)*.032);width:auto;fill:currentColor}.sb .r{display:flex;gap:calc(var(--w)*.014);align-items:center}
.phone.dark .sb{color:#fff}
.isl{position:absolute;left:50%;top:calc(var(--w)*.03);transform:translateX(-50%);width:calc(var(--w)*.27);height:calc(var(--w)*.075);background:#050506;border-radius:99px}
.hi{position:absolute;left:50%;bottom:calc(var(--w)*.022);transform:translateX(-50%);width:calc(var(--w)*.34);height:calc(var(--w)*.012);background:#16181d;border-radius:99px;opacity:.85}
.phone.dark .hi{background:#fff}
"""

# ---------- the eight screenshots ----------
SHOTS = [
    ('welcome', 'Your culture. Your clan. Your language.', 'Sign in with Google or your email.', True, '#16181d', '#fff', '#fddd16'),
    ('home', "Uganda's cultures, in one place", 'Lango first, with Acholi and more to follow. A word a day and your clan.', False, '#fddd16', '#16181d', '#16181d'),
    ('dictionary', 'A dictionary in your pocket', 'Search Leb Lango or English, even offline.', False, '#e0f5fe', '#16181d', '#0786b8'),
    ('clan', 'Know your clan', 'Totems, prohibitions and history, clearly marked until verified.', False, '#fde8e9', '#16181d', '#c9111c'),
    ('heritage', 'Heritage, with its sources', 'Attire, crafts, music and ceremonies, each with its credit.', False, '#fff3b8', '#16181d', '#7a5c00'),
    ('quizResult', 'Quizzes that teach', 'Every answer shows the fact behind it and where it comes from.', False, '#e3f5ea', '#16181d', '#146b3c'),
    ('contribute', 'Add what you know', 'Share a word, a story or a photo. It is reviewed before it is published.', False, '#fde8e9', '#16181d', '#c9111c'),
    ('translate-result', 'Look up any word', 'Single words and set phrases, English to Leb Lango and back.', False, '#e0f5fe', '#16181d', '#0786b8'),
]
cards = []
for i, (name, head, sub, dark, bg, fg, accent) in enumerate(SHOTS, 1):
    cards.append('''<section class="shot" id="s%d" style="background:%s;color:%s">
  <div class="flag"></div>
  <h2>%s</h2><p style="color:%s">%s</p>
  <div class="ph">%s</div>
</section>''' % (i, bg, fg, head, accent, sub, phone(name, 800, dark)))

screens = '''<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Lango Play screenshots</title><style>%s
.shot{position:relative;width:1080px;height:1920px;overflow:hidden;margin:0 auto 20px}
.flag{position:absolute;left:0;right:0;top:0;height:18px;background:linear-gradient(90deg,var(--red) 0 33.4%%,var(--yellow) 0 66.7%%,var(--sky) 0)}
.shot h2{position:absolute;left:80px;right:80px;top:104px;margin:0;font-size:92px;line-height:1.04;font-weight:800;letter-spacing:-.025em}
.shot p{position:absolute;left:80px;right:100px;top:330px;margin:0;font-size:44px;line-height:1.3;font-weight:700}
.shot .ph{position:absolute;left:140px;top:500px}
</style></head><body>%s</body></html>''' % (CSS, '\n'.join(cards))
open(os.path.join(HERE, 'screens.html'), 'w', encoding='utf-8').write(screens)

# ---------- the feature graphic ----------
feature = '''<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Lango feature graphic</title><style>%s
.feature{position:relative;width:1024px;height:500px;overflow:hidden;background:#16181d url('../../briefing-source/art/sunset-lira.jpg') 50%% 70%%/cover;color:#fff;margin:0 auto}
.feature::before{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(22,24,29,.94) 0%%,rgba(22,24,29,.78) 46%%,rgba(22,24,29,.25) 100%%)}
.flag{position:absolute;left:0;right:0;top:0;height:10px;background:linear-gradient(90deg,var(--red) 0 33.4%%,var(--yellow) 0 66.7%%,var(--sky) 0);z-index:3}
.logo{position:absolute;left:56px;top:50px;width:78px;height:88px;border-radius:16px;background:#fff;display:grid;place-items:center;z-index:3}
.logo img{width:54px;height:68px;object-fit:contain}
.t{position:absolute;left:56px;top:170px;width:500px;z-index:3}
.t b{display:block;font-size:80px;line-height:1;font-weight:800;letter-spacing:-.03em}
.t span{display:block;margin-top:14px;font-size:34px;line-height:1.25;font-weight:600;color:#fddd16}
.t i{display:block;margin-top:22px;font-style:normal;font-size:24px;font-weight:600;color:rgba(255,255,255,.82)}
.p1{position:absolute;left:600px;top:70px;transform:rotate(7deg);z-index:2}
.p2{position:absolute;left:790px;top:110px;transform:rotate(-4deg);z-index:2}
</style></head><body><section class="feature" id="f1"><div class="flag"></div>
<div class="logo"><img src="../../briefing-source/art/logo-mark.png" alt=""></div>
<div class="t"><b>MyCulture</b><span>Uganda's cultures, in one place.</span><i>Language &middot; Clans &middot; Heritage &middot; Community</i></div>
<div class="p1">%s</div><div class="p2">%s</div></section></body></html>''' % (CSS, phone('home', 250), phone('dictionary', 250))
open(os.path.join(HERE, 'feature.html'), 'w', encoding='utf-8').write(feature)
print('written: icon-512.png, source/screens.html, source/feature.html')
